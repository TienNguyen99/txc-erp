@extends('layouts.staff')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>Lịch sử nhập kho</h5>
        <a href="{{ route('staff.warehouse.index') }}" class="btn btn-sm btn-outline-success">
            <i class="fa-solid fa-plus me-1"></i>Nhập mới
        </a>
    </div>

    @if ($data->isEmpty())
        <div class="alert alert-info">Chưa có dữ liệu nhập kho nào.</div>
    @else
        <div class="staff-card p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0" style="font-size:.85rem">
                    <thead class="table-success">
                        <tr>
                            <th>Ngày</th>
                            <th>Mã HH</th>
                            <th>Màu</th>
                            <th>Kích</th>
                            <th class="text-end">SL</th>
                            <th>Lệnh SX</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->ngay)->format('d/m') }}</td>
                                <td class="fw-bold">{{ $row->ma_hh }}</td>
                                <td>{{ $row->mau ?: '—' }}</td>
                                <td>{{ $row->size ?: '—' }}</td>
                                <td class="text-end fw-bold">{{ number_format($row->so_luong) }}</td>
                                <td>{{ $row->lenh_sx ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3 d-flex justify-content-center">
            {{ $data->links() }}
        </div>
    @endif
@endsection
