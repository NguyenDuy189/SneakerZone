<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Đặt lại mật khẩu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center bg-[url('https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=2000&auto=format&fit=crop')] bg-cover bg-center">
    
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden p-8 m-4">
        
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-slate-800">Bảo mật tài khoản</h1>
            <p class="text-slate-500 text-sm mt-1">Thiết lập mật khẩu quản trị mới</p>
        </div>

        <form action="{{ route('admin.password.update') }}" method="POST" class="space-y-4">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ request()->email }}" readonly
                    class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 outline-none cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Mật khẩu mới</label>
                <input type="password" name="password" 
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none @error('password') border-rose-500 @enderror"
                    placeholder="••••••••" required autofocus>
                @error('password')
                    <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Xác nhận mật khẩu</label>
                <input type="password" name="password_confirmation" 
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none"
                    placeholder="••••••••" required>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl shadow-lg transition-all transform active:scale-[0.98] mt-2">
                Hoàn tất
            </button>
        </form>
    </div>
</body>
</html>