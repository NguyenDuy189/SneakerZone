<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomerUserController extends Controller implements HasMiddleware
{
    /**
     * CẤU HÌNH MIDDLEWARE
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('checkRole:admin,staff'),
        ];
    }

    /**
     * 1. DANH SÁCH KHÁCH HÀNG
     */
    public function index(Request $request)
    {
        $query = User::where('role', User::ROLE_CUSTOMER)
            ->withCount('orders')
            // Tính tổng tiền đã chi (chỉ tính đơn đã thanh toán)
            ->withSum(['orders' => function($q) {
                $q->where('payment_status', 'paid');
            }], 'total_amount')
            ->latest('id');

        // Tìm kiếm
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        // Lọc trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * 2. XEM CHI TIẾT HỒ SƠ (ĐÃ BỔ SUNG)
     */
    public function show($id)
    {
        // Lấy thông tin khách hàng kèm các chỉ số thống kê
        $customer = User::where('role', User::ROLE_CUSTOMER)
            ->withCount(['orders', 'reviews'])
            ->withSum(['orders' => function($q) {
                $q->where('payment_status', 'paid');
            }], 'total_amount')
            ->findOrFail($id);

        // Lấy lịch sử mua hàng của khách (Phân trang 5 dòng)
        $orders = $customer->orders()->latest()->paginate(5);

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    /**
     * 3. KHÓA / MỞ KHÓA TÀI KHOẢN (ĐÃ BỔ SUNG)
     */
    public function updateStatus($id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($id);
        
        // Đảo ngược trạng thái: active <-> banned
        $customer->status = ($customer->status === User::STATUS_ACTIVE) 
            ? User::STATUS_BANNED 
            : User::STATUS_ACTIVE;
            
        $customer->save();

        $msg = $customer->status === User::STATUS_ACTIVE 
            ? 'Đã mở khóa tài khoản khách hàng.' 
            : 'Đã khóa tài khoản thành công.';

        return back()->with('success', $msg);
    }

    /**
     * 4. FORM TẠO MỚI
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * 5. LƯU KHÁCH HÀNG MỚI
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'phone'     => ['nullable', 'regex:/^(0)[0-9]{9}$/', 'unique:users,phone'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'status'    => ['required', Rule::in(['active', 'banned'])],
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên khách hàng.',
            'email.unique'       => 'Email này đã được đăng ký.',
            'phone.unique'       => 'Số điện thoại này đã được sử dụng.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        try {
            DB::beginTransaction();

            $validated['role'] = User::ROLE_CUSTOMER;
            User::create($validated);

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Thêm khách hàng mới thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi tạo khách hàng: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống. Vui lòng thử lại.');
        }
    }

    /**
     * 6. FORM CHỈNH SỬA
     */
    public function edit($id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * 7. CẬP NHẬT THÔNG TIN
     */
    public function update(Request $request, $id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($id);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', Rule::unique('users')->ignore($customer->id)],
            'phone'     => ['nullable', 'regex:/^(0)[0-9]{9}$/', Rule::unique('users')->ignore($customer->id)],
            'status'    => ['required', Rule::in(['active', 'banned'])],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'Email này đã thuộc về khách hàng khác.',
            'phone.unique' => 'Số điện thoại này đã thuộc về khách hàng khác.',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        try {
            DB::beginTransaction();

            $validated['role'] = User::ROLE_CUSTOMER; 
            $customer->update($validated);

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Cập nhật thông tin khách hàng thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi cập nhật khách hàng ID $id: " . $e->getMessage());
            return back()->with('error', 'Lỗi hệ thống khi cập nhật.');
        }
    }

    /**
     * 8. XÓA KHÁCH HÀNG (BẢO MẬT DỮ LIỆU)
     */
    public function destroy($id)
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($id);

        if ($customer->orders()->exists()) {
            return back()->with('error', 'Không thể xóa khách hàng đã có lịch sử mua hàng. Hãy chọn "Khóa tài khoản" thay thế.');
        }

        try {
            DB::beginTransaction();

            $customer->reviews()->delete(); 
            $customer->delete();

            DB::commit();

            return back()->with('success', 'Đã xóa hồ sơ khách hàng.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi xóa khách hàng ID $id: " . $e->getMessage());
            return back()->with('error', 'Không thể xóa khách hàng này. Vui lòng thử lại.');
        }
    }
}