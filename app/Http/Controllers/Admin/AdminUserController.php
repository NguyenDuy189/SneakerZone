<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminUserController extends Controller implements HasMiddleware
{
    /**
     * CẤU HÌNH MIDDLEWARE (LARAVEL 11)
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'), // Bắt buộc đăng nhập
            // Cho phép cả Admin và Staff truy cập, NHƯNG sẽ phân quyền chặt ở bên dưới
            new Middleware('checkRole:admin,staff'), 
        ];
    }

    /**
     * DANH SÁCH NHÂN SỰ
     */
    public function index(Request $request)
    {
        // Staff chỉ được xem danh sách, không được xem Admin nếu muốn chặt chẽ hơn
        // Ở đây tôi cho phép xem hết nhưng sẽ chặn hành động Sửa/Xóa
        $query = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])
            ->latest('id');

        // Tìm kiếm đa năng
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function($q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo vai trò
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * FORM TẠO MỚI
     */
    public function create()
    {
        // Chặn Staff tạo Admin (Chỉ Admin mới được tạo nhân sự)
        if (Auth::user()->role !== User::ROLE_ADMIN) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không có quyền thêm mới nhân sự.');
        }

        return view('admin.users.create');
    }

    /**
     * XỬ LÝ LƯU (STORE)
     */
    public function store(Request $request)
    {
        // 1. Phân quyền: Staff không được phép tạo User
        if (Auth::user()->role !== User::ROLE_ADMIN) {
            return back()->with('error', 'Bạn không đủ thẩm quyền để thực hiện hành động này.');
        }

        // 2. Validate tiếng Việt chi tiết
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'phone'     => ['nullable', 'regex:/^(0)[0-9]{9}$/', 'unique:users,phone'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'role'      => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
            'status'    => ['required', Rule::in(['active', 'banned'])],
        ], [
            'full_name.required' => 'Họ và tên là bắt buộc.',
            'email.required'     => 'Email là bắt buộc.',
            'email.email'        => 'Định dạng email không hợp lệ.',
            'email.unique'       => 'Email này đã tồn tại trong hệ thống.',
            'phone.regex'        => 'Số điện thoại không đúng định dạng (10 số).',
            'phone.unique'       => 'Số điện thoại này đã được sử dụng.',
            'password.required'  => 'Mật khẩu là bắt buộc.',
            'password.min'       => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'role.required'      => 'Vui lòng chọn vai trò.',
            'status.required'    => 'Vui lòng chọn trạng thái.',
        ]);

        // 3. Tạo User
        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Tạo tài khoản quản trị viên mới thành công.');
    }

    /**
     * FORM CHỈNH SỬA
     */
    public function edit($id)
    {
        $user = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->findOrFail($id);

        // Phân quyền: Staff không được sửa Admin khác
        if (Auth::user()->role !== User::ROLE_ADMIN && $user->role === User::ROLE_ADMIN) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa tài khoản Quản trị cấp cao.');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * XỬ LÝ CẬP NHẬT (UPDATE) - LOGIC CỐT LÕI
     */
    public function update(Request $request, $id)
    {
        $targetUser = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->findOrFail($id);
        $currentUser = Auth::user();

        // === LỚP BẢO MẬT 1: PHÂN CẤP (HIERARCHY) ===
        // Staff không được phép sửa bất kỳ thông tin nào của Admin
        if ($currentUser->role !== User::ROLE_ADMIN && $targetUser->role === User::ROLE_ADMIN) {
            return back()->with('error', 'CẢNH BÁO: Bạn không đủ quyền hạn để tác động đến tài khoản này.');
        }

        // === LỚP BẢO MẬT 2: BẤT BIẾN SUPER ADMIN (ID=1) ===
        // Không ai được phép hạ quyền hoặc khóa tài khoản gốc (ID 1)
        if ($targetUser->id === 1) {
            if ($request->role !== User::ROLE_ADMIN) {
                return back()->with('error', 'BẢO MẬT: Không thể hạ quyền của Super Admin gốc.');
            }
            if ($request->status === 'banned') {
                return back()->with('error', 'BẢO MẬT: Không thể khóa tài khoản Super Admin gốc.');
            }
        }

        // === LỚP BẢO MẬT 3: CHỐNG TỰ HỦY (ANTI-SUICIDE) ===
        // Không cho phép tự khóa hoặc tự hạ quyền chính mình
        if ($targetUser->id === $currentUser->id) {
            if ($request->status === 'banned') {
                return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình.');
            }
            if ($request->role !== $currentUser->role) {
                return back()->with('error', 'Bạn không thể tự thay đổi vai trò của chính mình.');
            }
        }

        // === VALIDATION TIẾNG VIỆT ===
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', Rule::unique('users')->ignore($targetUser->id)],
            'phone'     => ['nullable', 'regex:/^(0)[0-9]{9}$/', Rule::unique('users')->ignore($targetUser->id)],
            'role'      => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
            'status'    => ['required', Rule::in(['active', 'banned'])],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'full_name.required' => 'Họ tên không được để trống.',
            'email.unique'       => 'Email này đã thuộc về tài khoản khác.',
            'phone.regex'        => 'Số điện thoại không đúng định dạng.',
            'password.confirmed' => 'Mật khẩu mới không khớp.',
        ]);

        // Logic mật khẩu: Chỉ cập nhật nếu người dùng nhập
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $targetUser->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật thông tin nhân sự thành công.');
    }

    /**
     * XÓA TÀI KHOẢN (DESTROY) - CỰC KỲ CHẶT CHẼ
     */
    public function destroy($id)
    {
        $targetUser = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])->findOrFail($id);
        $currentUser = Auth::user();

        // 1. Bảo vệ Super Admin (ID 1): Bất khả xâm phạm
        if ($targetUser->id === 1) {
            return back()->with('error', 'NGHIÊM CẤM: Không thể xóa tài khoản Super Admin khởi tạo hệ thống.');
        }

        // 2. Bảo vệ bản thân: Không tự xóa mình
        if ($targetUser->id === $currentUser->id) {
            return back()->with('error', 'Bạn không thể xóa tài khoản đang đăng nhập.');
        }

        // 3. Phân cấp: Staff không được xóa Admin
        if ($currentUser->role !== User::ROLE_ADMIN && $targetUser->role === User::ROLE_ADMIN) {
            return back()->with('error', 'Bạn không đủ thẩm quyền để xóa tài khoản Quản trị viên.');
        }

        $targetUser->delete();

        return back()->with('success', 'Đã xóa tài khoản nhân sự khỏi hệ thống.');
    }
}