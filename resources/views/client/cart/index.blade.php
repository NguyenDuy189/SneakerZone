@extends('client.layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
<div class="container my-5">
    <h2 class="mb-4">🛒 Giỏ hàng của bạn</h2>

    @if(!empty($cart) && count($cart) > 0)
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tạm tính</th>
                </tr>
            </thead>
            <tbody>
@php $total = 0; @endphp
@foreach($cart as $item)
@php $subtotal = $item['price'] * $item['quantity']; $total += $subtotal; @endphp
<tr>
    <td>{{ $item['name'] }}</td>

    <td>{{ number_format($item['price']) }} VNĐ</td>

    <td>
        <form action="{{ route('cart.update', $item['id']) }}" method="POST" class="d-flex">
    @csrf
    <input
        type="number"
        name="quantity"   
        value="{{ $item['quantity'] }}"
        min="1"
        class="form-control form-control-sm me-2"
        style="width:70px"
    >
    <button class="btn btn-sm btn-outline-dark">Cập nhật</button>
</form>
    </td>

    <td>{{ number_format($subtotal) }} VNĐ</td>

    <td>
        <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Xóa</button>
        </form>
    </td>
</tr>
@endforeach
</tbody>
<tfoot>
<tr>
    <th colspan="3">Tổng tiền</th>
    <th>{{ number_format($total) }} VNĐ</th>
</tr>
</tfoot>

        </table>
    @else
        <p>Giỏ hàng đang trống.</p>
    @endif
</div>
@endsection
