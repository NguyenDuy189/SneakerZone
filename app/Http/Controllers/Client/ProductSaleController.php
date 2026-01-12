<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductSaleController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->active() // Lọc status = 'published'
            
            // Lọc: Chỉ lấy sản phẩm có ít nhất 1 biến thể thỏa mãn điều kiện Sale
            ->whereHas('variants', function($q) {
                // Sửa tên cột theo đúng DB: sale_price và original_price
                $q->whereNotNull('sale_price')
                  ->where('sale_price', '>', 0)
                  ->whereColumn('sale_price', '<', 'original_price');
            })
            
            // Eager Load: Nạp sẵn variants để dùng ở View
            ->with(['variants' => function($q) {
                $q->whereNotNull('sale_price')
                  ->where('sale_price', '>', 0)
                  ->whereColumn('sale_price', '<', 'original_price')
                  // Sắp xếp giảm giá nhiều nhất lên đầu
                  ->orderByRaw('(original_price - sale_price) DESC');
            }])
            
            ->paginate(12);

        $title = "Săn Deal Hot - Giảm giá cực sốc";
        
        return view('client.products.sale', compact('products', 'title'));
    }
}