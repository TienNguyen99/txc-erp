@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h4 class="page-title mb-1"><i class="fa-solid fa-scale-balanced me-2"></i>Danh sách đơn vị tính</h4>
                <div class="text-muted small">ĐVT tồn kho là đơn vị chuẩn để tính tồn và giá vốn. Mỗi vật tư vẫn có thể mua theo ĐVT khác.</div>
            </div>
        </div>

        @include('admin.partials.alert')

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="card-page">
                    <h6 class="fw-bold mb-3">Thêm đơn vị tính</h6>
                    <form method="POST" action="{{ route('admin.units-of-measure.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-4"><label class="form-label">Mã ĐVT</label><input name="code" class="form-control" required placeholder="KG"></div>
                            <div class="col-8"><label class="form-label">Tên</label><input name="name" class="form-control" required placeholder="Kilogram"></div>
                            <div class="col-6"><label class="form-label">Nhóm đo lường</label><select name="dimension" class="form-select" required>@foreach (['mass' => 'Khối lượng', 'length' => 'Chiều dài', 'quantity' => 'Số lượng', 'volume' => 'Thể tích', 'packaging' => 'Đóng gói'] as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                            <div class="col-6"><label class="form-label">Hệ số về đơn vị gốc</label><input type="number" min="0.000001" step="0.000001" name="factor_to_base" value="1" class="form-control" required></div>
                            <div class="col-6 form-check ms-2 mt-3"><input type="checkbox" name="is_base" value="1" class="form-check-input" id="new-base"><label class="form-check-label" for="new-base">Đơn vị gốc</label></div>
                            <div class="col-5 form-check mt-3"><input type="checkbox" name="active" value="1" class="form-check-input" id="new-active" checked><label class="form-check-label" for="new-active">Đang dùng</label></div>
                        </div>
                        <button class="btn btn-primary mt-3"><i class="fa-solid fa-plus me-1"></i>Thêm ĐVT</button>
                    </form>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card-page p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light"><tr><th>Mã</th><th>Tên</th><th>Nhóm</th><th class="text-end">Hệ số về gốc</th><th>Vai trò</th><th>Trạng thái</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($units as $unit)
                                    <tr>
                                        <td><input form="unit-{{ $unit->id }}" name="code" value="{{ $unit->code }}" class="form-control form-control-sm" required></td>
                                        <td><input form="unit-{{ $unit->id }}" name="name" value="{{ $unit->name }}" class="form-control form-control-sm" required></td>
                                        <td><select form="unit-{{ $unit->id }}" name="dimension" class="form-select form-select-sm">@foreach (['mass' => 'Khối lượng', 'length' => 'Chiều dài', 'quantity' => 'Số lượng', 'volume' => 'Thể tích', 'packaging' => 'Đóng gói'] as $key => $label)<option value="{{ $key }}" @selected($unit->dimension === $key)>{{ $label }}</option>@endforeach</select></td>
                                        <td><input form="unit-{{ $unit->id }}" type="number" min="0.000001" step="0.000001" name="factor_to_base" value="{{ $unit->factor_to_base }}" class="form-control form-control-sm text-end" required></td>
                                        <td><label class="small"><input form="unit-{{ $unit->id }}" type="checkbox" name="is_base" value="1" @checked($unit->is_base)> Đơn vị gốc</label></td>
                                        <td><label class="small"><input form="unit-{{ $unit->id }}" type="checkbox" name="active" value="1" @checked($unit->active)> Đang dùng</label></td>
                                        <td class="text-end"><form id="unit-{{ $unit->id }}" method="POST" action="{{ route('admin.units-of-measure.update', $unit) }}">@csrf @method('PUT')<button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i></button></form></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
