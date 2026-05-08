@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1"><i class="fa-solid fa-gears me-2"></i>Cấu hình hệ thống</h4>
            <p class="text-muted mb-0" style="font-size:.85rem">Quản lý các tham số hoạt động của xưởng</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-page" style="max-width: 800px;">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row g-4">
                @foreach($settings as $setting)
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size: .9rem">
                            {{ $setting->description ?: mb_convert_case(str_replace('_', ' ', $setting->key), MB_CASE_TITLE) }}
                            <span class="text-muted fw-normal ms-2" style="font-size: .8rem">({{ $setting->key }})</span>
                        </label>
                        
                        @if($setting->type === 'boolean')
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" role="switch" name="{{ $setting->key }}" value="1" {{ $setting->value == '1' ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $setting->value == '1' ? 'Đang bật' : 'Đang tắt' }}</label>
                            </div>
                            <input type="hidden" name="{{ $setting->key }}" value="0" @if($setting->value == '1') disabled @endif>
                        @elseif($setting->type === 'number')
                            <input type="number" step="any" class="form-control" name="{{ $setting->key }}" value="{{ $setting->value }}">
                        @elseif($setting->type === 'text')
                            <textarea class="form-control" name="{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                        @else
                            <input type="text" class="form-control" name="{{ $setting->key }}" value="{{ $setting->value }}">
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-4 text-end border-top pt-3">
                <button type="submit" class="btn btn-primary px-4 rounded-3 shadow-sm">
                    <i class="fa-solid fa-save me-2"></i>Lưu cấu hình
                </button>
            </div>
        </form>
        </div>
    </div>

    {{-- ── GOOGLE SHEETS SYNC PANEL ── --}}
    <div class="card-page mt-4" style="max-width:800px">
        <h6 class="fw-bold mb-1" style="font-size:.95rem">
            <i class="fa-brands fa-google me-2 text-success"></i>Google Sheets Sync (Apps Script)
        </h6>
        <p class="text-muted mb-3" style="font-size:.8rem">
            Tạo API token, sau đó dán vào Google Apps Script để đồng bộ đơn hàng trực tiếp từ Google Sheet.
        </p>

        @php $apiToken = \App\Models\Setting::where('key','api_sync_token')->value('value'); @endphp

        <div class="d-flex align-items-center gap-2 mb-3">
            <code id="tokenDisplay" class="flex-grow-1 p-2 rounded"
                style="background:#f8fafc;border:1px solid var(--border);font-size:.8rem;word-break:break-all">
                {{ $apiToken ?: '(chưa tạo token)' }}
            </code>
            @if($apiToken)
            <button onclick="navigator.clipboard.writeText('{{ $apiToken }}').then(()=>alert('Đã copy!'))"
                class="btn btn-outline-secondary btn-sm flex-shrink-0">
                <i class="fa-solid fa-copy"></i>
            </button>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.settings.generate-token') }}" class="d-inline">
            @csrf
            <button class="btn btn-success btn-sm"
                onclick="return confirm('Tạo token mới sẽ vô hiệu token cũ. Tiếp tục?')">
                <i class="fa-solid fa-rotate me-1"></i>{{ $apiToken ? 'Tạo lại token mới' : 'Tạo API Token' }}
            </button>
        </form>

        @if($apiToken)
        <hr class="my-3">
        <p class="fw-semibold mb-1" style="font-size:.82rem"><i class="fa-solid fa-code me-1 text-primary"></i>
            Endpoint để dán vào Apps Script:</p>
        <code style="font-size:.78rem;background:#f8fafc;padding:.5rem .8rem;border-radius:8px;display:block;border:1px solid var(--border)">
            POST {{ url('/api/orders/sync') }}
        </code>

        <details class="mt-3">
            <summary class="fw-semibold" style="font-size:.82rem;cursor:pointer;color:var(--primary)">
                <i class="fa-solid fa-file-code me-1"></i>Xem code Apps Script mẫu
            </summary>
            <pre style="background:#1e293b;color:#e2e8f0;border-radius:10px;padding:1rem;font-size:.72rem;overflow-x:auto;margin-top:.75rem">// ── Dán code này vào Google Apps Script ──
