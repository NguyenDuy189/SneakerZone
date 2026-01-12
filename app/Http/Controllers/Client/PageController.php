<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about() {
        return view('client.pages.about');
    }

    public function contact() {
        return view('client.pages.contact');
    }

    // Các trang chính sách (dùng chung layout hoặc tách riêng tùy bạn)
    public function returnPolicy() {
        return view('client.pages.return-policy');
    }

    public function privacyPolicy() {
        return view('client.pages.privacy-policy');
    }
    
    public function buyingGuide() {
        return view('client.pages.buying-guide');
    }

    public function tracking() {
        return view('client.pages.tracking');
    }
    
    // Tạm thời các trang News và Store để static trước
    public function stores() {
        return view('client.pages.stores');
    }
    
    public function news() {
        return view('client.pages.news');
    }
}