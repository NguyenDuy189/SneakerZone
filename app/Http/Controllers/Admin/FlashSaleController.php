<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FlashSaleController extends Controller
{
    // =========================================================================
    // PHẦN 1: QUẢN LÝ CHIẾN DỊCH (CAMPAIGN MANAGEMENT)
    // =========================================================================

    public function index(Request $request)
    {
        // Eager load các item để đếm số lượng sản phẩm nhanh chóng
        $query = FlashSale::withCount('items');

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('status')) {
            $now = Carbon::now();
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true)
                        ->where('start_time', '<=', $now)
                        ->where('end_time', '>=', $now);
                    break;
                case 'upcoming':
                    $query->where('start_time', '>', $now);
                    break;
                case 'expired':
                    $query->where('end_time', '<', $now);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        $flashSales = $query->orderBy('start_time', 'desc')->paginate(20)->withQueryString();

        return view('admin.flash_sales.index', compact('flashSales'));
    }

    public function create()
    {
        return view('admin.flash_sales.create');
    }

    public function store(Request $request)
    {
        $this->validateCampaign($request);

        try {
            DB::transaction(function () use ($request) {
                $flashSale = FlashSale::create([
                    'name'       => $request->name,
                    'start_time' => $request->start_time,
                    'end_time'   => $request->end_time,
                    'is_active'  => $request->boolean('is_active', true),
                ]);

                // Ghi log audit
                Log::info("Admin ID " . Auth::id() . " đã tạo Flash Sale: {$flashSale->name}");
            });

            return redirect()->route('admin.flash_sales.index')
                ->with('success', 'Tạo chiến dịch Flash Sale thành công.');
        } catch (\Exception $e) {
            Log::error("Lỗi tạo Flash Sale: " . $e->getMessage());
            return back()->withInput()->withErrors('Lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    public function edit(FlashSale $flashSale)
    {
        return view('admin.flash_sales.edit', compact('flashSale'));
    }

    public function update(Request $request, FlashSale $flashSale)
    {
        $this->validateCampaign($request, $flashSale->id);

        try {
            DB::transaction(function () use ($request, $flashSale) {
                $flashSale->update([
                    'name'       => $request->name,
                    'start_time' => $request->start_time,
                    'end_time'   => $request->end_time,
                    'is_active'  => $request->boolean('is_active'),
                ]);
            });

            return redirect()->route('admin.flash_sales.index')
                ->with('success', 'Cập nhật chiến dịch Flash Sale thành công.');
        } catch (\Exception $e) {
            Log::error("Lỗi update Flash Sale ID {$flashSale->id}: " . $e->getMessage());
            return back()->withErrors('Không thể cập nhật. Lỗi hệ thống.');
        }
    }

    public function destroy(FlashSale $flashSale)
    {
        try {
            DB::transaction(function () use ($flashSale) {
                // Xóa mềm hoặc xóa cứng tuỳ policy, ở đây dùng xóa items trước
                $flashSale->items()->delete();
                $flashSale->delete();

                Log::warning("Admin ID " . Auth::id() . " đã xóa Flash Sale ID {$flashSale->id}");
            });

            return back()->with('success', 'Đã xóa chiến dịch và toàn bộ sản phẩm liên quan.');
        } catch (\Exception $e) {
            Log::error("Lỗi xóa Flash Sale ID {$flashSale->id}: " . $e->getMessage());
            return back()->withErrors('Không thể xóa chiến dịch này.');
        }
    }

    // =========================================================================
    // PHẦN 2: QUẢN LÝ SẢN PHẨM (ITEMS MANAGEMENT) - LOGIC CAO CẤP
    // =========================================================================

    public function items(FlashSale $flashSale)
    {
        // Chỉ load danh sách đã add, không load variants ở đây để tránh nặng
        $items = $flashSale->items()
            ->with(['productVariant.product'])
            ->paginate(20);

        return view('admin.flash_sales.items', compact('flashSale', 'items'));
    }

    /**
     * API Tìm kiếm sản phẩm (Dành cho Select2/Ajax Search trên Frontend)
     * Đã bao gồm fix lỗi giá 0đ và logic gợi ý tồn kho
     */
    public function searchProductVariants(Request $request)
    {
        $query = ProductVariant::with('product')
            ->whereHas('product', function($q) {
                // Chỉ lấy sản phẩm chưa bị xóa (nếu dùng soft delete)
                $q->whereNull('deleted_at'); 
            });

        // 1. LOGIC TÌM KIẾM (Nếu có từ khóa)
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function($q) use ($keyword) {
                $q->whereHas('product', function($pq) use ($keyword) {
                    $pq->where('name', 'like', "%{$keyword}%");
                })
                ->orWhere('sku', 'like', "%{$keyword}%");
            });
        } 
        // 2. LOGIC GỢI Ý (Nếu không nhập gì -> Lấy 20 sản phẩm tồn kho cao nhất)
        else {
            $query->orderBy('stock_quantity', 'desc');
        }

        $variants = $query->limit(20)->get()->map(function($variant) {
            // FIX LỖI GIÁ 0 ĐỒNG:
            // Ưu tiên giá của Variant, nếu = 0 thì lấy giá của Product cha
            $realPrice = $variant->price > 0 ? $variant->price : ($variant->product->price ?? 0);

            return [
                'id' => $variant->id,
                // Hiển thị: Tên SP (SKU)
                'text' => $variant->product->name . ' (' . $variant->sku . ')',
                'stock' => $variant->stock_quantity,
                'original_price' => (float) $realPrice, // Ép kiểu float
            ];
        });

        return response()->json($variants);
    }

    public function addItem(Request $request, FlashSale $flashSale)
    {
        // 1. Validate cơ bản
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'price'              => 'required|numeric|min:0',
            'quantity'           => 'required|integer|min:1',
        ], [
            'product_variant_id.required' => 'Chưa chọn sản phẩm.',
            'product_variant_id.exists'   => 'Sản phẩm không tồn tại.',
            'price.min'                   => 'Giá bán không hợp lệ.',
            'quantity.min'                => 'Số lượng phải lớn hơn 0.',
        ]);

        // 2. Lấy thông tin Variant để check logic nghiệp vụ
        $variant = ProductVariant::findOrFail($request->product_variant_id);
        
        // FIX LỖI GIÁ: Lấy giá chuẩn (variant hoặc product)
        $originalPrice = $variant->price > 0 ? $variant->price : ($variant->product->price ?? 0);

        // LOGIC CAO CẤP 1: Chống bán lỗ/sai giá (Flash Sale phải rẻ hơn giá gốc)
        if ($request->price >= $originalPrice) {
            return back()->withErrors("Giá Flash Sale ({$request->price}) phải nhỏ hơn giá gốc (" . number_format($originalPrice) . ").");
        }

        // LOGIC CAO CẤP 2: Chống bán khống (Số lượng Flash Sale <= Tồn kho thực tế)
        if ($request->quantity > $variant->stock_quantity) {
            return back()->withErrors("Số lượng Flash Sale ({$request->quantity}) vượt quá tồn kho thực tế ({$variant->stock_quantity}).");
        }

        // LOGIC CAO CẤP 3: Chống trùng lặp sản phẩm trong chiến dịch này
        $existsInCampaign = $flashSale->items()->where('product_variant_id', $variant->id)->exists();
        if ($existsInCampaign) {
            return back()->withErrors('Sản phẩm này đã có trong chiến dịch hiện tại.');
        }

        // LOGIC CAO CẤP 4: Chống xung đột thời gian (Time Overlap)
        // Kiểm tra xem sản phẩm này có đang chạy ở một Flash Sale KHÁC trong cùng khung giờ không?
        $isConflict = FlashSaleItem::where('product_variant_id', $variant->id)
            ->whereHas('flashSale', function ($q) use ($flashSale) {
                $q->where('id', '!=', $flashSale->id) // Không tính chiến dịch hiện tại
                    ->where('is_active', true) // Chỉ tính chiến dịch đang bật
                    ->where(function ($timeQ) use ($flashSale) {
                        // Logic trùng giờ: (StartA <= EndB) and (EndA >= StartB)
                        $timeQ->where('start_time', '<=', $flashSale->end_time)
                            ->where('end_time', '>=', $flashSale->start_time);
                    });
            })->exists();

        if ($isConflict) {
            return back()->withErrors('Sản phẩm này đang tham gia một Flash Sale khác trong cùng khung giờ. Vui lòng kiểm tra lại lịch.');
        }

        try {
            FlashSaleItem::create([
                'flash_sale_id'      => $flashSale->id,
                'product_variant_id' => $variant->id,
                'price'              => $request->price,
                'quantity'           => $request->quantity,
                'sold_count'         => 0,
            ]);

            return back()->with('success', 'Đã thêm sản phẩm thành công.');
        } catch (\Exception $e) {
            Log::error("Add Item Error: " . $e->getMessage());
            return back()->withErrors('Lỗi hệ thống khi thêm sản phẩm.');
        }
    }

    public function updateItem(Request $request, FlashSale $flashSale, FlashSaleItem $item)
    {
        if ($item->flash_sale_id !== $flashSale->id) abort(403);

        $request->validate([
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = $item->productVariant;
        $originalPrice = $variant->price > 0 ? $variant->price : ($variant->product->price ?? 0);

        // Check lại Logic giá và tồn kho khi update
        if ($request->price >= $originalPrice) {
            return back()->withErrors("Giá Flash Sale phải thấp hơn giá gốc (" . number_format($originalPrice) . ").");
        }

        if ($request->quantity > $variant->stock_quantity) {
            return back()->withErrors("Số lượng vượt quá tồn kho hiện tại ({$variant->stock_quantity}).");
        }

        $item->update([
            'price'    => $request->price,
            'quantity' => $request->quantity
        ]);

        return back()->with('success', 'Cập nhật thông tin sản phẩm thành công.');
    }

    public function removeItem(FlashSale $flashSale, FlashSaleItem $item)
    {
        if ($item->flash_sale_id !== $flashSale->id) abort(403);
        $item->delete();
        return back()->with('success', 'Đã gỡ sản phẩm khỏi chiến dịch.');
    }

    // =========================================================================
    // PRIVATE HELPERS (Clean Code)
    // =========================================================================

    private function validateCampaign(Request $request, $ignoreId = null)
    {
        $rules = [
            'name'       => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
            'is_active'  => 'nullable|boolean',
        ];

        // Nếu tạo mới, bắt buộc ngày bắt đầu >= hiện tại (chút xíu dư địa cho delay mạng)
        if (!$ignoreId) {
            $rules['start_time'] .= '|after_or_equal:now';
        }

        $messages = [
            'name.required'             => 'Tên chiến dịch là bắt buộc.',
            'start_time.required'       => 'Vui lòng chọn giờ bắt đầu.',
            'start_time.after_or_equal' => 'Thời gian bắt đầu không được ở trong quá khứ.',
            'end_time.after'            => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ];

        $request->validate($rules, $messages);
    }

    // =========================================================================
    // PHẦN 3: THỐNG KÊ & BÁO CÁO (ANALYTICS)
    // =========================================================================

    /**
     * Dashboard thống kê hiệu quả của Flash Sale
     */
    public function statistics(FlashSale $flashSale)
    {
        // 1. Tổng quan (KPIs)
        $totalItems = $flashSale->items()->count();

        // Tính toán trên Collection để giảm query DB (do số lượng item trong 1 flash sale thường không quá lớn)
        // Nếu item lên tới hàng nghìn, nên chuyển sang dùng DB::raw()
        $items = $flashSale->items;

        $totalStockAllocated = $items->sum('quantity'); // Tổng số lượng cam kết chạy
        $totalSold = $items->sum('sold_count');         // Tổng số lượng đã bán (Giả sử bạn có cột sold_count trong bảng flash_sale_items)
        $totalRevenue = $items->sum(function ($item) {
            return $item->price * $item->sold_count;
        });

        // Tỉ lệ bán hết (Sell-through rate)
        $sellThroughRate = $totalStockAllocated > 0
            ? round(($totalSold / $totalStockAllocated) * 100, 2)
            : 0;

        // 2. Top sản phẩm bán chạy nhất
        $topProducts = $flashSale->items()
            ->orderByDesc('sold_count')
            ->take(5)
            ->with('productVariant.product')
            ->get();

        // 3. Sản phẩm chưa bán được cái nào (Để admin biết đường hạ giá hoặc marketing)
        $unsoldProducts = $flashSale->items()
            ->where('sold_count', 0)
            ->with('productVariant.product')
            ->take(5)
            ->get();

        return view('admin.flash_sales.statistics', compact(
            'flashSale',
            'totalItems',
            'totalStockAllocated',
            'totalSold',
            'totalRevenue',
            'sellThroughRate',
            'topProducts',
            'unsoldProducts'
        ));
    }
}