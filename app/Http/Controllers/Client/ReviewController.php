<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\Order; // Để kiểm tra đã mua hàng chưa (nếu cần)

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'required|string|min:10|max:500', // Ít nhất 10 ký tự
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá!',
            'comment.min'     => 'Nội dung đánh giá quá ngắn (tối thiểu 10 ký tự).',
        ]);

        try {
            $userId = Auth::id();
            $productId = $request->product_id;

            // --- TÙY CHỌN: CHỈ CHO PHÉP ĐÁNH GIÁ KHI ĐÃ MUA HÀNG ---
            // Nếu bạn muốn bật tính năng này, hãy bỏ comment đoạn dưới:
            /*
            $hasPurchased = Order::where('user_id', $userId)
                ->whereHas('orderDetails', function($q) use ($productId) {
                    $q->where('product_id', $productId);
                })
                ->where('status', 'completed') // Chỉ đơn hàng đã hoàn thành
                ->exists();

            if (!$hasPurchased) {
                return back()->with('error', 'Bạn cần mua sản phẩm này để viết đánh giá.');
            }
            */
            // ---------------------------------------------------------

            // 2. Kiểm tra xem đã đánh giá chưa (Tránh spam 1 người đánh giá nhiều lần)
            $existingReview = Review::where('user_id', $userId)
                                    ->where('product_id', $productId)
                                    ->first();

            if ($existingReview) {
                // Nếu có rồi -> Cập nhật lại
                $existingReview->update([
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                    'status' => 'approved' // Tự động duyệt khi sửa
                ]);
                $message = 'Đánh giá của bạn đã được cập nhật!';
            } else {
                // Nếu chưa -> Tạo mới
                Review::create([
                    'user_id'    => $userId,
                    'product_id' => $productId,
                    'rating'     => $request->rating,
                    'comment'    => $request->comment,
                    'status'     => 'approved' // 'approved' để hiện ngay, 'pending' nếu cần duyệt
                ]);
                $message = 'Cảm ơn bạn đã đánh giá sản phẩm!';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}