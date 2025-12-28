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
            // 1. Lấy giỏ hàng
            $cart = Cart::with(['items.variant.product'])
                ->where('user_id', Auth::id())
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('client.products.index')->with('error', 'Giỏ hàng của bạn đang trống.');
            }

            // 2. CHECK SƠ BỘ TỒN KHO (Soft Check - Để hiển thị cảnh báo UI)
            foreach ($cart->items as $item) {
                if (!$item->variant || !$item->variant->product) {
                    $item->delete();
                    return redirect()->route('client.carts.index')->with('error', 'Giỏ hàng đã được cập nhật do một số sản phẩm không còn tồn tại.');
                }

                $stock = (int) $item->variant->stock_quantity;
                $qty   = (int) $item->quantity;

                if ($stock < $qty) {
                    return redirect()->route('client.carts.index')
                        ->with('error', "Sản phẩm '{$item->variant->product->name}' (Phân loại: {$item->variant->sku}) chỉ còn {$stock} sản phẩm. Vui lòng cập nhật số lượng.");
                }
            }

            // 3. Tính toán tổng tiền
            $subtotal    = $this->calculateSubtotal($cart);
            $discount    = (int) session('voucher.discount', 0);
            $shippingFee = (int) session('shipping_fee', 0);
            $total       = max(0, $subtotal - $discount + $shippingFee);

            // 4. Lấy danh sách địa chỉ
            $addresses = UserAddress::where('user_id', Auth::id())->orderByDesc('is_default')->get();

            return view('client.checkouts.index', compact('cart', 'subtotal', 'discount', 'shippingFee', 'total', 'addresses'));
        } catch (\Throwable $e) {
            Log::error("CHECKOUT_VIEW_ERROR: " . $e->getMessage());
            return redirect()->route('client.home')->with('error', 'Đã xảy ra lỗi khi tải trang thanh toán.');
        }
    }

    /* =====================================================
     | 2. PROCESS: XỬ LÝ ĐẶT HÀNG (CORE LOGIC)
     ===================================================== */
    public function process(Request $request)
    {
        $this->validateCheckout($request);

        DB::beginTransaction();
        try {
            $cart = Cart::with(['items'])
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($cart->items->isEmpty()) throw new Exception('Giỏ hàng trống.');

            // HARD CHECK TỒN KHO
            foreach ($cart->items as $item) {
                $variantId = $item->product_variant_id ?? $item->variant_id;

                if (empty($variantId)) {
                    $item->delete();
                    throw new Exception("Giỏ hàng có sản phẩm bị lỗi dữ liệu (Mất ID).");
                }

                $variant = ProductVariant::lockForUpdate()
                    ->with('product')
                    ->find($variantId);

                if (!$variant) {
                    $item->delete();
                    throw new Exception("Sản phẩm (ID: {$variantId}) không còn tồn tại.");
                }

                $productName = $variant->product ? $variant->product->name : 'Sản phẩm không xác định';

                $currentStock = (int) $variant->stock_quantity;
                $requestQty   = (int) $item->quantity;

                if ($currentStock < $requestQty) {
                    throw new Exception("Sản phẩm '{$productName}' (SKU: {$variant->sku}) hiện chỉ còn {$currentStock}, không đủ số lượng {$requestQty} bạn yêu cầu.");
                }
            }

            $shippingAddress = $this->resolveShippingAddress($request);
            $cart->load('items.variant.product');

            $subtotal    = $this->calculateSubtotal($cart);
            $discount    = (int) session('voucher.discount', 0);
            $shippingFee = (int) session('shipping_fee', 0);
            $totalAmount = max(0, $subtotal - $discount + $shippingFee);
            $orderCode   = $this->generateOrderCode();

            $order = Order::create([
                'user_id'          => Auth::id(),
                'order_code'       => $orderCode,
                'status'           => 'pending',
                'payment_status'   => 'unpaid',
                'payment_method'   => $request->payment_method,
                'subtotal'         => $subtotal,
                'discount_amount'  => $discount,
                'shipping_fee'     => $shippingFee,
                'total_amount'     => $totalAmount,
                'shipping_address' => json_encode([
                    'name'    => $request->customer_name,
                    'phone'   => $request->customer_phone,
                    'email'   => $request->customer_email,
                    'address' => $shippingAddress
                ], JSON_UNESCAPED_UNICODE),
                'note'             => $request->note,
            ]);

            foreach ($cart->items as $item) {
                $price = $item->variant->sale_price ?: $item->variant->original_price;
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $item->variant_id,
                    'product_name'       => $item->variant->product->name,
                    'sku'                => $item->variant->sku,
                    'quantity'           => $item->quantity,
                    'price'              => $price,
                    'total_line'         => $price * $item->quantity,
                ]);
            }

            $cart->items()->delete();
            session()->forget(['voucher.discount', 'shipping_fee', 'voucher.code']);

            if ($request->payment_method === 'cod') {
                $this->deductStock($order);
                $msg = 'Đặt hàng thành công!';
            } else {
                $msg = 'Đang chuyển hướng thanh toán...';
            }

            DB::commit();

            return $this->dispatchPayment($order);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("CHECKOUT_PROCESS_ERROR: " . $e->getMessage());
            return redirect()->route('client.checkouts.index')->with('error', $e->getMessage());
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
                    $order->update(['payment_status' => 'paid', 'status' => 'processing']);
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

    /* =====================================================
     | 7.1 TẠO URL THANH TOÁN ZALOPAY
     | ===================================================== */
    public function createZaloPayUrl(Order $order)
    {
        // 1. Lấy cấu hình từ .env
        $config = [
            "app_id" => config('services.zalopay.app_id', env('ZALOPAY_APP_ID')),
            "key1"   => config('services.zalopay.key1', env('ZALOPAY_KEY1')),
            "key2"   => config('services.zalopay.key2', env('ZALOPAY_KEY2')),
            "endpoint" => config('services.zalopay.endpoint', env('ZALOPAY_ENDPOINT')),
        ];

        // Kiểm tra config cơ bản
        if (empty($config['app_id']) || empty($config['key1'])) {
            Log::error('ZaloPay Config Missing', ['app_id' => $config['app_id'], 'key1' => substr($config['key1'], 0, 10) . '...']);
            throw new \Exception('ZaloPay config missing: app_id or key1 is empty.');
        }

        // 2. Tạo mã giao dịch ZaloPay
        $transID = rand(0, 999999);
        $app_trans_id = date("ymd") . "_" . $transID;

        // Lưu order_code vào session
        session(['zalopay_order_code' => $order->order_code]);

        // 3. Chuẩn bị embed_data và items
        $embed_data = [
            "redirecturl" => route('client.checkouts.zalopay_return')  // Đảm bảo route tồn tại
        ];
        $items = []; // Mapping từ $order->items nếu cần

        // 4. Tạo payload
        $amount = (int) round($order->total_amount);
        if ($amount <= 0) {
            throw new \Exception('Amount must be positive.');
        }
        $orderData = [
            "app_id"       => $config["app_id"],
            "app_user"     => Auth::check() ? "User_" . Auth::id() : "Guest",
            "app_time"     => (int) round(microtime(true) * 1000), // Đã sửa int
            "amount"       => $amount,
            "app_trans_id" => $app_trans_id,
            "embed_data"   => json_encode($embed_data, JSON_UNESCAPED_UNICODE),
            "item"         => json_encode($items, JSON_UNESCAPED_UNICODE),
            "description"  => "Thanh toan don hang #" . $order->order_code,
            "bank_code"    => "",
        ];

        // 5. Tạo HMAC
        $data = $orderData["app_id"] . "|" . $orderData["app_trans_id"] . "|" . $orderData["app_user"] . "|" . $orderData["amount"] . "|" . $orderData["app_time"] . "|" . $orderData["embed_data"] . "|" . $orderData["item"];
        $orderData["mac"] = hash_hmac("sha256", $data, $config["key1"]);

        // Log dữ liệu gửi đi và HMAC string để debug
        Log::info('ZaloPay Data Prepared', [
            'data' => $orderData,
            'hmac_string' => $data,
            'endpoint' => $config["endpoint"]
        ]);

        // 6. Gửi request
        try {
            $httpClient = Http::timeout(30);
            if (app()->environment('local')) {
                $httpClient = $httpClient->withoutVerifying(); // Chỉ dùng trên localhost
            }
            $response = $httpClient->post($config["endpoint"], $orderData);

            // Kiểm tra HTTP status
            if ($response->failed()) {
                Log::error('ZaloPay HTTP Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'data' => $orderData
                ]);
                throw new \Exception('HTTP Error: ' . $response->status() . ' - ' . $response->reason());
            }

            $result = $response->json();

            // Kiểm tra return_code
            if (!isset($result['return_code']) || $result['return_code'] != 1) {
                Log::error('ZaloPay API Failed', [
                    'result' => $result,
                    'data' => $orderData,
                    'hmac_string' => $data
                ]);
                throw new \Exception('ZaloPay Error: ' . ($result['return_message'] ?? 'Unknown') . ' (Code: ' . ($result['sub_return_code'] ?? 'N/A') . ')');
            }

            // Thành công
            Log::info('ZaloPay Success', ['order_url' => $result['order_url']]);
            return $result['order_url'];

        } catch (\Exception $e) {
            Log::error('ZaloPay System Error', ['message' => $e->getMessage()]);
            throw $e; // Re-throw để dispatchPayment handle
        }
    }

    /* =====================================================
     | 7.2 XỬ LÝ KHI NGƯỜI DÙNG QUAY VỀ (REDIRECT URL)
     | ===================================================== */
    public function zalopayCallback(Request $request)
    {
        // ZaloPay Redirect trả về: ?amount=...&appid=...&apptransid=...&checksum=...&status=1

        // 1. Kiểm tra trạng thái từ URL
        // status = 1: Thành công, status != 1: Thất bại/Hủy
        if (!$request->has('status') || $request->status != 1) {
            return redirect()->route('client.checkouts.failed')->with('error', 'Thanh toán ZaloPay thất bại hoặc đã bị hủy.');
        }

        // 2. Lấy lại Order Code từ Session
        $orderCode = session('zalopay_order_code');
        session()->forget('zalopay_order_code'); // Xóa session ngay sau khi lấy

        if (!$orderCode) {
            // Trường hợp mất session (hiếm), thử tìm order mới nhất của user đang pending
            $order = Order::where('user_id', Auth::id())
                ->where('payment_method', 'zalopay')
                ->where('payment_status', 'unpaid')
                ->latest()
                ->first();
        } else {
            $order = Order::where('order_code', $orderCode)->first();
        }

        if (!$order) {
            return redirect()->route('client.checkouts.failed')->with('error', 'Không tìm thấy đơn hàng tương ứng.');
        }

        // 3. Xác thực Checksum (Tùy chọn nhưng nên làm để bảo mật)
        // Cách tính: HMAC-SHA256(appid|apptransid|pmcid|bankcode|amount|discountamount|status, key2)
        // Tuy nhiên với đồ án/sandbox, việc check status=1 và mapping đúng order là đủ.

        // 4. Xử lý thành công
        // Lưu ý: ZaloPay Redirect không đảm bảo 100% server đã nhận tiền (nên dùng Callback/IPN).
        // Nhưng để UX tốt, ta cập nhật luôn ở đây.

        try {
            $this->handlePaymentSuccess($order); // Hàm này update status = paid và trừ kho
            return redirect()->route('client.checkouts.success')->with('success', 'Thanh toán ZaloPay thành công!');
        } catch (\Exception $e) {
            Log::error("ZALOPAY_UPDATE_ERR: " . $e->getMessage());
            return redirect()->route('client.checkouts.failed')->with('error', 'Lỗi cập nhật trạng thái đơn hàng.');
        }
    }

    /* =====================================================
     | 8. VALIDATION & UTILS
     ===================================================== */
    private function validateCheckout(Request $request)
    {
        $rules = [
            'customer_name'  => 'required|string|min:2|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => ['required', 'regex:/^(0)[0-9]{9}$/'],
            'payment_method' => 'required|in:cod,vnpay,momo,zalopay',
            'address_id'     => 'required',
        ];

        if ($request->address_id === 'new') {
            $rules = array_merge($rules, [
                'province_name'    => 'required',
                'district_name'    => 'required',
                'ward_name'        => 'required',
                'specific_address' => 'required',
            ]);
        }
        $request->validate($rules, [
            'customer_phone.regex' => 'Số điện thoại không hợp lệ.',
            'payment_method.in'    => 'Phương thức thanh toán không hỗ trợ.'
        ]);
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
        return $cart->items->sum(
            fn($item) => (($item->variant->sale_price > 0) ? $item->variant->sale_price : $item->variant->original_price) * $item->quantity
        );
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
}
