<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    // 1. Về chúng tôi
    public function about()
    {
        return view('client.pages.about');
    }

    // 2. Liên hệ
    public function contact()
    {
        return view('client.pages.contact');
    }

    // 3. Hệ thống cửa hàng
    public function stores()
    {
        return view('client.pages.stores');
    }

    // 4. Hướng dẫn mua hàng
    public function buyingGuide()
    {
        return view('client.pages.buying-guide');
    }

    // 5. Chính sách đổi trả
    public function returnPolicy()
    {
        return view('client.pages.return-policy');
    }

    // 6. Chính sách bảo mật
    public function privacyPolicy()
    {
        return view('client.pages.privacy-policy');
    }
    
    // 7. Tin tức (Tạm thời trả về trang tĩnh, sau này có thể làm dynamic sau)
    public function blog()
    {
        return view('client.pages.blog');
    }
}