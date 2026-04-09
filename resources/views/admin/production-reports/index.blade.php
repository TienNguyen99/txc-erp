@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-industry me-2"></i>Quản lý Báo cáo Sản xuất</h4>
            <a href="{{ route('admin.production-reports.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Thêm Báo cáo
            </a>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Lệnh SX / Mã NV..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Tìm</button>
                </div>
            </form>
            <div class="table-responsive">
                <form id="productionActionForm" method="POST">
                    @csrf
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th><input type="checkbox" id="checkAllProduction"></th>
                                <th>#</th>
                                <th>Ngày SX</th>
                                <th>Ca</th>
                                <th>Mã NV</th>
                                <th>Lệnh SX</th>
                                <th>Công đoạn</th>
                                <th>Màu</th>
                                <th>Size (Mã HH)</th>
                                <th>SL Đạt</th>
                                <th>SL Hư</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                                <tr>
                                    <td><input type="checkbox" name="report_ids[]" value="{{ $item->id }}"
                                            class="production-check"></td>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->ngay_sx->format('d/m/Y') }}</td>
                                    <td>{{ $item->ca }}</td>
                                    <td>{{ $item->ma_nv }}</td>
                                    <td>{{ $item->lenh_sx }}</td>
                                    <td>
                                        @if ($item->cong_doan === 'Đã nhập kho')
                                            <span class="badge bg-success">{{ $item->cong_doan }}</span>
                                        @else
                                            <span class="badge bg-info">{{ $item->cong_doan }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->mau }}</td>
                                    <td>{{ $item->size }}</td>
                                    <td>{{ number_format($item->sl_dat, 2) }}</td>
                                    <td>{{ number_format($item->sl_hu, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.production-reports.edit', $item) }}"
                                            class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                        <button type="button" class="btn btn-danger btn-xs btn-delete-report"
                                            data-url="{{ route('admin.production-reports.destroy', $item) }}"
                                            onclick="if(confirm('Xóa báo cáo này?')){let f=document.getElementById('deleteReportForm');f.action=this.dataset.url;f.submit();}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-muted text-center">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>

            @if ($data->count())
                <div class="d-flex gap-2 mt-2 mb-3">
                    <button type="button" class="btn btn-success btn-sm" id="btnPushWarehouse">
                        <i class="fa-solid fa-warehouse me-1"></i>Nhập Kho (gộp theo Mã HH)
                    </button>
                </div>
            @endif

            {{ $data->links() }}
        </div>
    </div>

    <form id="deleteReportForm" method="POST" style="display:none">
        @csrf @method('DELETE')
    </form>

    <script>
        document.getElementById('checkAllProduction')?.addEventListener('change', function() {
            document.querySelectorAll('.production-check').forEach(cb => cb.checked = this.checked);
        });

        document.getElementById('btnPushWarehouse')?.addEventListener('click', function() {
            const form = document.getElementById('productionActionForm');
            const checked = form.querySelectorAll('.production-check:checked');
            if (checked.length === 0) return alert('Chọn ít nhất 1 báo cáo SX.');
            if (!confirm(`Nhập kho ${checked.length} báo cáo SX (gộp theo Mã HH)?`)) return;
            form.action = '{{ route('admin.production-reports.push-warehouse') }}';
            form.submit();
        });
    </script>
@endsection
