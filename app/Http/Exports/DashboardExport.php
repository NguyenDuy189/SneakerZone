<?php

namespace App\Http\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DashboardExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 1. Lấy dữ liệu
     */
    public function collection()
    {
        return $this->data;
    }

    /**
     * 2. Định nghĩa Tiêu đề cột (Header)
     */
    public function headings(): array
    {
        return [
            'STT',
            'Mã Đơn Hàng',
            'Ngày Đặt',
            'Khách Hàng',
            'Số Điện Thoại',
            'Phương Thức TT',
            'Trạng Thái',
            'Tổng Tiền (VNĐ)',
        ];
    }

    /**
     * 3. Map dữ liệu & Việt hóa (Quan trọng)
     * Chuyển đổi dữ liệu thô từ DB sang tiếng Việt hiển thị
     */
    public function map($order): array
    {
        // Việt hóa trạng thái
        $statusMap = [
            'pending'    => 'Chờ xử lý',
            'processing' => 'Đang đóng gói',
            'shipping'   => 'Đang giao hàng',
            'completed'  => 'Hoàn thành',
            'cancelled'  => 'Đã hủy',
            'returned'   => 'Trả hàng',
        ];

        // Việt hóa phương thức thanh toán
        $paymentMethodMap = [
            'cod'     => 'Tiền mặt (COD)',
            'vnpay'   => 'VNPAY',
            'momo'    => 'Momo',
            'banking' => 'Chuyển khoản',
        ];

        // Việt hóa trạng thái thanh toán
        $paymentStatusText = match($order->payment_status) {
            'paid'     => ' (Đã TT)',
            'refunded' => ' (Hoàn tiền)',
            default    => ' (Chưa TT)',
        };

        return [
            // STT (Nếu muốn số thứ tự tăng dần, cần xử lý logic bên ngoài hoặc dùng $row index)
            // Ở đây tạm để ID hoặc logic đơn giản
            $order->id, 
            
            $order->order_code,
            
            $order->created_at->format('d/m/Y H:i'),
            
            $order->user->name ?? 'Khách vãng lai',
            
            // Xử lý phone để Excel không hiểu nhầm là số
            $order->receiver_phone ?? '---', 
            
            ($paymentMethodMap[$order->payment_method] ?? $order->payment_method),
            
            ($statusMap[$order->status] ?? $order->status) . $paymentStatusText,
            
            // Định dạng tiền tệ (chuyển về float để Excel tính toán được)
            (float) $order->total_amount
        ];
    }

    /**
     * 4. Styling nâng cao (Làm đẹp)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                $range = 'A1:' . $lastColumn . $lastRow;

                // --- A. Style cho Header (Dòng 1) ---
                $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'], // Chữ trắng
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF10B981'], // Màu nền Xanh Emerald (giống nút Excel trên web)
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Set chiều cao hàng header
                $sheet->getRowDimension(1)->setRowHeight(30);

                // --- B. Style cho toàn bộ bảng dữ liệu ---
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFCCCCCC'], // Viền màu xám nhạt
                        ],
                    ],
                    'font' => [
                        'name' => 'Times New Roman', // Hoặc Arial
                        'size' => 11,
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // --- C. Căn chỉnh từng cột cụ thể ---
                
                // Căn giữa: STT (A), Mã đơn (B), Ngày (C), SĐT (E), Trạng thái (G)
                $sheet->getStyle('A2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E2:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G2:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Căn trái: Tên khách (D), PTTT (F)
                $sheet->getStyle('D2:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Căn phải & Format tiền tệ: Tổng tiền (H)
                $sheet->getStyle('H2:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('H2:H' . $lastRow)->getNumberFormat()->setFormatCode('#,##0'); // Format dạng 1.000.000
            },
        ];
    }
}