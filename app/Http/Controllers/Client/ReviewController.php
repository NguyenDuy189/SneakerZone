<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Order; 

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'required|string|min:10|max:1000',
        ], [
            'rating.required'  => 'Vui lòng chọn số sao.',
            'comment.required' => 'Vui lòng nhập nội dung đánh giá.',
            'comment.min'      => 'Nội dung quá ngắn (tối thiểu 10 ký tự).',
        ]);

        try {
            $userId = Auth::id();
            $productId = $request->product_id;

            // Dùng updateOrCreate để: Chưa có -> Tạo mới | Có rồi -> Cập nhật
            $review = Review::updateOrCreate(
                [
                    'user_id'    => $userId,
                    'product_id' => $productId
                ],
                [
                    'rating'      => $request->rating,
                    'comment'     => $request->comment,
                    'is_approved' => true, // Duyệt luôn
                ]
            );

            $message = $review->wasRecentlyCreated 
                ? 'Cảm ơn bạn đã đánh giá sản phẩm!' 
                : 'Đánh giá của bạn đã được cập nhật thành công!';

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại.');
        }
    }
}