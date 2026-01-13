<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Order, OrderItem, Cart, UserAddress, ProductVariant};
use Illuminate\Support\Facades\{DB, Auth, Log, Http};
use Illuminate\Support\Str;
use Exception;

class CheckoutController extends Controller
{
    /* =====================================================
     | 1. VIEW: TRANG THANH TOÁN (CHECKOUT SCREEN)
     ===================================================== */
    public function index()
    {
        try {
            // 1. Lấy giỏ hàng (Giữ nguyên code cũ của bạn)
            $cart = Cart::with(['items' => function($query) {
                    $query->where('is_selected', true)->with('variant.product');
                }])
                ->where('user_id', Auth::id())
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('client.carts.index')->with('error', 'Bạn chưa chọn sản phẩm nào.');
            }

            // 2. Check tồn kho (Giữ nguyên code cũ của bạn)
            // ... (Đoạn code foreach check stock giữ nguyên) ...

            // 3. TÍNH TOÁN LẠI (SỬA ĐOẠN NÀY)
            $subtotal = $this->calculateSubtotal($cart);

            // --- SỬA: Lấy giảm giá từ DB thay vì Session ---
            $discount = $this->calculateDiscountAmount($cart, $subtotal);

            // Phí ship (Có thể cần logic tính riêng, tạm thời giữ nguyên session nếu bạn xử lý ship ở chỗ khác)
            $shippingFee = (int) session('shipping_fee', 0);
            
            $total = max(0, $subtotal - $discount + $shippingFee);

            // 4. Lấy địa chỉ
            $addresses = UserAddress::where('user_id', Auth::id())->orderByDesc('is_default')->get();
            // Đoạn code debug
            // $couponCode = $cart->discount_code; // Lấy "VIP10"
            // $voucher = \App\Models\Discount::where('code', $couponCode)->first(); // Thay Discount bằng tên Model của bạn

            // dd($voucher); // <--- Chạy lại và xem nó ra NULL hay ra dữ liệu?
            return view('client.checkouts.index', compact('cart', 'subtotal', 'discount', 'shippingFee', 'total', 'addresses'));

        } catch (\Throwable $e) {
            Log::error("CHECKOUT_VIEW_ERROR: " . $e->getMessage());
            return redirect()->route('client.home')->with('error', 'Lỗi tải trang thanh toán.');
        }
    }

    /* =====================================================
     | 2. PROCESS: XỬ LÝ ĐẶT HÀNG (CORE LOGIC)
     ===================================================== */
    public function process(Request $request)
    {
        
        // 1. Validate dữ liệu
        $this->validateCheckout($request);

        DB::beginTransaction();
        try {
            // 2. Lấy giỏ hàng & Check khóa (Lock)
            $cart = Cart::with(['items' => function($query) {
                    $query->where('is_selected', true);
                }])
                ->where('user_id', Auth::id())
                ->lockForUpdate() // Chống race condition khi nhiều người mua cùng lúc
                ->firstOrFail();

            if ($cart->items->isEmpty()) {
                throw new Exception('Giỏ hàng trống. Vui lòng chọn sản phẩm để thanh toán.');
            }

            // 3. Kiểm tra tồn kho
            foreach ($cart->items as $item) {
                // Load biến thể để check stock
                $variant = ProductVariant::find($item->product_variant_id);
                if (!$variant || $variant->stock_quantity < $item->quantity) {
                    throw new Exception("Sản phẩm " . ($variant->name ?? 'trong giỏ') . " hiện không đủ hàng.");
                }
            }

            // 4. CHUẨN BỊ DỮ LIỆU ĐỂ TẠO ORDER
            $shippingAddress = $this->resolveShippingAddress($request);
            
            // Load lại quan hệ product để lấy tên (Fix lỗi thiếu product_name)
            $cart->load(['items' => function($q) {
                $q->where('is_selected', true)->with('variant.product');
            }]);

            $subtotal = $this->calculateSubtotal($cart);
            $discountAmount = $this->calculateDiscountAmount($cart, $subtotal);
            $shippingFee = (int) session('shipping_fee', 0);
            
            // Đảm bảo không âm
            $totalAmount = max(0, $subtotal - $discountAmount + $shippingFee);
            $orderCode   = $this->generateOrderCode();

            // 5. TẠO ORDER (Bảng cha)
            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_code'       => $orderCode,
                'status'           => 'pending', // Trạng thái mặc định
                'payment_status'   => 'unpaid',
                'payment_method'   => $request->payment_method,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'discount_code'    => ($discountAmount > 0) ? $cart->discount_code : null,
                'shipping_fee'     => $shippingFee,
                'total_amount'     => $totalAmount,
                // Lưu JSON địa chỉ (snapshot)
                'shipping_address' => json_encode([
                    'name'    => $request->customer_name,
                    'phone'   => $request->customer_phone,
                    'email'   => $request->customer_email,
                    'address' => $shippingAddress
                ], JSON_UNESCAPED_UNICODE),
                'note'             => $request->note,
            ]);

            // ==============================================================
            // 6. TẠO ORDER ITEMS (FIX LỖI SKU & THUMBNAIL)
            // ==============================================================
            foreach ($cart->items as $cartItem) {
                $variant = $cartItem->variant;
                $product = $variant->product; 

                // Giá ưu tiên: Sale > Gốc
                $price = $variant->sale_price > 0 ? $variant->sale_price : $variant->original_price;

                // Lấy ảnh: Ưu tiên ảnh biến thể -> ảnh sản phẩm -> ảnh mặc định
                $thumbnail = $variant->image_url ?? $product->thumbnail ?? $product->image ?? 'no-image.png';

                OrderItem::create([
                    'order_id'             => $order->id,
                    'product_variant_id'   => $variant->id,
                    
                    // 1. Cột bắt buộc có (theo lỗi bạn gặp)
                    'product_name'         => $product->name,
                    'sku'                  => $variant->sku,       // <--- THÊM DÒNG NÀY
                    'thumbnail'            => $thumbnail,          // <--- THÊM DÒNG NÀY (Thường đi kèm SKU)
                    
                    // 2. Cột KHÔNG có trong DB (Bỏ đi)
                    // 'product_variant_name' => $variant->name, 
                    
                    'quantity'             => $cartItem->quantity,
                    'price'                => $price,
                    'total_line'           => $price * $cartItem->quantity,
                ]);
            }

            // 7. XỬ LÝ MÃ GIẢM GIÁ (Tăng lượt dùng)
            if ($discountAmount > 0 && $cart->discount_code) {
                $coupon = \App\Models\Discount::where('code', $cart->discount_code)->first();
                if ($coupon) {
                    $coupon->increment('used_count'); 
                }
            }

            // 8. DỌN DẸP GIỎ HÀNG
            $cart->items()->where('is_selected', true)->delete();
            $cart->discount_code = null;
            $cart->save();
            session()->forget(['shipping_fee']); 

            // 9. TRỪ KHO (Nếu thanh toán COD thì trừ luôn)
            if ($request->payment_method === 'cod') {
                $this->deductStock($order);
            }

            DB::commit();

            // Chuyển hướng thanh toán (VNPAY/MOMO hoặc trang thành công)
            return $this->dispatchPayment($order);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("CHECKOUT_ERROR: " . $e->getMessage());
            // Trả về trang checkout với lỗi hiển thị
            return redirect()->route('client.checkouts.index')->with('error', 'Lỗi xử lý: ' . $e->getMessage());
        }
    }

    /* =====================================================
     | 3. HÀM TRỪ KHO
     ===================================================== */
    private function deductStock(Order $order)
    {
        $order->load('items');
        foreach ($order->items as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

            if ($variant && $variant->stock_quantity >= $item->quantity) {
                $variant->decrement('stock_quantity', $item->quantity);
            } else {
                Log::channel('daily')->critical("OVERSOLD_ALERT: Order {$order->order_code} đã thanh toán nhưng hết hàng SKU: {$item->sku}");
            }
        }
    }

    /* =====================================================
     | 4. DISPATCH PAYMENT
     ===================================================== */
    private function dispatchPayment(Order $order)
    {
        if ($order->payment_method === 'cod') {
            return redirect()->route('client.checkouts.success')->with('success', 'Đặt hàng thành công!');
        }

        try {
            $url = match ($order->payment_method) {
                'vnpay'     => $this->createVnpayUrl($order),
                'momo'      => $this->createMomoUrl($order),
                'zalopay'   => $this->createZaloPayUrl($order),  // Gọi trực tiếp
                default     => throw new \Exception('Phương thức thanh toán không hỗ trợ.'),
            };
            return redirect()->away($url);  // Chuyển đến cổng thanh toán
        } catch (\Exception $e) {
            Log::error("PAYMENT_DISPATCH_ERROR: " . $e->getMessage());
            // Redirect về checkout với error chi tiết, không về home
            return redirect()->route('client.checkouts.index')->with('error', 'Lỗi khởi tạo thanh toán: ' . $e->getMessage() . '. Vui lòng thử lại hoặc chọn COD.');
        }
    }

    /* =====================================================
     | 5. XỬ LÝ THANH TOÁN THÀNH CÔNG (CHUNG)
     ===================================================== */
    private function handlePaymentSuccess(Order $order)
    {
        if ($order->payment_status === 'paid') return;

        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'processing'
            ]);

            $this->deductStock($order);
        });
    }

    /* =====================================================
     | 6. CALLBACKS
     ===================================================== */

    // VNPAY CALLBACK
    public function vnpayCallback(Request $request)
    {
        $inputData = $request->all();
        $secureHash = $inputData['vnp_SecureHash'] ?? '';

        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        $secureHashCheck = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $secureHashCheck) {
            $order = Order::where('order_code', $inputData['vnp_TxnRef'])->first();
            if (!$order) return redirect()->route('client.checkouts.failed');

            if ($inputData['vnp_ResponseCode'] == '00') {
                $this->handlePaymentSuccess($order);
                return redirect()->route('client.checkouts.success');
            }

            $order->update(['payment_status' => 'failed', 'status' => 'canceled']);
            return redirect()->route('client.checkouts.failed')->with('error', 'Giao dịch không thành công.');
        }
        return redirect()->route('client.checkouts.failed')->with('error', 'Chữ ký không hợp lệ.');
    }

    // MOMO CALLBACK (Server-to-server - IPN thật)
    public function momoCallback(Request $request)
    {
        // Debug: Luôn log khi hàm được gọi, dù env gì
        Log::debug('MoMo IPN Callback Received: ' . json_encode($request->all()));

        if (config('app.env') !== 'production') {
            Log::warning('MoMo Callback in non-production env - Skipping full process');
            return response()->json(['returnCode' => 1]);
        }

        // Bước 1: Kiểm tra params và signature (di chuyển lên đây)
        $params = $request->all();
        if (!isset($params['signature'])) {
            Log::error('MoMo IPN: Missing signature');
            return response()->json(['returnCode' => 1]);
        }

        $receivedSignature = $params['signature'];
        unset($params['signature']); // Loại signature khỏi hash

        ksort($params); // Sort alphabet (yêu cầu MoMo)

        $rawHash = '';
        foreach ($params as $key => $value) {
            $rawHash .= $key . '=' . $value . '&';
        }
        $rawHash = rtrim($rawHash, '&'); // Xóa & cuối

        $secretKey = config('services.momo.secret_key');
        $signatureCheck = hash_hmac('sha256', $rawHash, $secretKey);

        // Debug signatures
        Log::debug('MoMo IPN Raw Hash: ' . $rawHash);
        Log::debug('MoMo IPN Calculated Signature: ' . $signatureCheck);
        Log::debug('MoMo IPN Received Signature: ' . $receivedSignature);

        if ($signatureCheck !== $receivedSignature) {
            Log::error('MoMo IPN Signature Invalid: Received=' . $receivedSignature . ', Calculated=' . $signatureCheck);
            return response()->json(['returnCode' => 1]);
        }

        if ($request->partnerCode !== config('services.momo.partner_code')) {
            Log::error('MoMo IPN Invalid PartnerCode: ' . $request->partnerCode);
            return response()->json(['returnCode' => 1]);
        }

        // Bước 2: Handle success (chỉ nếu valid)
        if ($request->resultCode == 0) {
            $order = Order::where('order_code', $request->orderId)->first();
            if ($order) {
                $this->handlePaymentSuccess($order);
                Log::info('MoMo IPN Success: Order ' . $order->order_code . ' processed');
            } else {
                Log::warning('MoMo IPN: Order not found for orderId=' . $request->orderId);
            }
        } else {
            Log::info('MoMo IPN: Payment failed or canceled, resultCode=' . $request->resultCode);
            // Optional: Tìm order và update failed nếu cần
        }

        return response()->json(['returnCode' => 0]);
    }

    /* =====================================================
     | 7. TẠO URL THANH TOÁN
     ===================================================== */

    private function createVnpayUrl(Order $order)
    {
        // (Giữ nguyên code cũ của bạn)
        $vnp_Url = config('services.vnpay.url');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_TmnCode = config('services.vnpay.tmn_code');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $order->total_amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan don " . $order->order_code,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => route('client.checkouts.vnpay_return'),
            "vnp_TxnRef" => $order->order_code,
        ];
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
        return $vnp_Url;
    }

    // MOMo URL - GIẢ LẬP KHI DEV, DỄ CHUYỂN THẬT KHI PRODUCTION
    // 1. TẠO URL THANH TOÁN (GỬI SANG MOMO THẬT)
    public function createMomoUrl($order)
    {
        // Cấu hình từ .env
        $endpoint = config('services.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = config('services.momo.partner_code', 'MOMO');
        $accessKey = config('services.momo.access_key', 'F8BBA842ECF85');
        $secretKey = config('services.momo.secret_key', 'K951B6PE1waDMi640xX08PD3vg6EkVlz');

        // Thông tin đơn hàng
        // Lưu ý: requestId và orderId PHẢI KHÁC NHAU ở mỗi lần bấm thanh toán
        $requestId = (string) time() . rand(100, 999);

        // Kỹ thuật tránh trùng đơn hàng khi dùng Key công cộng:
        // Thêm microtime vào mã đơn
        $orderId = "ORD_" . $order->id . "_" . uniqid();

        $amount = (string) round($order->total_amount);
        $orderInfo = "Thanh toan don hang #" . $order->id;

        // Cấu hình Ngrok (Bắt buộc để MoMo trả kết quả về)
        // Hãy thay bằng domain Ngrok mới nhất của bạn
        $domain = "https://uncalm-genny-unjust.ngrok-free.dev";

        $redirectUrl = $domain . "/checkouts/momo-return";
        $ipnUrl = $domain . "/checkouts/momo-callback";

        $extraData = "";
        $requestType = "captureWallet";

        // TẠO CHỮ KÝ (SIGNATURE)
        // Quy tắc: Sắp xếp a-z các biến trong chuỗi hash
        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $ipnUrl .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $partnerCode .
            "&redirectUrl=" . $redirectUrl .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'Web Ban Hang',
            'storeId' => 'MomoTestStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        // Gửi request sang MoMo
        $response = Http::post($endpoint, $data);
        $json = $response->json();

        // Kiểm tra kết quả
        if (isset($json['payUrl'])) {
            return $json['payUrl']; // Chuyển hướng người dùng sang MoMo thật
        } else {
            // Debug lỗi nếu có
            dd("Lỗi tạo đơn MoMo:", $json, "Hash:", $rawHash);
        }
    }

    // 2. XỬ LÝ KHI NGƯỜI DÙNG QUAY VỀ (REDIRECT URL)
    public function momo_return(Request $request)
    {
        // Debug
        // dd($request->all());

        // Kiểm tra chữ ký trả về để bảo mật (Optional ở mức cơ bản)
        // Kiểm tra resultCode
        if ($request->resultCode == 0) {
            // Thanh toán thành công

            // Tách ID đơn hàng gốc từ chuỗi ORD_123_abcxyz
            // Format: ORD_{id}_{uniqid} -> Lấy phần tử thứ 2
            $parts = explode('_', $request->orderId);
            $realOrderId = $parts[1] ?? null;

            if ($realOrderId) {
                $order = Order::find($realOrderId);
                if ($order) {
                    $order->update(['payment_status' => 'paid', 'status' => 'pending']);
                    // Trừ kho...
                    $this->deductStock($order);
                }
            }

            return redirect()->route('client.checkouts.success')->with('success', 'Thanh toán MoMo thành công!');
        } else {
            // Thanh toán thất bại hoặc hủy
            return redirect()->route('client.checkouts.failed')->with('error', 'Giao dịch MoMo thất bại: ' . $request->message);
        }
    }

    // FAKE MOMO SCREEN
    public function fakeMomoScreen(Request $request)
    {
        $amount = $request->amount;
        $orderId = $request->orderId;
        $realOrderCode = $request->realOrderCode;

        // URL xử lý thành công (ResultCode = 0)
        $successUrl = route('client.checkouts.fake_momo_process', [
            'orderCode' => $realOrderCode,
            'resultCode' => 0
        ]);

        // URL xử lý thất bại (ResultCode = 99)
        $failUrl = route('client.checkouts.fake_momo_process', [
            'orderCode' => $realOrderCode,
            'resultCode' => 99
        ]);

        return "
            <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                <img src='https://developers.momo.vn/v3/assets/images/logo-custom2-57d6118fe524633b89befe8cb63a3956.png' width='100'><br><br>
                <h2>CỔNG THANH TOÁN MOMO (SIMULATOR)</h2>
                <div style='background:#f4f4f4; padding: 20px; display:inline-block; border-radius:10px;'>
                    <p>Mã đơn hàng gốc: <b>{$realOrderCode}</b></p>
                    <p>Số tiền: <b style='color:#a50064; font-size:24px'>" . number_format($amount) . " VND</b></p>
                </div>
                <br><br>
                
                <a href='{$successUrl}' 
                   style='background:#a50064; color:white; padding:15px 30px; text-decoration:none; border-radius:5px; margin:10px; display:inline-block; font-weight:bold;'>
                   ✅ THANH TOÁN THÀNH CÔNG
                </a>
                
                <br>

                <a href='{$failUrl}' 
                   style='background:#666; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin:10px; display:inline-block;'>
                   ❌ HỦY GIAO DỊCH / THẤT BẠI
                </a>
            </div>
        ";
    }
    // FAKE MOMO PROCESS
    public function fakeMomoProcess(Request $request)
    {
        $orderCode = $request->orderCode;
        $resultCode = $request->resultCode;

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return redirect()->route('client.checkouts.failed')->with('error', 'Không tìm thấy đơn hàng.');
        }

        // TRƯỜNG HỢP 1: THÀNH CÔNG (User bấm nút màu hồng)
        if ($resultCode == 0) {
            $this->handlePaymentSuccess($order); // Hàm này update status = paid
            return redirect()->route('client.checkouts.success')->with('success', 'Thanh toán MoMo thành công (Simulated)!');
        }

        // TRƯỜNG HỢP 2: THẤT BẠI / HỦY (User bấm nút màu xám)
        // Cập nhật đơn hàng thành hủy hoặc thất bại
        $order->update([
            'payment_status' => 'failed',
            'status'         => 'canceled' // Hoặc 'pending' nếu muốn cho phép thanh toán lại
        ]);

        // Bạn nên hoàn lại kho (restore stock) nếu hủy đơn hàng
        // $this->restoreStock($order); (Tùy logic của bạn có muốn hoàn kho ngay không)

        return redirect()->route('client.checkouts.failed')->with('error', 'Giao dịch đã bị hủy bởi người dùng (Giả lập).');
    }

    // Đảm bảo bạn đã có dòng này trên cùng file
    // use Illuminate\Support\Facades\Http; 
    public function createZaloPayUrl(Order $order)
        {
            // 1. Cấu hình cứng (Dùng bộ Key Sandbox chuẩn của ZaloPay)
            // Lưu ý: Khi nào chạy thật (Production) mới dùng config('...')
            $config = [
                "app_id" => 2553,
                "key1"   => "PcY4iZIKFCIdgZvA21hkpsxwd29a2zkd",
                "key2"   => "kLtgPl8HHhfvMuDHPwKfgfsY4Ydm9eIz",
                "endpoint" => "https://sb-openapi.zalopay.vn/v2/create"
            ];

            // 2. Tạo TransID (Đảm bảo duy nhất)
            $transID = rand(0, 999999);
            $app_trans_id = date("ymd") . "_" . $transID;

            // Lưu session
            session(['zalopay_order_code' => $order->order_code]);

            // 3. Chuẩn bị dữ liệu JSON
            // Dùng json_encode mặc định để đảm bảo tương thích tốt nhất
            $embed_data = json_encode([
                "redirecturl" => route('client.checkouts.zalopay_return')
            ]);
            
            $items = json_encode([]); // Mảng rỗng

            // 4. Tạo Payload gửi đi
            // Quan trọng: Ép kiểu (int) cho app_id, app_time, amount
            $orderData = [
                "app_id"       => (int) $config["app_id"],
                "app_user"     => "User_" . (Auth::id() ?? "Guest"),
                "app_time"     => (int) round(microtime(true) * 1000), // milliseconds
                "amount"       => (int) $order->total_amount,
                "app_trans_id" => $app_trans_id,
                "embed_data"   => $embed_data,
                "item"         => $items,
                "description"  => "Thanh toan don hang #" . $order->order_code,
                "bank_code"    => "", // Để trống
                // "callback_url" => "" // Sandbox không cần callback public
            ];

            // 5. Tạo chữ ký MAC
            // Công thức chuẩn: app_id|app_trans_id|app_user|amount|app_time|embed_data|item
            $data = $orderData["app_id"] . "|" . $orderData["app_trans_id"] . "|" . $orderData["app_user"] . "|" . $orderData["amount"] . "|" . $orderData["app_time"] . "|" . $orderData["embed_data"] . "|" . $orderData["item"];
            
            $orderData["mac"] = hash_hmac("sha256", $data, $config["key1"]);

            try {
                // 6. Gửi Request dạng JSON (Http::post mặc định là JSON)
                // Không dùng asForm() nữa để tránh sai kiểu dữ liệu
                $response = Http::withoutVerifying()
                    ->post($config["endpoint"], $orderData);

                $result = $response->json();

                // 7. Xử lý kết quả
                if (!isset($result['return_code']) || $result['return_code'] != 1) {
                    // Nếu lỗi, hiển thị chi tiết nguyên nhân
                    // if (app()->environment('local')) {
                    //     dd([
                    //         'Lỗi ZaloPay' => $result, // Nhìn kỹ sub_return_code ở đây
                    //         'Payload gửi đi' => $orderData,
                    //         'Chuỗi MAC' => $data
                    //     ]);
                    // }
                    throw new \Exception('ZaloPay Error: ' . ($result['return_message'] ?? 'Unknown'));
                }

                // Thành công!
                return $result['order_url'];

            } catch (\Exception $e) {
                Log::error('ZaloPay Create Failed: ' . $e->getMessage());
                throw $e;
            }
        }

        
    /* =====================================================
     | 8. VALIDATION & UTILS
     ===================================================== */
    private function validateCheckout(Request $request)
    {
        // 1. Định nghĩa Rules (Luật kiểm tra)
        $rules = [
            'customer_name'  => 'required|string|min:3|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => ['required', 'regex:/^(0)[0-9]{9}$/'], // Bắt đầu bằng 0, tổng 10 số
            'payment_method' => 'required|in:cod,vnpay,momo,zalopay',
            'address_id'     => 'required', // ID địa chỉ hoặc 'new'
            'note'           => 'nullable|string|max:500', // Cho phép ghi chú, tối đa 500 ký tự
        ];

        // 2. Định nghĩa Messages (Thông báo lỗi Tiếng Việt)
        $messages = [
            'customer_name.required'  => 'Vui lòng nhập họ tên người nhận.',
            'customer_name.min'       => 'Tên người nhận phải có ít nhất 3 ký tự.',
            'customer_name.max'       => 'Tên người nhận không được quá 255 ký tự.',
            
            'customer_email.required' => 'Vui lòng nhập địa chỉ email.',
            'customer_email.email'    => 'Email không đúng định dạng.',
            
            'customer_phone.required' => 'Vui lòng nhập số điện thoại.',
            'customer_phone.regex'    => 'Số điện thoại không hợp lệ (phải bắt đầu bằng số 0 và có 10 số).',
            
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'       => 'Phương thức thanh toán hiện chưa được hỗ trợ.',
            
            'address_id.required'     => 'Vui lòng chọn địa chỉ giao hàng.',
            'note.max'                => 'Ghi chú không được vượt quá 500 ký tự.',
        ];

        // 3. Logic kiểm tra địa chỉ mới (Nếu chọn "Địa chỉ khác")
        if ($request->address_id === 'new') {
            $newAddressRules = [
                'province_name'    => 'required|string|max:100',
                'district_name'    => 'required|string|max:100',
                'ward_name'        => 'required|string|max:100',
                'specific_address' => 'required|string|max:255',
            ];

            $newAddressMessages = [
                'province_name.required'    => 'Vui lòng chọn Tỉnh/Thành phố.',
                'district_name.required'    => 'Vui lòng chọn Quận/Huyện.',
                'ward_name.required'        => 'Vui lòng chọn Phường/Xã.',
                'specific_address.required' => 'Vui lòng nhập số nhà/tên đường cụ thể.',
            ];

            // Gộp luật và thông báo lại
            $rules = array_merge($rules, $newAddressRules);
            $messages = array_merge($messages, $newAddressMessages);
        }

        // 4. Thực thi Validate
        $request->validate($rules, $messages);
    }

    private function resolveShippingAddress(Request $request): string
    {
        if ($request->address_id === 'new') {
            return "{$request->specific_address}, {$request->ward_name}, {$request->district_name}, {$request->province_name}";
        }
        $addr = UserAddress::where('id', $request->address_id)->where('user_id', Auth::id())->first();
        return $addr ? $addr->full_address : 'Địa chỉ không xác định';
    }

    private function calculateSubtotal($cart): int
    {
        // Hàm này nhận vào $cart, và giả định $cart->items đã được filter 'is_selected' từ các bước trước
        return $cart->items->sum(function($item) {
            $price = $item->variant->sale_price > 0 
                ? $item->variant->sale_price 
                : ($item->variant->price ?: $item->variant->product->price_min);
                
            return $price * $item->quantity;
        });
    }

    private function generateOrderCode(): string
    {
        return 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
    }

    public function success()
    {
        return view('client.checkouts.success');
    }

    public function paymentFailed()
    {
        return view('client.checkouts.failed');
    }

    /**
     * Tính toán số tiền được giảm dựa trên Cart và Mã giảm giá
     */
    /**
     * Tính toán số tiền được giảm (Đã fix theo đúng Model Discount)
     */
    private function calculateDiscountAmount($cart, $subtotal)
    {
        // 1. Kiểm tra cơ bản
        if (empty($cart->discount_code)) return 0;

        $voucher = \App\Models\Discount::where('code', $cart->discount_code)->first();

        if (!$voucher) return 0;
        
        // Check hiệu lực (Hàm isValid bạn đã có trong Model)
        if (!$voucher->isValid()) return 0;

        // Check giá trị đơn hàng tối thiểu
        if ($voucher->min_order_amount > 0 && $subtotal < $voucher->min_order_amount) {
            return 0;
        }

        $amount = 0;
        
        // --- KHẮC PHỤC LỖI TẠI ĐÂY ---
        // Chuẩn hóa chuỗi trước khi so sánh để tránh lỗi thừa khoảng trắng hoặc hoa thường
        $type = strtolower(trim($voucher->type)); 

        if ($type === 'percent' || $type === 'percentage') {
            // TÍNH THEO PHẦN TRĂM
            // Công thức: Tổng tiền * (10 / 100)
            $amount = $subtotal * ($voucher->value / 100);

            // Kiểm tra giảm tối đa (Nếu có cấu hình)
            if ($voucher->max_discount_value > 0 && $amount > $voucher->max_discount_value) {
                $amount = $voucher->max_discount_value;
            }
        } else {
            // TÍNH THEO SỐ TIỀN CỐ ĐỊNH (FIXED)
            // Đây là chỗ code cũ của bạn bị nhảy vào nhầm, dẫn đến ra 10đ
            $amount = $voucher->value;
        }

        return min($amount, $subtotal);
    }
}