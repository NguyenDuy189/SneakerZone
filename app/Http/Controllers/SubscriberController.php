<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeDiscountMail;
use Exception;

class SubscriberController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email'
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã đăng ký rồi.'
        ]);

        try {
            //Subscriber::create(['email' => $request->email]);
            
            // Gửi email (nhớ cấu hình .env nhé)
            Mail::to($request->email)->send(new WelcomeDiscountMail());

            return redirect()->back()->with('success', 'Đã gửi mã giảm giá vào email của bạn!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}