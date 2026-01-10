<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        // Lấy danh sách voucher
        // Sắp xếp: Mã đang diễn ra lên trước, sau đó đến mã sắp diễn ra, cuối cùng là mã đã hết hạn/hết lượt
        $vouchers = Discount::query()
            ->orderByRaw("
                CASE 
                    -- Ưu tiên 1: Đang hiệu lực (Ngày trong hạn + Còn lượt)
                    WHEN start_date <= NOW() AND end_date >= NOW() AND (max_usage = 0 OR used_count < max_usage) THEN 1
                    -- Ưu tiên 2: Sắp diễn ra
                    WHEN start_date > NOW() THEN 2
                    -- Ưu tiên 3: Đã hết hạn hoặc hết lượt
                    ELSE 3
                END
            ")
            ->orderBy('end_date', 'asc') // Mã nào sắp hết hạn thì hiện trước trong nhóm ưu tiên
            ->get();

        return view('client.vouchers.index', compact('vouchers'));
    }
}