<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quên mật khẩu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center bg-[url('https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=2000&auto=format&fit=crop')] bg-cover bg-center">
    
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden p-8 m-4 border-t-4 border-indigo-600">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Admin Recovery</h1>
            <p class="text-slate-500 text-sm mt-1">Khôi phục quyền truy cập quản trị</p>
        </div>

        @if (session('status'))
            <div class="bg-indigo-50 text-indigo-700 px-4 py-3 rounded-xl mb-4 text-sm flex items-center gap-2 border border-indigo-200">
                <i class="fa-solid fa-circle-info"></i>
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('admin.password.email') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Email Quản trị</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" 
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none @error('email') border-rose-500 @enderror"
                        placeholder="admin@example.com" required autofocus>
                </div>
                @error('email')
                    <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl shadow-lg transition-all transform active:scale-[0.98]">
                Gửi liên kết
            </button>

            <div class="text-center mt-6">
                <a href="{{ route('admin.login') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-wide">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</body>
</html>