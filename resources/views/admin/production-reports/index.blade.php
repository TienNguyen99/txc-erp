@extends('layouts.app')

@section('page-title', 'Báo cáo sản xuất')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-industry me-2"></i>Quản lý Báo cáo Sản xuất</h4>
            <div>
                <a href="{{ route('admin.production-reports.export', ['thang' => request('thang', now()->month), 'nam' => request('nam', now()->year)]) }}" class="btn btn-success btn-sm me-2">
                    <i class="fa-solid fa-file-excel me-1"></i>Xuất Excel
                </a>
                <a href="{{ route('admin.production-reports.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Thêm báo cáo
                </a>
            </div>
        </div>

        <div class="card-page">
            @include('admin.partials.alert')

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-2">
                    <input type="date" name="ngay_sx" class="form-control form-control-sm" value="{{ request('ngay_sx') }}" title="Ngày sản xuất">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" @selected(request('status') === 'pending')>Chờ duyệt</option>
                        <option value="approved" @selected(request('status') === 'approved')>Đã duyệt</option>
                        <option value="posted" @selected(request('status') === 'posted')>Đã tạo phiếu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="thang" class="form-select form-select-sm">
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @selected(($thang ?? now()->month) == $i)>Tháng {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="nam" class="form-select form-select-sm">
                        @for ($i = now()->year - 2; $i <= now()->year + 1; $i++)
                            <option value="{{ $i }}" @selected(($nam ?? now()->year) == $i)>Năm {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm lệnh SX / mã NV / mã hàng..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Tìm</button>
                </div>
            </form>

            <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-success btn-sm" id="btnApproveSelected">
                    <i class="fa-solid fa-check me-1"></i>Duyệt dòng chọn
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnCreateReceipt">
                    <i class="fa-solid fa-file-invoice me-1"></i>Tạo phiếu nhập kho
                </button>
            </div>

            <div class="table-responsive">
                <form id="productionActionForm" method="POST">
                    @csrf
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="no-sort"><input type="checkbox" id="checkAllProduction"></th>
                                <th>#</th>
                                <th>Ngày SX</th>
                                <th>Ca</th>
                                <th>Mã NV</th>
                                <th>Lệnh SX</th>
                                <th>Công đoạn</th>
                                <th>Màu</th>
                                <th>Size / Mã HH</th>
                                <th class="text-end">SL đạt</th>
                                <th class="text-end">SL hư</th>
                                <th>Trạng thái</th>
                                <th class="text-center no-sort">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                                <tr>
                                    <td>
                                        @if (! $item->isPosted())
                                            <input type="checkbox" name="report_ids[]" value="{{ $item->id }}" class="production-check">
                                        @endif
                                    </td>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->ngay_sx?->format('d/m/Y') }}</td>
                                    <td>{{ $item->ca }}</td>
                                    <td>{{ $item->ma_nv }}</td>
                                    <td>{{ $item->lenh_sx }}</td>
                                    <td><span class="badge bg-info">{{ $item->cong_doan ?: '-' }}</span></td>
                                    <td>{{ $item->mau }}</td>
                                    <td>{{ $item->size }}</td>
                                    <td class="text-end">{{ number_format((float) $item->sl_dat, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $item->sl_hu, 2) }}</td>
                                    <td>
                                        @if ($item->status === 'posted')
                                            <span class="badge bg-primary">Đã tạo phiếu</span>
                                        @elseif ($item->status === 'approved')
                                            <span class="badge bg-success">Đã duyệt</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($item->receipt)
                                            <a href="{{ route('admin.production-receipts.show', $item->receipt) }}"
                                                class="btn btn-info btn-xs" title="Xem phiếu nhập">
                                                <i class="fa-solid fa-file-lines"></i>
                                            </a>
                                        @endif
                                        @if (! $item->isPosted())
                                            @if ($item->status !== 'approved')
                                                <button type="button" class="btn btn-success btn-xs"
                                                    data-url="{{ route('admin.production-reports.approve', $item) }}"
                                                    onclick="if(confirm('Duyệt báo cáo này?')){let f=document.getElementById('approveReportForm');f.action=this.dataset.url;f.submit();}"
                                                    title="Duyệt báo cáo">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('admin.production-reports.edit', $item) }}"
                                                class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                            <button type="button" class="btn btn-danger btn-xs"
                                                data-url="{{ route('admin.production-reports.destroy', $item) }}"
                                                onclick="if(confirm('Xóa báo cáo này?')){let f=document.getElementById('deleteReportForm');f.action=this.dataset.url;f.submit();}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-muted text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>

            {{ $data->links() }}
        </div>
    </div>

    <form id="deleteReportForm" method="POST" style="display:none">
        @csrf @method('DELETE')
    </form>

    <form id="approveReportForm" method="POST" style="display:none">
        @csrf
    </form>

    <script>
        const actionForm = document.getElementById('productionActionForm');

        document.getElementById('checkAllProduction')?.addEventListener('change', function() {
            document.querySelectorAll('.production-check').forEach(cb => cb.checked = this.checked);
        });

        function selectedCount() {
            return actionForm.querySelectorAll('.production-check:checked').length;
        }

        document.getElementById('btnApproveSelected')?.addEventListener('click', function() {
            const count = selectedCount();
            if (count === 0) return alert('Chọn ít nhất 1 báo cáo sản xuất.');
            if (!confirm(`Duyệt ${count} báo cáo sản xuất đã chọn?`)) return;
            actionForm.action = '{{ route('admin.production-reports.approve-selected') }}';
            actionForm.submit();
        });

        document.getElementById('btnCreateReceipt')?.addEventListener('click', function() {
            const count = selectedCount();
            if (count === 0) return alert('Chọn ít nhất 1 báo cáo đã duyệt.');
            if (!confirm(`Tạo phiếu nhập kho từ ${count} báo cáo đã chọn?`)) return;
            actionForm.action = '{{ route('admin.production-reports.push-warehouse') }}';
            actionForm.submit();
        });
    </script>
@endsection
