@extends('admin.layouts.app')

@section('title', 'Thùng rác đơn giao hàng')
@section('header', 'Thùng rác đơn giao hàng')

@section('content')
<div class="container px-6 mx-auto pb-10">

    <!-- Header Tools -->
    <div class="flex flex-col md:flex-row justify-between items-center my-6 gap-4">
        <h2 class="text-2xl font-bold text-slate-800">Danh sách đơn giao hàng đã xóa</h2>
        <div class="flex gap-3">
            <a href="{{ route('admin.shipping.index') }}" class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 mb-6 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200 shadow-sm flex items-center animate-fade-in-down">
            <i class="fa-solid fa-circle-check mr-2 text-lg"></i> {{ session('success') }}
        </div>
    @endif

    <!-- DATA TABLE -->
    <div class="w-full overflow-hidden rounded-xl shadow-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap text-left">
                <thead>
                    <tr class="text-xs font-bold tracking-wider text-slate-500 uppercase border-b border-slate-200 bg-slate-50">
                        <th class="px-4 py-4 w-24">Mã đơn</th>
                        <th class="px-4 py-4">Khách hàng</th>
                        <th class="px-4 py-4">Shipper</th>
                        <th class="px-4 py-4">Ngày xóa</th>
                        <th class="px-4 py-4 text-center w-48">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shippings as $shipping)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-4 py-3 font-mono text-slate-700">{{ $shipping->tracking_code }}</td>
                            <td class="px-4 py-3">{{ $shipping->order?->customer?->full_name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $shipping->shipper?->full_name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $shipping->deleted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="px-4 py-3 text-center flex justify-center gap-2">
                                <form action="{{ route('admin.shipping.restore', $shipping->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-2 bg-emerald-500 text-white rounded hover:bg-emerald-600 transition">Khôi phục</button>
                                </form>
                                <form action="{{ route('admin.shipping.destroy', $shipping->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2 bg-rose-500 text-white rounded hover:bg-rose-600 transition">Xóa vĩnh viễn</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fa-solid fa-truck-fast text-3xl text-slate-300"></i>
                                    </div>
                                    <p class="font-medium text-slate-500">Chưa có đơn hàng nào trong thùng rác.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $shippings->links() }}
        </div>
    </div>
</div>

<style>
.animate-fade-in-down { animation: fadeInDown 0.5s ease-out; }
@keyframes fadeInDown {
    from { opacity: 0; transform: translate3d(0, -20px, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}
</style>
@endsection
