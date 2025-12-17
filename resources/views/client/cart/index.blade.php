@extends('client.layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
<div class="max-w-5xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6">Giỏ hàng của bạn</h1>

    @foreach($items as $item)
        <div class="flex justify-between border-b py-4">
            <div>
                <p class="font-semibold">{{ $item->variant?->product?->name ?? 'Sản phẩm không tồn tại' }}</p>
                <p class="text-sm text-gray-500">
                    {{ number_format($item->price ?? $item->variant?->price ?? 0) }}đ
                </p>
            </div>

            <form method="POST" action="{{ route('client.cart.update') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $item->id }}">
                <input type="number" name="quantity" value="{{ $item->quantity }}"
                       class="w-16 border rounded">
                <button class="text-blue-600 text-sm">Cập nhật</button>
            </form>

            <a href="{{ route('client.cart.remove', $item->id) }}"
               class="text-red-500">Xoá</a>
        </div>
    @endforeach

    <form method="POST" action="{{ route('client.cart.checkout') }}" class="mt-6">
        @csrf
        <button class="px-6 py-3 bg-indigo-600 text-white rounded">
            Đặt hàng
        </button>
    </form>
</div>
@endsection
