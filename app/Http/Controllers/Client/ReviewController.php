<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'required|string|min:3|max:500',
        ]);

        try {
            // 2. Kiểm tra xem user đã đánh giá sản phẩm này chưa (Tránh spam)
            // (Nếu muốn cho đánh giá nhiều lần thì comment đoạn này lại)
            $exists = Review::where('user_id', Auth::id())
                            ->where('product_id', $request->product_id)
                            ->exists();
            
            if ($exists) {
                return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi!');
            }

            // 3. Tạo Review
            Review::create([
                'user_id'    => Auth::id(),
                'product_id' => $request->product_id,
                'rating'     => $request->rating,
                'comment'    => $request->comment,
                'status'     => 'approved' // <--- QUAN TRỌNG: Tự động duyệt để hiện ngay
            ]);

            return back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}