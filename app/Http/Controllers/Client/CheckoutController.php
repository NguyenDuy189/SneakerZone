<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // Dùng để gọi API MoMo/Zalo
use Illuminate\Support\Str;

// Import các Model
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;     // Model chi tiết đơn hàng
use App\Models\OrderHistory;  // Model lịch sử đơn hàng

class CheckoutController extends Controller
{
    // --- 1. HIỂN THỊ TRANG THANH TOÁN ---
    public function index()
    {
        $cart = Cart::with('items.variant.product')->where('user_id', Auth::id())->first();

        // Nếu giỏ hàng trống thì đá về trang sản phẩm
        if (!$cart || $cart->items->count() == 0) {
            return redirect()->route('client.products.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        // Tính toán tổng tiền
        $subtotal = $cart->items->sum(fn($item) => $item->quantity * ($item->variant->price ?: $item->variant->product->price_min));
        $discount = $cart->discount_amount ?? 0;
        $total = max(0, $subtotal - $discount);

        return view('client.checkout.index', compact('cart', 'subtotal', 'discount', 'total'));
    }

    // --- 2. XỬ LÝ ĐẶT HÀNG (CORE) ---
    public function process(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'customer_email'   => 'required|email|max:255',
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|in:cod,vnpay,momo,zalopay'
        ]);

        $cart = Cart::with('items.variant.product')->where('user_id', Auth::id())->first();
        if (!$cart) return redirect()->route('client.products.index');

        // Tính lại tổng tiền lần cuối để đảm bảo an toàn
        $subtotal = $cart->items->sum(fn($item) => $item->quantity * ($item->variant->price ?: $item->variant->product->price_min));
        $total = max(0, $subtotal - ($cart->discount_amount ?? 0));

        try {
            DB::beginTransaction(); // Bắt đầu giao dịch DB

            // A. Tạo mã đơn hàng: ORD-NămThángNgàyGiờPhút-Random (Ví dụ: ORD-2412181530-ABCD)
            $orderCode = 'ORD-' . date('ymdHi') . '-' . strtoupper(Str::random(4));

            // B. Lưu vào bảng ORDERS
            $order = Order::create([
                'user_id'          => Auth::id(),
                'code'             => $orderCode,
                'customer_name'    => $request->customer_name,
                'customer_phone'   => $request->customer_phone,
                'customer_email'   => $request->customer_email,
                'shipping_address' => $request->shipping_address,
                'note'             => $request->note,
                'total_amount'     => $total,
                'payment_method'   => $request->payment_method,
                'payment_status'   => 'pending', // Mặc định chưa thanh toán
                'status'           => 'pending', // Mặc định chờ xử lý
            ]);

            // C. Lưu vào bảng ORDERITEMS (Thay cho OrderDetail cũ)
            foreach ($cart->items as $item) {
                // Lấy giá ưu tiên: giá biến thể -> giá min sản phẩm
                $price = $item->variant->price ?: $item->variant->product->price_min;
                
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $item->quantity,
                    'price'              => $price,
                    'total'              => $price * $item->quantity,
                ]);
            }

            // D. Lưu vào bảng ORDERHISTORY (Lịch sử khởi tạo)
            OrderHistory::create([
                'order_id'    => $order->id,
                'action'      => 'Tạo đơn hàng',
                'description' => 'Đơn hàng được tạo mới. Phương thức thanh toán: ' . strtoupper($request->payment_method),
            ]);

            // E. Xóa giỏ hàng
            $cart->items()->delete();
            $cart->delete();

            DB::commit(); // Lưu tất cả vào DB

            // --- ĐIỀU HƯỚNG THANH TOÁN ---
            switch ($request->payment_method) {
                case 'vnpay':
                    return $this->createVnpayUrl($order);
                case 'momo':
                    return $this->createMomoUrl($order);
                case 'zalopay':
                    return $this->createZaloPayUrl($order);
                case 'cod':
                default:
                    return redirect()->route('client.checkouts.success')->with('success', 'Đặt hàng thành công!');
            }

        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác nếu lỗi
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // ====================================================
    // 3. TÍCH HỢP VNPAY
    // ====================================================
    private function createVnpayUrl($order)
    {
        $vnp_Url = env('VNP_URL');
        $vnp_Returnurl = route('client.checkouts.vnpay_return');
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');

        $vnp_TxnRef = $order->code; 
        $vnp_OrderInfo = "Thanh toan don hang " . $order->code;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total_amount * 100; // VNPAY nhân 100
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return redirect($vnp_Url);
    }

    public function vnpayReturn(Request $request)
    {
        if ($request->vnp_ResponseCode == "00") {
            // Cập nhật trạng thái đơn hàng
            $this->updatePaymentStatus($request->vnp_TxnRef, 'VNPAY', $request->vnp_TransactionNo);
            return redirect()->route('client.checkouts.success')->with('success', 'Thanh toán VNPAY thành công!');
        }
        return redirect()->route('client.checkouts.index')->with('error', 'Thanh toán VNPAY thất bại hoặc bị hủy.');
    }


