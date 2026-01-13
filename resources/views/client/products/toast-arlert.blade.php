<div x-data="{ 
        show: false, 
        title: '',
        message: '', 
        type: 'success', 
        actionUrl: '',
        actionText: '',
        percent: 100,
        interval: null,
        timeout: 5000, 
        
        init() {
            // 1. Nghe sự kiện từ JS
            window.addEventListener('show-toast', event => {
                this.trigger(
                    event.detail.message, 
                    event.detail.type, 
                    event.detail.actionUrl, 
                    event.detail.actionText
                );
            });

            // 2. Nghe sự kiện từ Laravel Session (Flash message)
            @if(session()->has('success'))
                this.trigger('{{ session('success') }}', 'success', '{{ session('action_url') }}', '{{ session('action_text') }}');
            @elseif(session()->has('error'))
                this.trigger('{{ session('error') }}', 'error');
            @endif
        },

        trigger(msg, type = 'success', url = '', text = '') {
            this.show = true;
            this.message = msg;
            this.type = type;
            this.actionUrl = url;
            this.actionText = text;
            this.percent = 100;

            // Tự động đặt tiêu đề nếu không có
            if (type === 'success') this.title = 'Thành công!';
            else if (type === 'error') this.title = 'Đã xảy ra lỗi!';
            else this.title = 'Thông báo';

            this.startTimer();
        },

        startTimer() {
            if (this.interval) clearInterval(this.interval);
            this.interval = setInterval(() => {
                if (this.percent > 0) {
                    this.percent -= 1; 
                } else {
                    this.close();
                }
            }, this.timeout / 100);
        },

        pauseTimer() {
            clearInterval(this.interval);
        },

        resumeTimer() {
            if (this.show) this.startTimer();
        },

        close() {
            this.show = false;
            setTimeout(() => { 
                this.percent = 100; 
                this.actionUrl = ''; 
            }, 300); 
            clearInterval(this.interval);
        }
    }" 
    class="fixed top-24 right-5 z-[9999] flex flex-col gap-2 pointer-events-none"
    x-cloak>

    <div x-show="show" 
         @mouseenter="pauseTimer()" 
         @mouseleave="resumeTimer()"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-8 scale-95"
         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
         x-transition:leave-end="opacity-0 translate-x-8 scale-95"
         class="relative overflow-hidden w-full max-w-sm bg-white rounded-xl shadow-2xl border-l-4 flex flex-col pointer-events-auto"
         :class="type === 'success' ? 'border-emerald-500' : 'border-rose-500'">

        <div class="flex p-4 gap-4 items-start">
            {{-- Icon --}}
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
                     :class="type === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                    <i class="text-sm fa-solid" :class="type === 'success' ? 'fa-check' : 'fa-xmark'"></i>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex-1 pt-0.5">
                <h4 class="text-sm font-bold text-gray-900" x-text="title"></h4>
                <p class="text-sm text-gray-600 mt-1 leading-relaxed" x-text="message"></p>

                <div x-show="actionUrl" class="mt-3">
                    <a :href="actionUrl" 
                       class="inline-flex items-center text-xs font-bold uppercase tracking-wider transition-colors duration-200"
                       :class="type === 'success' ? 'text-emerald-600 hover:text-emerald-700' : 'text-rose-600 hover:text-rose-700'">
                        <span x-text="actionText || 'Xem chi tiết'"></span>
                        <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                    </a>
                </div>
            </div>

            {{-- Close Button --}}
            <button @click="close()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Progress Bar --}}
        <div class="h-1 w-full bg-gray-100 absolute bottom-0 left-0">
            <div class="h-full transition-all duration-100 ease-linear"
                 :class="type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
                 :style="'width: ' + percent + '%'"></div>
        </div>
    </div>
</div>