// Extensions → Apps Script → paste vào → Save

const ERP_URL   = "{{ url('/api/orders/sync') }}";
const API_TOKEN = "{{ $apiToken }}"; // ⚠️ Nếu tạo token mới phải cập nhật lại

// Cột header trong Google Sheet của bạn (dòng 1)
const COLUMN_MAP = {
  A: "job_no",       // BẮT BUỘC
  B: "fty_po",
  C: "im_number",
  D: "khach_hang",   // ma_kh hoặc ten_kh
  E: "ma_hh",
  F: "ten_hh",
  G: "color",
  H: "unit",
  I: "qty",
  J: "yrd",
  K: "price_usd",
  L: "tagtime_etc",  // VD: 2025-06-15
  M: "sig_need_date",
  N: "chart",
  O: "pl_number",
  P: "status",       // pending / in_production / done / shipped
};

function syncToERP() {
  const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  const lastRow = sheet.getLastRow();
  if (lastRow < 2) { SpreadsheetApp.getUi().alert("Sheet trống!"); return; }

  const colKeys = Object.keys(COLUMN_MAP);
  const firstCol = colKeys[0].charCodeAt(0) - 64;          // 1-based
  const lastCol  = colKeys[colKeys.length-1].charCodeAt(0) - 64;
  const data = sheet.getRange(2, firstCol, lastRow - 1, lastCol - firstCol + 1).getValues();

  const rows = data
    .filter(r => r[0] !== "" && r[0] !== null)  // bỏ dòng trống
    .map(r => {
      const obj = {};
      colKeys.forEach((col, i) => {
        let v = r[i];
        // Chuyển Date sang chuỗi YYYY-MM-DD
        if (v instanceof Date) v = Utilities.formatDate(v, Session.getScriptTimeZone(), "yyyy-MM-dd");
        obj[COLUMN_MAP[col]] = v === "" ? null : String(v).trim();
      });
      return obj;
    });

  const payload = JSON.stringify({ rows });
  const options = {
    method: "post",
    contentType: "application/json",
    headers: { Authorization: "Bearer " + API_TOKEN },
    payload: payload,
    muteHttpExceptions: true,
  };

  const res  = UrlFetchApp.fetch(ERP_URL, options);
  const json = JSON.parse(res.getContentText());
  SpreadsheetApp.getUi().alert(json.message || JSON.stringify(json));
}

function testConnection() {
  const res  = UrlFetchApp.fetch("{{ url('/api/orders/sync/status') }}", {
    headers: { Authorization: "Bearer " + API_TOKEN }, muteHttpExceptions: true,
  });
  const json = JSON.parse(res.getContentText());
  SpreadsheetApp.getUi().alert("Server: " + json.server + "\nOrders: " + json.orders + "\nTime: " + json.time);
}

// Thêm menu vào Sheet
function onOpen() {
  SpreadsheetApp.getUi().createMenu("🔄 ERP Sync")
    .addItem("✅ Test kết nối", "testConnection")
    .addItem("📤 Đồng bộ lên ERP", "syncToERP")
    .addToUi();
}</pre>
        </details>
        @endif
    </div>
</div>

@section('js')
<script>
    // Handle toggle switch hidden input logic
    document.querySelectorAll('.form-check-input').forEach(function(el) {
        el.addEventListener('change', function() {
            let hiddenInput = this.parentElement.nextElementSibling;
            if (this.checked) {
                hiddenInput.disabled = true;
                this.nextElementSibling.textContent = 'Đang bật';
            } else {
                hiddenInput.disabled = false;
                this.nextElementSibling.textContent = 'Đang tắt';
            }
        });
    });
</script>
@endsection
@endsection
