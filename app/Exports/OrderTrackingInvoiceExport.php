<?php

namespace App\Exports;

use App\Models\OrderTracking;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderTrackingInvoiceExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $trackingNumber;

    public function __construct($trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function view(): View
    {
        // Nhóm các Order Tracking theo mã hàng, màu, kích để gom số lượng
        // Lấy tất cả, rồi tự gộp bằng Collection
        $trackings = OrderTracking::with('order')
            ->where('tracking_number', $this->trackingNumber)
            ->get();

        $exchangeRate = Setting::where('key', 'usd_to_vnd')->value('value') ?? 25400;

        $items = [];
        $totalQuantity = 0;
        $totalVnd = 0;

        foreach ($trackings as $t) {
            $order = $t->order;
            if (!$order) continue;

            $maHh = $order->ma_hh ?? $t->size;
            $description = $order->im_number ?? ($maHh . ' ' . $t->mau . ' - ' . $t->kich);
            
            // Xử lý giá USD (fallback)
            $priceUsd = $order->price_usd ?? $order->price_usd_auto ?? 0;
            $qty = (float)$t->sl_don_hang;
            
            if ($qty <= 0) continue;

            // Gom nhóm
            $key = md5($description . $priceUsd);
            if (!isset($items[$key])) {
                $items[$key] = [
                    'description' => $description,
                    'unit'        => 'Yard',
                    'quantity'    => 0,
                    'price_usd'   => $priceUsd,
                ];
            }

            $items[$key]['quantity'] += $qty;
        }

        // Tính thành tiền từng dòng
        foreach ($items as &$item) {
            $item['price_vnd'] = $item['price_usd'] * $exchangeRate;
            $item['amount_usd'] = $item['quantity'] * $item['price_usd'];
            $item['amount_vnd'] = $item['amount_usd'] * $exchangeRate;
            
            $totalQuantity += $item['quantity'];
            $totalVnd += $item['amount_vnd'];
        }

        $vatRate = 0.08; // 8% VAT
        $vatAmount = $totalVnd * $vatRate;
        $grandTotalVnd = $totalVnd + $vatAmount;

        // Chuyển số tiền sang chữ
        $amountInWords = $this->numberToWordsVn(round($grandTotalVnd)) . ' đồng';

        return view('admin.exports.invoice', [
            'trackingNumber' => $this->trackingNumber,
            'items'          => array_values($items),
            'totalQuantity'  => $totalQuantity,
            'subTotal'       => $totalVnd,
            'vatRate'        => 8,
            'vatAmount'      => $vatAmount,
            'grandTotal'     => $grandTotalVnd,
            'amountInWords'  => $amountInWords,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Do FromView HTML tự parse ra Excel nên WithStyles ở đây có thể dùng thêm
        // để canh lề, bôi đậm nếu HTML không đủ
        return [
            // Áp dụng viền cho các cột
        ];
    }

    private function numberToWordsVn($number)
    {
        if ($number == 0) return 'không';
        
        $hyphen      = ' ';
        $conjunction = '  ';
        $separator   = ' ';
        $negative    = 'âm ';
        $decimal     = ' phẩy ';
        $dictionary  = array(
            0                   => 'không',
            1                   => 'một',
            2                   => 'hai',
            3                   => 'ba',
            4                   => 'bốn',
            5                   => 'năm',
            6                   => 'sáu',
            7                   => 'bảy',
            8                   => 'tám',
            9                   => 'chín',
            10                  => 'mười',
            11                  => 'mười một',
            12                  => 'mười hai',
            13                  => 'mười ba',
            14                  => 'mười bốn',
            15                  => 'mười lăm',
            16                  => 'mười sáu',
            17                  => 'mười bảy',
            18                  => 'mười tám',
            19                  => 'mười chín',
            20                  => 'hai mươi',
            30                  => 'ba mươi',
            40                  => 'bốn mươi',
            50                  => 'năm mươi',
            60                  => 'sáu mươi',
            70                  => 'bảy mươi',
            80                  => 'tám mươi',
            90                  => 'chín mươi',
            100                 => 'trăm',
            1000                => 'nghìn',
            1000000             => 'triệu',
            1000000000          => 'tỷ',
            1000000000000       => 'nghìn tỷ',
            1000000000000000    => 'ngàn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            trigger_error('numberToWordsVn chỉ nhận số nguyên', E_USER_WARNING);
            return false;
        }

        if ($number < 0) {
            return $negative . $this->numberToWordsVn(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . ($units == 1 ? 'mốt' : ($units == 5 ? 'lăm' : $dictionary[$units]));
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . ($remainder < 10 ? 'lẻ ' . $dictionary[$remainder] : $this->numberToWordsVn($remainder));
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->numberToWordsVn($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->numberToWordsVn($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        // Viết hoa chữ cái đầu
        return mb_ucfirst(trim($string));
    }
}

// Hàm hỗ trợ viết hoa chữ cái đầu tiên (hỗ trợ Unicode)
if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        $str_end = "";
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        } else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }
}