    // ====================================================
    // 4. TÍCH HỢP MOMO
    // ====================================================
    private function createMomoUrl($order)
    {
        $endpoint = env('MOMO_ENDPOINT');
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        // MoMo yêu cầu orderId phải duy nhất mỗi lần request
        // Ta nối thêm timestamp để nhỡ khách hủy thanh toán rồi ấn lại thì vẫn được
        $requestId = $order->code . '_' . time(); 
        $orderId = $requestId;
        
        $orderInfo = "Thanh toan don hang " . $order->code;
        $amount = (string)$order->total_amount;
        $redirectUrl = route('client.checkouts.momo_return');
        $ipnUrl = route('client.checkouts.momo_return'); // Trong môi trường test dùng chung
        $extraData = "";

        $rawHash = "accessKey=" . $accessKey . 
                   "&amount=" . $amount . 
                   "&extraData=" . $extraData . 
                   "&ipnUrl=" . $ipnUrl . 
                   "&orderId=" . $orderId . 
                   "&orderInfo=" . $orderInfo . 
                   "&partnerCode=" . $partnerCode . 
                   "&redirectUrl=" . $redirectUrl . 
                   "&requestId=" . $requestId . 
                   "&requestType=captureWallet";

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'Website Ban Hang',
            'storeId' => 'MomoTestStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => 'captureWallet',
            'signature' => $signature
        ];

        try {
            $response = Http::post($endpoint, $data);
            $json = $response->json();
            
            if(isset($json['payUrl'])) {
                return redirect($json['payUrl']);
            }
            return back()->with('error', 'MoMo Error: ' . ($json['message'] ?? 'Unknown'));
        } catch (\Exception $e) {
            return back()->with('error', 'Không kết nối được MoMo: ' . $e->getMessage());
        }
    }

    public function momoReturn(Request $request)
    {
        if ($request->resultCode == '0') {
            // Tách mã đơn gốc ra (bỏ phần _time phía sau)
            $realOrderCode = explode('_', $request->orderId)[0];
            
            $this->updatePaymentStatus($realOrderCode, 'MoMo', $request->transId);
            return redirect()->route('client.checkouts.success')->with('success', 'Thanh toán MoMo thành công!');
        }
        return redirect()->route('client.checkouts.index')->with('error', 'Thanh toán MoMo thất bại.');
    }


    // ====================================================
    // 5. TÍCH HỢP ZALOPAY
    // ====================================================
    private function createZaloPayUrl($order)
    {
        $config = [
            "app_id" => env('ZALO_APP_ID'),
            "key1" => env('ZALO_KEY1'),
            "key2" => env('ZALO_KEY2'),
            "endpoint" => env('ZALO_ENDPOINT')
        ];

        $embeddata = json_encode(['redirecturl' => route('client.checkouts.zalopay_return')]);
        $items = json_encode([]); 
        $transID = rand(0,1000000); 
        
        // ZaloPay yêu cầu mã giao dịch: yymmdd_appid_transid
        $app_trans_id = date("ymd") . "_" . $config["app_id"] . "_" . $transID;

        // Lưu tạm app_trans_id vào session để lát check lúc return
        session(['zalopay_txn_ref' => $app_trans_id, 'zalopay_order_code' => $order->code]);

        $params = [
            "app_id" => $config["app_id"],
            "app_user" => $order->customer_name ?: "KhachHang",
            "app_time" => round(microtime(true) * 1000),
            "amount" => (int)$order->total_amount,
            "app_trans_id" => $app_trans_id,
            "embed_data" => $embeddata,
            "item" => $items,
            "description" => "Thanh toan don hang " . $order->code,
            "bank_code" => ""
        ];

        // Tạo chữ ký MAC
        $data = $config["app_id"] . "|" . $params["app_trans_id"] . "|" . $params["app_user"] . "|" . $params["amount"] . "|" . $params["app_time"] . "|" . $params["embed_data"] . "|" . $params["item"];
        $params["mac"] = hash_hmac("sha256", $data, $config["key1"]);

        try {
            $resp = Http::post($config["endpoint"], $params);
            $result = $resp->json();
    
            if (isset($result['return_code']) && $result['return_code'] == 1) {
                return redirect($result['order_url']);
            }
            return back()->with('error', 'ZaloPay Error: ' . ($result['return_message'] ?? 'Unknown'));

        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi kết nối ZaloPay');
        }
    }

    public function zaloPayReturn(Request $request)
    {
        // Kiểm tra kết quả trả về từ ZaloPay (status = 1 là thành công)
        // Lưu ý: Đây là kiểm tra đơn giản phía Client. Đúng chuẩn cần dùng Callback Server-to-Server.
        if ($request->status == 1) {
            $orderCode = session('zalopay_order_code');
            if($orderCode) {
                $this->updatePaymentStatus($orderCode, 'ZaloPay', $request->apptransid);
                return redirect()->route('client.checkouts.success');
            }
        }
        return redirect()->route('client.checkouts.index')->with('error', 'Thanh toán ZaloPay thất bại');
    }

    // ====================================================
    // 6. HELPER: CẬP NHẬT TRẠNG THÁI & GHI LOG
    // ====================================================
    private function updatePaymentStatus($orderCode, $channel, $transactionId = null)
    {
        $order = Order::where('code', $orderCode)->first();
        
        if ($order && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->save();

            // Ghi lịch sử quan trọng này
            OrderHistory::create([
                'order_id' => $order->id,
                'action' => 'Thanh toán thành công',
                'description' => "Khách đã thanh toán qua cổng $channel. Mã GD: " . ($transactionId ?? 'N/A'),
            ]);
        }
    }

    // --- 7. TRANG THÔNG BÁO THÀNH CÔNG ---
    public function success()
    {
        return view('client.checkout.success');
    }
}