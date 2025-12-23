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
    // PHẦN 1: QUẢN LÝ CHIẾN DỊCH (Campaign Management)
    // =========================================================================

    public function index(Request $request)
    {
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

        $flashSales = $query->orderByDesc('start_time')->paginate(20)->withQueryString();
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
                    'is_active'  => $request->boolean('is_active', false),
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
    // PHẦN 2: QUẢN LÝ SẢN PHẨM TRONG FLASH SALE (Items Management)
    // =========================================================================

    public function items(FlashSale $flashSale)
    {
        $items = $flashSale->items()
            ->with(['productVariant.product'])
            ->paginate(20);

        return view('admin.flash_sales.items', compact('flashSale', 'items'));
    }

    public function searchProductVariants(Request $request)
    {
        $query = ProductVariant::with('product')
            ->whereHas('product', fn($q) => $q->whereNull('deleted_at'));

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('product', fn($pq) => $pq->where('name', 'like', "%{$keyword}%"))
                  ->orWhere('sku', 'like', "%{$keyword}%");
            });
        } else {
            $query->orderByDesc('stock_quantity');
        }

        $variants = $query->limit(20)->get()->map(function($variant) {
            return [
                'id'             => $variant->id,
                'text'           => $variant->product->name . ' (' . $variant->sku . ')',
                'stock'          => $variant->stock_quantity,
                'original_price' => (float) $variant->original_price_display,
            ];
        });

        return response()->json($variants);
    }

    public function addItem(Request $request, FlashSale $flashSale)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'price'              => 'required|numeric|min:0',
            'quantity'           => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($request->product_variant_id);
        $originalPrice = $variant->original_price_display;

        if ($request->price >= $originalPrice) {
            return back()->withErrors("Giá Flash Sale ({$request->price}) phải nhỏ hơn giá gốc (" . number_format($originalPrice) . ").");
        }

        if ($request->quantity > $variant->stock_quantity) {
            return back()->withErrors("Số lượng Flash Sale ({$request->quantity}) vượt quá tồn kho ({$variant->stock_quantity}).");
        }

        $existsInCampaign = $flashSale->items()
            ->where('product_variant_id', $variant->id)
            ->exists();

        if ($existsInCampaign) {
            return back()->withErrors('Sản phẩm này đã có trong chiến dịch.');
        }

        $isConflict = FlashSaleItem::where('product_variant_id', $variant->id)
            ->whereHas('flashSale', fn($q) => $q
                ->where('id', '!=', $flashSale->id)
                ->where('is_active', true)
                ->where(fn($timeQ) => $timeQ
                    ->where('start_time', '<=', $flashSale->end_time)
                    ->where('end_time', '>=', $flashSale->start_time)
                )
            )->exists();

        if ($isConflict) {
            return back()->withErrors('Sản phẩm đang tham gia Flash Sale khác cùng khung giờ.');
        }

        FlashSaleItem::create([
            'flash_sale_id'      => $flashSale->id,
            'product_variant_id' => $variant->id,
            'price'              => $request->price,
            'quantity'           => $request->quantity,
            'sold_count'         => 0,
        ]);

        return back()->with('success', 'Đã thêm sản phẩm thành công.');
    }

    public function updateItem(Request $request, FlashSale $flashSale, FlashSaleItem $item)
    {
        if ($item->flash_sale_id !== $flashSale->id) abort(403);

        $request->validate([
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = $item->productVariant;
        $originalPrice = $variant->original_price_display;

        if ($request->price >= $originalPrice) {
            return back()->withErrors("Giá Flash Sale phải nhỏ hơn giá gốc (" . number_format($originalPrice) . ").");
        }

        if ($request->quantity > $variant->stock_quantity) {
            return back()->withErrors("Số lượng vượt quá tồn kho hiện tại ({$variant->stock_quantity}).");
        }

        $item->update([
            'price'    => $request->price,
            'quantity' => $request->quantity,
        ]);

        return back()->with('success', 'Cập nhật sản phẩm thành công.');
    }

    public function removeItem(FlashSale $flashSale, FlashSaleItem $item)
    {
        if ($item->flash_sale_id !== $flashSale->id) abort(403);

        $item->delete();
        return back()->with('success', 'Đã gỡ sản phẩm khỏi chiến dịch.');
    }

    // =========================================================================
    // PHẦN 3: THỐNG KÊ & BÁO CÁO
    // =========================================================================

    public function statistics(FlashSale $flashSale)
    {
        $totalItems = $flashSale->items()->count();
        $items = $flashSale->items;

        $totalStockAllocated = $items->sum('quantity');
        $totalSold           = $items->sum('sold_count');
        $totalRevenue        = $items->sum(fn($item) => $item->price * $item->sold_count);
        $sellThroughRate     = $totalStockAllocated > 0
            ? round(($totalSold / $totalStockAllocated) * 100, 2)
            : 0;

        $topProducts = $flashSale->items()
            ->orderByDesc('sold_count')
            ->take(5)
            ->with('productVariant.product')
            ->get();

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

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function validateCampaign(Request $request, $ignoreId = null)
    {
        $rules = [
            'name'       => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
            'is_active'  => 'nullable|boolean',
        ];

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
}
