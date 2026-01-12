<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Sneaker Zone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center bg-[url('https://images.unsplash.com/photo-1556906781-9a412961c28c?q=80&w=2000&auto=format&fit=crop')] bg-cover bg-center">
    
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden p-8 m-4">
        
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-500 text-white mb-4 shadow-lg shadow-amber-500/40">
                <i class="fa-solid fa-key text-xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Quên mật khẩu?</h1>
            <p class="text-slate-500 text-sm mt-1">Đừng lo, hãy nhập email để lấy lại mật khẩu.</p>
        </div>

        {{-- Hiển thị thông báo thành công khi gửi mail xong --}}
        @if (session('status'))
            <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Email đăng ký</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" 
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all outline-none @error('email') border-rose-500 @enderror"
                        placeholder="email@example.com" required autofocus>
                </div>
                @error('email')
                    <p class="text-xs text-rose-500 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-lg transition-all transform active:scale-[0.98] flex justify-center items-center gap-2">
                <span>Gửi link khôi phục</span>
                <i class="fa-solid fa-paper-plane"></i>
            </button>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </form>
    </div>
</body>
</html>