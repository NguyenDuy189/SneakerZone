<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;

class ReviewController extends Controller
{
    /**
     * Danh sách đánh giá
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product'])->latest('id');

        // ----------------------------
        // Tìm kiếm theo từ khóa
        // ----------------------------
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {

                // Tìm theo sản phẩm
                $q->whereHas('product', function ($p) use ($keyword) {
                    $p->where('products.name', 'like', "%{$keyword}%");
                })

                // Hoặc theo người dùng
                ->orWhereHas('user', function ($u) use ($keyword) {
                    $u->where('users.full_name', 'like', "%{$keyword}%")
                    ->orWhere('users.email', 'like', "%{$keyword}%");
                });
            });
        }

        // ----------------------------
        // Lọc theo số sao
        // ----------------------------
        if ($request->filled('rating') && $request->rating != 0) {
            $query->where('rating', $request->rating);
        }

        // ----------------------------
        // Lọc theo trạng thái duyệt
        // ----------------------------
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_approved', $request->status);
        }

        // ----------------------------
        // Lọc theo ngày (ĐÃ FIX)
        // ----------------------------
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $query->whereDate('created_at', $date);
        }


        $reviews = $query->paginate(10)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Lưu đánh giá (User gửi)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:500',
        ], [
            'product_id.required' => 'Thiếu mã sản phẩm.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'order_id.required' => 'Thiếu mã đơn hàng.',
            'order_id.exists' => 'Đơn hàng không tồn tại.',
            'rating.required' => 'Bạn phải chọn số sao đánh giá.',
            'rating.min' => 'Số sao tối thiểu là 1.',
            'rating.max' => 'Số sao tối đa là 5.',
            'comment.required' => 'Bạn chưa nhập nội dung đánh giá.',
            'comment.min' => 'Đánh giá tối thiểu 10 ký tự.',
            'comment.max' => 'Đánh giá tối đa 500 ký tự.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Kiểm tra user có thật sự đã mua sản phẩm không
            $purchased = OrderItem::where('order_id', $request->order_id)
                ->where('product_id', $request->product_id)
                ->exists();

            if (!$purchased) {
                return back()->with('error', 'Bạn chỉ có thể đánh giá sản phẩm đã mua.');
            }

            // Kiểm tra user đã đánh giá sản phẩm này trong đơn hàng này chưa
            $exists = Review::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->where('order_id', $request->order_id)
                ->exists();

            if ($exists) {
                return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
            }

            Review::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'order_id' => $request->order_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_approved' => false, // chờ duyệt
            ]);

            return back()->with('success', 'Gửi đánh giá thành công. Vui lòng chờ duyệt.');
        } catch (\Exception $e) {
            return back()->with('error', 'Đã xảy ra lỗi, vui lòng thử lại.');
        }
    }

    /**
     * Admin duyệt hoặc hủy duyệt review
     */
    public function approve(Request $request, $id)
    {
        try {
            $review = Review::findOrFail($id);

            $review->is_approved = !$review->is_approved;
            $review->save();

            return back()->with('success', 'Cập nhật trạng thái đánh giá thành công.');
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Không tìm thấy đánh giá.');
        }
    }

    /**
     * Xóa review
     */
    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);

            $review->delete();

            return back()->with('success', 'Xóa đánh giá thành công.');
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Đánh giá không tồn tại.');
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể xóa đánh giá.');
        }
    }
}
