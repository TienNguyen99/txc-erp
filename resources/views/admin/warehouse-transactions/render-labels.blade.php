<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In Tem Dán Thùng</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f0f0f0;
        }
        .page {
            width: 194mm; /* 210mm - 2*8mm */
            height: 281mm; /* 297mm - 2*8mm */
            margin: 0 auto;
            background: #fff;
            page-break-after: always;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 6mm;
            padding: 0;
            box-sizing: border-box;
        }
        @media screen {
            .page {
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                margin-top: 20px;
                margin-bottom: 20px;
                padding: 10mm;
            }
        }
        @media print {
            body { background: #fff; }
            .page { box-shadow: none; margin: 0; padding: 0; }
        }
        .label {
            border: 2px solid #000;
            width: 100%;
            height: 100%;
            max-height: 135mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .header {
            text-align: center;
            color: #0000FF;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding: 6px 0;
        }
        .header h1 {
            font-size: 15px;
            margin: 0;
        }
        .header h2 {
            font-size: 13px;
            margin: 0;
        }
        .row {
            display: flex;
            border-bottom: 1px solid #000;
            min-height: 24px;
            align-items: stretch;
        }
        .row:last-child {
            border-bottom: none;
        }
        .col-label {
            width: 32%;
            border-right: 1px solid #000;
            padding: 3px 6px;
            font-weight: bold;
            font-style: italic;
            font-size: 11px;
            display: flex;
            align-items: center;
        }
        .col-value {
            width: 68%;
            padding: 3px 6px;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            word-break: break-word;
        }
        
        .yellow-bg {
            background-color: #FFFF00 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .huge-text {
            font-size: 20px !important;
            font-weight: 900 !important;
        }
        
        .watermark {
            position: absolute;
            bottom: 4px;
            right: 4px;
            color: red;
            font-weight: 900;
            font-size: 11px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
<body onload="window.print()">
    @php
        $chunks = array_chunk($selectedLabels->toArray(), 4);
    @endphp

    @foreach($chunks as $pageLabels)
    <div class="page">
        @foreach($pageLabels as $c)
        <div class="label">
            <div class="header">
                <h1>TEXENCO CORPORATION</h1>
            </div>
            <div class="header" style="border-bottom: 1px solid #000;">
                <h2>PRODUCT INFORMATION</h2>
            </div>
            
            <div class="row">
                <div class="col-label">DATE:</div>
                <div class="col-value">{{ $c['date'] }}</div>
            </div>
            <div class="row">
                <div class="col-label">CUSTOMER:</div>
                <div class="col-value">{{ $c['customer'] }}</div>
            </div>
            <div class="row">
                <div class="col-label">PKL No:</div>
                <div class="col-value">{{ $c['pkl'] }}</div>
            </div>
            <div class="row" style="flex: 1.2;">
                <div class="col-label">Item code</div>
                <div class="col-value" style="font-size: 11px;">{{ $c['item_code'] }}</div>
            </div>
            <div class="row">
                <div class="col-label">Color</div>
                <div class="col-value">{{ $c['color'] }}</div>
            </div>
            <div class="row">
                <div class="col-label">N/WEIGHT</div>
                <div class="col-value">{{ number_format((float)$c['nw'], 1) }} kgs</div>
            </div>
            <div class="row">
                <div class="col-label">G/WEIGHT</div>
                <div class="col-value">{{ number_format((float)$c['gw'], 1) }} kgs</div>
            </div>
            <div class="row" style="flex: 1;">
                <div class="col-label yellow-bg">JOB No.</div>
                <div class="col-value">{{ $c['job'] }}</div>
            </div>
            <div class="row">
                <div class="col-label yellow-bg">PO</div>
                <div class="col-value">{{ $c['po'] }}</div>
            </div>
            <div class="row">
                <div class="col-label">QUANTITY:</div>
                <div class="col-value huge-text">{{ $c['qty'] }} YARD</div>
            </div>
            <div class="row" style="border-bottom: none;">
                <div class="col-label" style="border-bottom: none;">Carton No:</div>
                <div class="col-value huge-text" style="justify-content: flex-start; padding-left: 20px; border-bottom: none;">
                    {{ $c['carton_no'] }} / {{ $c['total_cartons'] }}
                </div>
            </div>
            <div class="watermark">MADE IN VIET NAM</div>
        </div>
        @endforeach
    </div>
    @endforeach
</body>
</html>
