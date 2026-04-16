<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lệnh SX — {{ $lenhSx }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --bg: #f0f4ff;
        }

        body {
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .scan-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            padding: 1.5rem 1rem;
            border-radius: 0 0 24px 24px;
        }

        .scan-card {
            background: #fff;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .06);
            margin-bottom: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }

        .info-item {
            padding: .5rem;
            background: #f8fafc;
            border-radius: 10px;
        }

        .info-item .label {
            font-size: .7rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
        }

        .info-item .value {
            font-size: .95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .btn-scan {
            border-radius: 12px;
            font-weight: 600;
            padding: .75rem;
            font-size: .9rem;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            font-weight: 600;
            font-size: .85rem;
        }

        .nav-pills .nav-link.active {
            background: var(--primary);
        }

        .history-item {
            padding: .5rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: .85rem;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .back-link {
            color: rgba(255,255,255,.8);
            text-decoration: none;
            font-size: .85rem;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            margin-bottom: .5rem;
        }
        .back-link:hover {
            color: #fff;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="scan-header text-center">
        <a href="{{ route('lenh-sx.index', $trackingNumber) }}" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
        </a>
        <div style="font-size:.75rem;opacity:.8">LỆNH SẢN XUẤT</div>
        <h4 class="fw-bold mb-1">{{ $lenhSx }}</h4>
        <div style="font-size:.8rem;opacity:.8">
            <i class="fa-solid fa-layer-group me-1"></i>{{ $trackingNumber }}
        </div>
        @if ($maHh)
            <div class="mt-1">
                <span class="badge bg-white text-primary" style="font-size:.85rem">{{ $maHh }}</span>
            </div>
        @endif
    </div>

    <div class="container px-3 py-3" style="max-width:600px">

        {{-- Alert --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 mb-3" style="border-radius:12px;font-size:.85rem">
                <i class="fa-solid fa-check-circle me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size:.7rem"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" style="border-radius:12px;font-size:.85rem">
                <i class="fa-solid fa-exclamation-circle me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size:.7rem"></button>
            </div>
        @endif

        {{-- Thông tin lệnh --}}
        <div class="scan-card">
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-info-circle text-primary me-1"></i>Thông tin lệnh</h6>
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Mã hàng</div>
                    <div class="value">{{ $maHh ?: '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Tên hàng hóa</div>
                    <div class="value">{{ $hangHoa?->ten_hh ?: '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Màu</div>
                    <div class="value">{{ $mau ?: '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">SL đơn hàng</div>
                    <div class="value">{{ number_format($totalSlDonHang, 0) }}</div>
                </div>
                <div class="info-item">
                    <div class="label">SL đã SX</div>
                    <div class="value text-info">{{ number_format($totalSlDat, 0) }}</div>
                </div>
                <div class="info-item">
                    <div class="label">SL hư</div>
                    <div class="value {{ $totalSlHu > 0 ? 'text-danger' : '' }}">{{ number_format($totalSlHu, 0) }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Đã nhập kho</div>
                    <div class="value text-success">{{ number_format($totalNhapKho, 0) }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Tồn kho hiện tại</div>
                    <div class="value {{ $tonKho > 0 ? 'text-success' : 'text-danger' }}">{{ number_format($tonKho, 0) }}</div>
                </div>
            </div>
            @if ($hangHoa?->hinh_anh)
                <div class="text-center mt-2">
                    <img src="{{ asset('storage/' . $hangHoa->hinh_anh) }}" alt="Hình ảnh"
                        style="max-width:100%;max-height:120px;border-radius:8px;object-fit:cover">
                </div>
            @endif
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-pills nav-fill mb-3 gap-1" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="pill" href="#tabSX" role="tab">
                    <i class="fa-solid fa-industry me-1"></i>Báo cáo SX
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="pill" href="#tabNhapKho" role="tab">
                    <i class="fa-solid fa-warehouse me-1"></i>Nhập kho
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- TAB: BÁO CÁO SX --}}
            <div class="tab-pane fade show active" id="tabSX" role="tabpanel">
                <div class="scan-card">
                    <form method="POST" action="{{ route('lenh-sx.report', [$trackingNumber, $stt]) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Công đoạn</label>
                            <select name="cong_doan" class="form-select" required>
                                <option value="Dệt">Dệt</option>
                                <option value="Định hình">Định hình</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">SL Đạt</label>
                                <input type="number" step="0.01" min="0" name="sl_dat" class="form-control"
                                    placeholder="0" required inputmode="decimal" autofocus>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">SL Hư</label>
                                <input type="number" step="0.01" min="0" name="sl_hu" class="form-control"
                                    placeholder="0" value="0" inputmode="decimal">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Ca</label>
                                <select name="ca" class="form-select">
                                    <option value="1">Ca 1</option>
                                    <option value="2">Ca 2</option>
                                    <option value="3">Ca 3</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Mã NV</label>
                                <input type="text" name="ma_nv" class="form-control" placeholder="Tên / mã NV">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-scan w-100"
                            onclick="return confirm('Xác nhận ghi báo cáo SX?')">
                            <i class="fa-solid fa-paper-plane me-1"></i>Gửi báo cáo SX
                        </button>
                    </form>
                </div>

                {{-- Lịch sử SX --}}
                @if ($history->count())
                    <div class="scan-card">
                        <h6 class="fw-bold mb-2" style="font-size:.85rem">
                            <i class="fa-solid fa-clock-rotate-left me-1 text-muted"></i>Lịch sử SX
                            <span class="badge bg-secondary ms-1">{{ $history->count() }}</span>
                        </h6>
                        @foreach ($history as $h)
                            <div class="history-item d-flex justify-content-between">
                                <div>
                                    <span class="badge bg-info" style="font-size:.7rem">{{ $h->cong_doan }}</span>
                                    <span class="ms-1">Ca {{ $h->ca }}</span>
                                    @if ($h->ma_nv)
                                        <span class="text-muted ms-1">• {{ $h->ma_nv }}</span>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success">{{ number_format($h->sl_dat, 0) }}</span>
                                    @if ($h->sl_hu > 0)
                                        <span class="text-danger ms-1">-{{ number_format($h->sl_hu, 0) }}</span>
                                    @endif
                                    <div class="text-muted" style="font-size:.7rem">{{ $h->created_at->format('d/m H:i') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- TAB: NHẬP KHO --}}
            <div class="tab-pane fade" id="tabNhapKho" role="tabpanel">
                <div class="scan-card">
                    <form method="POST" action="{{ route('lenh-sx.nhap-kho', [$trackingNumber, $stt]) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mã hàng</label>
                            <input type="text" class="form-control" value="{{ $maHh ?: '' }}" disabled>
                            <input type="hidden" name="ma_hh" value="{{ $maHh }}">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Số lượng nhập</label>
                                <input type="number" step="0.01" min="0.01" name="so_luong" class="form-control"
                                    placeholder="0" required inputmode="decimal">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Mã NV</label>
                                <input type="text" name="ma_nv" class="form-control" placeholder="Tên / mã NV">
                            </div>
                        </div>
                        <div class="p-2 mb-3" style="background:#ecfdf5;border-radius:10px;font-size:.85rem">
                            <div class="d-flex justify-content-between">
                                <span>Tồn kho hiện tại:</span>
                                <span class="fw-bold {{ $tonKho > 0 ? 'text-success' : 'text-danger' }}">{{ number_format($tonKho, 0) }}</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-scan w-100"
                            onclick="return confirm('Xác nhận nhập kho?')">
                            <i class="fa-solid fa-warehouse me-1"></i>Nhập kho
                        </button>
                    </form>
                </div>

                {{-- Lịch sử nhập kho --}}
                @if ($warehouseHistory->count())
                    <div class="scan-card">
                        <h6 class="fw-bold mb-2" style="font-size:.85rem">
                            <i class="fa-solid fa-clock-rotate-left me-1 text-muted"></i>Lịch sử nhập kho
                            <span class="badge bg-secondary ms-1">{{ $warehouseHistory->count() }}</span>
                        </h6>
                        @foreach ($warehouseHistory as $wh)
                            <div class="history-item d-flex justify-content-between">
                                <div>
                                    <span class="badge bg-success" style="font-size:.7rem">{{ $wh->cong_doan }}</span>
                                    @if ($wh->ma_nv)
                                        <span class="text-muted ms-1">{{ $wh->ma_nv }}</span>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success">{{ number_format($wh->so_luong, 0) }}</span>
                                    <div class="text-muted" style="font-size:.7rem">{{ $wh->created_at->format('d/m H:i') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
