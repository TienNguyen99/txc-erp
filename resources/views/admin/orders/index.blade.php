@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="card-page">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 fw-bold" style="color:#1e3a5f"><i class="fa-solid fa-file-invoice me-2"></i>Quản lý Đơn hàng
                </h5>
                <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm"><i
                        class="fa-solid fa-plus me-1"></i>Thêm Đơn hàng</a>
            </div>
            @include('admin.partials.alert')
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Job No / Fty PO..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Trạng thái --</option>
                        @foreach (['pending', 'in_production', 'done', 'shipped'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                {{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-search me-1"></i>Tìm</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Job No</th>
                            <th>Fty PO</th>
                            <th>IM#</th>
                            <th>Color</th>
                            <th>Qty</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td class="fw-semibold">{{ $item->job_no }}</td>
                                <td>{{ $item->fty_po }}</td>
                                <td>{{ $item->im_number }}</td>
                                <td>{{ $item->color }}</td>
                                <td>{{ number_format($item->qty, 2) }}</td>
                                <td>{{ $item->size }}</td>
                                <td>
                                    @php $colors = ['pending'=>'warning','in_production'=>'info','done'=>'success','shipped'=>'primary']; @endphp
                                    <span
                                        class="badge bg-{{ $colors[$item->status] ?? 'secondary' }}">{{ $item->status }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.orders.edit', $item) }}" class="btn btn-warning btn-xs"><i
                                            class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.orders.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa đơn hàng này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->links() }}
        </div>
    </div>
@endsection
