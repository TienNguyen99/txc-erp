@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-warehouse me-2"></i>Quản lý Giao dịch Kho</h4>
            <a href="{{ route('admin.warehouse-transactions.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Thêm Giao dịch
            </a>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Lệnh SX / Mã NV..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="cong_doan" class="form-select form-select-sm">
                        <option value="">-- Loại --</option>
                        <option value="NHAPKHO" {{ request('cong_doan') == 'NHAPKHO' ? 'selected' : '' }}>NHẬP KHO</option>
                        <option value="XUATKHO" {{ request('cong_doan') == 'XUATKHO' ? 'selected' : '' }}>XUẤT KHO</option>
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
                            <th>Ngày</th>
                            <th>Loại</th>
                            <th>Mã HH</th>
                            <th>Size</th>
                            <th>Màu</th>
                            <th>Số lượng</th>
                            <th>Mã NV</th>
                            <th>Lệnh SX</th>
                            <th>Note</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->ngay->format('d/m/Y') }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $item->cong_doan == 'NHAPKHO' ? 'success' : 'danger' }}">{{ $item->cong_doan }}</span>
                                </td>
                                <td>{{ $item->ma_hh }}</td>
                                <td>{{ $item->size }}</td>
                                <td>{{ $item->mau }}</td>
                                <td>{{ number_format($item->so_luong, 2) }}</td>
                                <td>{{ $item->ma_nv }}</td>
                                <td>{{ $item->lenh_sx }}</td>
                                <td>{{ Str::limit($item->note, 30) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.warehouse-transactions.edit', $item) }}"
                                        class="btn btn-warning btn-xs"><i class="fa-solid fa-pen"></i></a>
                                    <form method="POST"
                                        action="{{ route('admin.warehouse-transactions.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa giao dịch này?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-xs"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-muted text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->links() }}
        </div>
    </div>
@endsection
