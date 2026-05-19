<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>{{ $warehouseDocument->document_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", serif; color: #000; margin: 0; }
        .copy { min-height: 138mm; page-break-inside: avoid; padding-bottom: 8mm; }
        .copy + .copy { border-top: 1px solid #ddd; padding-top: 8mm; }
        .header { display: grid; grid-template-columns: 180px 1fr 180px; align-items: start; }
        .logo { font-family: Arial, sans-serif; color: #f7941d; font-size: 34px; font-weight: 800; line-height: 1; text-shadow: 1px 1px #8a4a00; }
        h1 { margin: 0; text-align: center; font-size: 30px; }
        .meta { text-align: center; font-size: 18px; margin: 8px 0 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 7px 5px; font-size: 20px; line-height: 1.15; text-align: center; vertical-align: middle; }
        th { font-weight: 700; }
        .col-stt { width: 38px; }
        .col-name { width: 220px; }
        .col-code { width: 240px; }
        .col-color { width: 110px; }
        .col-size { width: 105px; }
        .col-qty { width: 120px; }
        .col-unit { width: 72px; }
        .col-order { width: 145px; }
        .col-note { width: 210px; }
        .date { text-align: right; font-size: 22px; font-style: italic; margin: 12px 28px 20px 0; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; text-align: center; font-size: 22px; font-weight: 700; font-style: italic; }
        .actions { position: fixed; top: 10px; right: 10px; font-family: Arial, sans-serif; }
        .actions button { font-size: 14px; padding: 8px 12px; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">In phiếu</button>
    </div>

    @php
        $title = $warehouseDocument->type === 'NHAPKHO' ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO';
    @endphp

    @for ($copy = 1; $copy <= 2; $copy++)
        <section class="copy">
            <div class="header">
                <div class="logo">Texenco</div>
                <h1>{{ $title }}</h1>
                <div></div>
            </div>

            <div class="meta">Số phiếu: {{ $warehouseDocument->document_no }}</div>

            <table>
                <thead>
                    <tr>
                        <th class="col-stt">Stt</th>
                        <th class="col-name">Tên sản phẩm</th>
                        <th class="col-code">Mã hàng</th>
                        <th class="col-color">Màu sắc</th>
                        <th class="col-size">Size</th>
                        <th class="col-qty">Số lượng</th>
                        <th class="col-unit">Đvt</th>
                        <th class="col-order">Lệnh</th>
                        <th class="col-note">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($warehouseDocument->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->ten_san_pham }}</td>
                            <td>{{ $item->ma_hh }}</td>
                            <td>{{ $item->mau }}</td>
                            <td>{{ $item->size }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $item->so_luong, 2, '.', ''), '0'), '.') }}</td>
                            <td>{{ $item->don_vi }}</td>
                            <td>{{ $item->lenh_sx }}</td>
                            <td>{{ $item->ghi_chu }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="date">
                NGÀY&nbsp;&nbsp;{{ $warehouseDocument->document_date->format('d') }}
                &nbsp;THÁNG&nbsp;&nbsp;{{ $warehouseDocument->document_date->format('m') }}
                &nbsp;NĂM&nbsp;{{ $warehouseDocument->document_date->format('Y') }}
            </div>

            <div class="signatures">
                <div>Người nhận</div>
                <div>Thủ kho</div>
                <div>Người xuất</div>
            </div>
        </section>
    @endfor
</body>
</html>
