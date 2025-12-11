<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomerUserController extends Controller implements HasMiddleware
{
    /**
     * Định nghĩa Middleware cho Controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('checkRole:admin,staff'),
        ];
    }

    /* =========================================================================
       1. DANH SÁCH KHÁCH HÀNG (INDEX)
    ========================================================================= */
    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_CUSTOMER)
            ->withCount(['orders']) // Đếm số đơn hàng
            ->withSum(['orders' => fn($q) => $q->where('payment_status', 'paid')], 'total_amount') // Tổng tiền đã mua
            ->latest('id');

        // --- Tìm kiếm từ khóa ---
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('full_name', 'like', "%$keyword%")
                  ->orWhere('email', 'like', "%$keyword%")
                  ->orWhere('phone', 'like', "%$keyword%");
            });
        }

        // --- Lọc theo trạng thái ---
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    /* =========================================================================
       2. XEM CHI TIẾT KHÁCH HÀNG (SHOW)
    ========================================================================= */
    public function show($id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)
            ->withCount(['orders', 'reviews'])
            ->withSum(['orders' => fn($q) => $q->where('payment_status', 'paid')], 'total_amount')
            ->findOrFail($id);

        // Lấy 5 đơn hàng gần nhất để hiển thị
        $orders = $customer->orders()->latest()->paginate(5);

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    /* =========================================================================
       3. KHÓA / MỞ KHÓA TÀI KHOẢN (UPDATE STATUS)
    ========================================================================= */
    public function updateStatus($id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($id);

        // Đảo ngược trạng thái hiện tại
        $customer->status = $customer->status === 'active' ? 'banned' : 'active';
        $customer->save();

        $message = $customer->status === 'active' 
            ? 'Đã mở khóa tài khoản khách hàng thành công.' 
            : 'Đã khóa tài khoản khách hàng thành công.';

        return back()->with('success', $message);
    }

    /* =========================================================================
       4. FORM THÊM MỚI (CREATE)
    ========================================================================= */
    public function create()
    {
        return view('admin.customers.create');
    }

    /* =========================================================================
       5. LƯU KHÁCH HÀNG MỚI (STORE)
    ========================================================================= */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'unique:users,email', 'max:255'],
            'phone'     => ['nullable', 'regex:/^(0)[0-9]{9}$/', 'unique:users,phone'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'status'    => ['required', Rule::in(['active', 'banned'])],
            'gender'    => ['nullable', Rule::in(['male', 'female', 'other'])],
            'birthday'  => ['nullable', 'date', 'before:today'],
            'avatar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'], // Max 2MB
        ], [
            // Custom thông báo lỗi tiếng Việt
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'full_name.max'      => 'Họ tên không được vượt quá 150 ký tự.',
            'email.required'     => 'Vui lòng nhập địa chỉ email.',
            'email.email'        => 'Email không đúng định dạng.',
            'email.unique'       => 'Email này đã được sử dụng trong hệ thống.',
            'phone.regex'        => 'Số điện thoại không hợp lệ (Phải gồm 10 số, bắt đầu bằng 0).',
            'phone.unique'       => 'Số điện thoại này đã được sử dụng.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'status.required'    => 'Vui lòng chọn trạng thái tài khoản.',
            'birthday.date'      => 'Ngày sinh không hợp lệ.',
            'birthday.before'    => 'Ngày sinh phải nhỏ hơn ngày hiện tại.',
            'avatar.image'       => 'File tải lên phải là hình ảnh.',
            'avatar.mimes'       => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'avatar.max'         => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        try {
            DB::beginTransaction();

            // 2. Xử lý Upload Avatar
            if ($request->hasFile('avatar')) {
                // Lưu vào 'storage/app/public/avatars'
                $path = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            // 3. Xử lý dữ liệu khác
            $validated['birthday'] = $request->filled('birthday') ? $request->birthday : null;
            $validated['password'] = Hash::make($request->password);
            $validated['role']     = User::ROLE_CUSTOMER; // Gán cứng role là customer

            // 4. Tạo User
            User::create($validated);

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Thêm mới khách hàng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Xóa ảnh rác nếu quá trình lưu DB thất bại
            if (isset($validated['avatar'])) {
                Storage::disk('public')->delete($validated['avatar']);
            }
            
            Log::error("Lỗi tạo khách hàng: " . $e->getMessage());
            return back()->withInput()->with('error', 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    /* =========================================================================
       6. FORM CHỈNH SỬA (EDIT)
    ========================================================================= */
    public function edit($id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)
            ->with('addresses') // Eager load địa chỉ để tránh N+1 query
            ->findOrFail($id);

        return view('admin.customers.edit', compact('customer'));
    }

    /* =========================================================================
       7. CẬP NHẬT THÔNG TIN KHÁCH HÀNG (UPDATE)
    ========================================================================= */
    public function update(Request $request, $id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($id);

        // 1. Validate dữ liệu
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($customer->id)],
            'phone'     => ['nullable', 'regex:/^(0)[0-9]{9}$/', Rule::unique('users')->ignore($customer->id)],
            'status'    => ['required', Rule::in(['active', 'banned'])],
            'gender'    => ['nullable', Rule::in(['male', 'female', 'other'])],
            'birthday'  => ['nullable', 'date', 'before:today'],
            'avatar'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'], // Mật khẩu không bắt buộc khi update
        ], [
            // Custom thông báo lỗi
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.unique'       => 'Email này đã được sử dụng bởi tài khoản khác.',
            'phone.regex'        => 'Số điện thoại không hợp lệ.',
            'phone.unique'       => 'Số điện thoại này đã tồn tại.',
            'password.min'       => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'avatar.max'         => 'Ảnh đại diện quá lớn (tối đa 2MB).',
        ]);

        try {
            DB::beginTransaction();

            // 2. Xử lý Mật khẩu: Chỉ update nếu người dùng nhập giá trị mới
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']); // Loại bỏ khỏi mảng update để giữ pass cũ
            }

            // 3. Xử lý Ngày sinh
            $validated['birthday'] = $request->filled('birthday') ? $request->birthday : null;

            // 4. Xử lý Upload Avatar mới
            if ($request->hasFile('avatar')) {
                // Xóa ảnh cũ nếu tồn tại trong storage
                if ($customer->avatar && Storage::disk('public')->exists($customer->avatar)) {
                    Storage::disk('public')->delete($customer->avatar);
                }
                // Lưu ảnh mới
                $path = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            // 5. Cập nhật vào Database
            $customer->update($validated);

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Cập nhật thông tin khách hàng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi cập nhật khách hàng ID {$id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Không thể cập nhật thông tin. Vui lòng thử lại.');
        }
    }

    /* =========================================================================
       8. XÓA KHÁCH HÀNG (DESTROY)
    ========================================================================= */
    public function destroy($id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($id);

        // Kiểm tra ràng buộc: Không xóa khách đã có đơn hàng
        if ($customer->orders()->exists()) {
            return back()->with('error', 'Không thể xóa khách hàng này vì đã phát sinh đơn hàng. Bạn chỉ có thể khóa tài khoản.');
        }

        try {
            DB::beginTransaction();

            // Xóa ảnh đại diện vật lý
            if ($customer->avatar && Storage::disk('public')->exists($customer->avatar)) {
                Storage::disk('public')->delete($customer->avatar);
            }

            // Xóa dữ liệu liên quan (Cascade thủ công để an toàn)
            $customer->reviews()->delete();     // Xóa đánh giá
            $customer->addresses()->delete();   // Xóa sổ địa chỉ
            
            // Cuối cùng xóa user
            $customer->delete();

            DB::commit();

            return back()->with('success', 'Đã xóa khách hàng và toàn bộ dữ liệu liên quan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi xóa khách hàng ID {$id}: " . $e->getMessage());
            return back()->with('error', 'Đã xảy ra lỗi khi xóa khách hàng.');
        }
    }

    /* =========================================================================
       9. QUẢN LÝ ĐỊA CHỈ (ADDRESSES) - CRUD
    ========================================================================= */

    /**
     * Form thêm địa chỉ mới
     */
    public function createAddress($customerId)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($customerId);
        return view('admin.customers.addresses.create', compact('customer'));
    }

    /**
     * Lưu địa chỉ mới
     */
    public function storeAddress(Request $request, $customerId)
    {
        $validated = $request->validate([
            'contact_name' => ['required', 'string', 'max:150'],
            'phone'        => ['required', 'regex:/^(0)[0-9]{9}$/'],
            'city'         => ['nullable', 'string', 'max:100'],
            'district'     => ['nullable', 'string', 'max:100'],
            'ward'         => ['nullable', 'string', 'max:100'],
            'address'      => ['nullable', 'string', 'max:255'],
            'is_default'   => ['boolean'],
        ], [
            'contact_name.required' => 'Vui lòng nhập tên người nhận.',
            'phone.required'        => 'Vui lòng nhập số điện thoại người nhận.',
            'phone.regex'           => 'Số điện thoại không hợp lệ.',
        ]);

        try {
            DB::beginTransaction();

            // Nếu người dùng chọn địa chỉ này là mặc định
            if ($request->boolean('is_default')) {
                // Reset tất cả địa chỉ cũ về false
                UserAddress::where('user_id', $customerId)->update(['is_default' => false]);
            }

            // Gán thông tin bổ sung
            $validated['user_id'] = $customerId;
            $validated['is_default'] = $request->boolean('is_default');

            // Tạo mới
            UserAddress::create($validated);

            DB::commit();

            return redirect()->route('admin.customers.edit', $customerId)
                ->with('success', 'Đã thêm địa chỉ giao hàng mới.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi thêm địa chỉ khách hàng ID {$customerId}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Không thể thêm địa chỉ. Lỗi hệ thống.');
        }
    }

    /**
     * Form sửa địa chỉ
     */
    public function editAddress($customerId, $addressId)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($customerId);
        
        // Đảm bảo địa chỉ thuộc về đúng khách hàng đó
        $address = UserAddress::where('user_id', $customerId)->findOrFail($addressId);

        return view('admin.customers.addresses.edit', compact('customer', 'address'));
    }

    /**
     * Cập nhật địa chỉ
     */
    public function updateAddress(Request $request, $customerId, $addressId)
    {
        // Tìm địa chỉ cần sửa và đảm bảo nó thuộc user đó
        $address = UserAddress::where('user_id', $customerId)->findOrFail($addressId);

        $validated = $request->validate([
            'contact_name' => ['required', 'string', 'max:150'],
            'phone'        => ['required', 'regex:/^(0)[0-9]{9}$/'],
            'city'         => ['nullable', 'string', 'max:100'],
            'district'     => ['nullable', 'string', 'max:100'],
            'ward'         => ['nullable', 'string', 'max:100'],
            'address'      => ['nullable', 'string', 'max:255'],
            'is_default'   => ['boolean'],
        ], [
            'contact_name.required' => 'Vui lòng nhập tên người nhận.',
            'phone.required'        => 'Vui lòng nhập số điện thoại.',
            'phone.regex'           => 'Số điện thoại không đúng định dạng.',
        ]);

        try {
            DB::beginTransaction();

            // Nếu set làm mặc định -> Bỏ mặc định các địa chỉ khác
            if ($request->boolean('is_default')) {
                UserAddress::where('user_id', $customerId)
                    ->where('id', '!=', $addressId) // Trừ thằng đang sửa ra
                    ->update(['is_default' => false]);
            }

            $validated['is_default'] = $request->boolean('is_default');
            $address->update($validated);

            DB::commit();

            return redirect()->route('admin.customers.edit', $customerId)
                ->with('success', 'Cập nhật địa chỉ giao hàng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi sửa địa chỉ ID {$addressId}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Không thể cập nhật địa chỉ. Lỗi hệ thống.');
        }
    }

    /**
     * Xóa địa chỉ
     */
    public function deleteAddress($customerId, $addressId)
    {
        $address = UserAddress::where('user_id', $customerId)->where('id', $addressId)->firstOrFail();

        // Tùy chọn: Chặn xóa nếu là địa chỉ mặc định (Để tránh user không có địa chỉ default)
        // if ($address->is_default) {
        //     return back()->with('error', 'Không thể xóa địa chỉ mặc định. Vui lòng chọn địa chỉ khác làm mặc định trước.');
        // }

        $address->delete();

        return back()->with('success', 'Đã xóa địa chỉ giao hàng.');
    }
}