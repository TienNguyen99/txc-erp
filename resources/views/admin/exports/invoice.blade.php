<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: 'Times New Roman', serif; font-size: 11pt;">
    <table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">
        <!-- HEADER -->
        <tr>
            <td colspan="2" rowspan="4" align="center" style="border: 1px solid #000; border-right: none;">
                <!-- Có thể thay logo Texenco bằng img base64 hoặc URL nếu export PDF, Excel sẽ tải hình -->
                <strong style="color: darkred; font-size: 24pt;">Texenco<sup>&reg;</sup></strong><br>
                <span style="font-size: 8pt;">WWW.TEXENCO.COM.VN<br>ISO 9001:2015 ISO 14001:2015<br>ISO 45001:2018</span>
            </td>
            <td colspan="4" style="border: 1px solid #000; border-left: none; color: darkred; font-size: 14pt; font-weight: bold; text-align: left;">
                CÔNG TY CỔ PHẦN CƠ KHÍ THỦ ĐỨC
            </td>
        </tr>
        <tr>
            <td colspan="4" style="border: 1px solid #000; border-left: none; border-top: none; text-align: left;">
                Mã số thuế <i>(Tax code)</i>: <strong style="font-size: 11pt;">0304059245</strong>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="border: 1px solid #000; border-left: none; border-top: none; text-align: left;">
                Địa chỉ <i>(Address)</i>: 219 Lê Văn Chí, Phường Linh Xuân, Thành phố Hồ Chí Minh, Việt Nam
            </td>
        </tr>
        <tr>
            <td colspan="4" style="border: 1px solid #000; border-left: none; border-top: none; text-align: left;">
                Điện thoại <i>(Tel)</i>: 02839003333 &nbsp;&nbsp;&nbsp;&nbsp; Email: info@texenco.com.vn<br>
                Số tài khoản <i>(Account No.)</i>: 11139398888 tại NH TMCP Á Châu - CN Thủ Đức, TP. HCM<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0381000427787 tại NH TMCP Vietcombank - CN Thủ Đức, TP. HCM
            </td>
        </tr>

        <!-- TITLE -->
        <tr>
            <td colspan="6" align="center" style="border-left: 1px solid #000; border-right: 1px solid #000; padding: 15px 0;">
                <span style="color: red; font-size: 18pt; font-weight: bold;">HÓA ĐƠN GIÁ TRỊ GIA TĂNG</span><br>
                <span style="color: red; font-size: 14pt; font-style: italic;">(VAT INVOICE)</span><br>
                <br>
                <strong style="font-size: 10pt;">Bản thể hiện của hóa đơn điện tử</strong><br>
                <i style="font-size: 10pt;">(Electronic invoice display)</i><br>
                <span style="font-size: 10pt;">Ngày <i>(date)</i> {{ date('d') }} tháng <i>(month)</i> {{ date('m') }} năm <i>(year)</i> {{ date('Y') }}</span>
                
                <div style="float: right; text-align: left; font-size: 10pt; margin-top: -60px; margin-right: 20px;">
                    Ký hiệu <i>(Serial)</i>: &nbsp;&nbsp;<b>1C26TTD</b><br>
                    Số <i>(Invoice No)</i>: &nbsp;&nbsp;&nbsp;<b style="color: red;">{{ rand(100, 999) }}</b>
                </div>
            </td>
        </tr>

        <!-- CUSTOMER INFO -->
        <tr>
            <td colspan="6" style="border: 1px solid #000; text-align: left;">
                Họ tên người mua hàng <i>(Attention)</i>:<br>
                Tên công ty <i>(Company)</i>: <strong>Khách hàng nội bộ (Dữ liệu từ Tracking)</strong><br>
                Địa chỉ <i>(Address)</i>:<br>
                Mã số thuế <i>(Tax code)</i>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Phương thức thanh toán <i>(Payment method)</i>: Chuyển khoản<br>
                Ghi chú <i>(Note)</i>: Lot {{ $trackingNumber }}
            </td>
        </tr>

        <!-- TABLE HEADER -->
        <tr>
            <td align="center" style="border: 1px solid #000; font-weight: bold; width: 40px;">STT<br><i>(No.)</i></td>
            <td align="center" style="border: 1px solid #000; font-weight: bold; width: 350px;">Tên hàng hóa, dịch vụ<br><i>(Description)</i></td>
            <td align="center" style="border: 1px solid #000; font-weight: bold; width: 80px;">Đơn vị tính<br><i>(Unit)</i></td>
            <td align="center" style="border: 1px solid #000; font-weight: bold; width: 90px;">Số lượng<br><i>(Quantity)</i></td>
            <td align="center" style="border: 1px solid #000; font-weight: bold; width: 100px;">Đơn giá<br><i>(Unit price)</i></td>
            <td align="center" style="border: 1px solid #000; font-weight: bold; width: 120px;">Thành tiền<br><i>(Amount)</i></td>
        </tr>

        <!-- TABLE BODY -->
        @foreach($items as $index => $item)
        <tr>
            <td align="center" style="border: 1px solid #000;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item['description'] }}</td>
            <td align="center" style="border: 1px solid #000;">{{ $item['unit'] }}</td>
            <td align="right" style="border: 1px solid #000;">{{ number_format($item['quantity'], 3, '.', '') }}</td>
            <td align="right" style="border: 1px solid #000;">{{ number_format($item['price_vnd'], 0, ',', '.') }}</td>
            <td align="right" style="border: 1px solid #000;">{{ number_format($item['amount_vnd'], 0, ',', '.') }}</td>
        </tr>
        @endforeach

        <!-- INVOICE TOTAL QTY ROW -->
        <tr>
            <td style="border: 1px solid #000; border-right: none;"></td>
            <td colspan="5" style="border: 1px solid #000; border-left: none; text-align: left;">
                - Invoice: FOB-{{ date('dmY') }}-E. Tổng số lượng: {{ number_format($totalQuantity, 3, '.', '') }} YARD
            </td>
        </tr>

        <!-- SUB TOTAL -->
        <tr>
            <td colspan="4" align="right" style="border: 1px solid #000; font-weight: bold;">Cộng tiền hàng <i>(Sub total)</i>:</td>
            <td colspan="2" align="right" style="border: 1px solid #000; font-weight: bold;">{{ number_format($subTotal, 0, ',', '.') }}</td>
        </tr>

        <!-- VAT -->
        <tr>
            <td colspan="2" style="border: 1px solid #000; font-weight: bold;">Thuế suất GTGT <i>(VAT rate)</i>: {{ $vatRate }}%</td>
            <td colspan="2" align="right" style="border: 1px solid #000; font-weight: bold;">Tiền thuế GTGT <i>(VAT amount)</i>:</td>
            <td colspan="2" align="right" style="border: 1px solid #000; font-weight: bold;">{{ number_format($vatAmount, 0, ',', '.') }}</td>
        </tr>

        <!-- GRAND TOTAL -->
        <tr>
            <td colspan="4" align="right" style="border: 1px solid #000; font-weight: bold;">Tổng cộng tiền thanh toán <i>(Total amount)</i>:</td>
            <td colspan="2" align="right" style="border: 1px solid #000; font-weight: bold;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>

        <!-- TO WORDS -->
        <tr>
            <td colspan="6" style="border: 1px solid #000; text-align: left;">
                Số tiền viết bằng chữ <i>(Amount in words)</i>: {{ $amountInWords }}
            </td>
        </tr>

        <!-- FOOTER SIGNATURE -->
        <tr>
            <td colspan="3" align="center" style="border-left: 1px solid #000; border-bottom: 1px solid #000; padding: 20px 0; vertical-align: top;">
                <strong>Người mua hàng <i>(Client)</i></strong>
            </td>
            <td colspan="3" align="center" style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 20px 0; vertical-align: top;">
                <strong>Người bán hàng <i>(Seller)</i></strong><br>
                <div style="color: darkgreen; margin-top: 10px;">
                    Signature valid<br>
                    <strong style="color: red;">Ký bởi CÔNG TY CỔ PHẦN CƠ KHÍ THỦ ĐỨC</strong><br>
                    <strong style="color: red;">Ký ngày {{ date('d/m/Y') }}</strong><br>
                    <span style="font-size: 24pt;">&#10004;</span>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
