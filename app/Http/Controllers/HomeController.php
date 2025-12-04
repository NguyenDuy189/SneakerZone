<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // Hiển thị sản phẩm theo danh mục
    public function category($slug)
    {
        // 1. Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->firstOrFail();

        // 2. Lấy danh sách sản phẩm theo danh mục
        $products = $category->products()->paginate(12);

        // 3. Trả về view
        return view('client.category', compact('category', 'products'));
    }
}
