@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title mb-0"><i class="fa-solid fa-building me-2"></i>Danh mục Khách hàng</h4>
            <a href="{{ route('admin.khach-hang.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>Thêm Khách hàng
            </a>
        </div>
        <div class="card-page">
            @include('admin.partials.alert')
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Tìm Mã KH / Tên KH..." value="{{ request('search') }}">
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
                            <th>Mã KH</th>
                            <th>Tên khách hàng</th>
                            <th>Người liên hệ</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th>MST</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td class="fw-semibold">{{ $item->ma_kh }}</td>
                                <td>{{ $item->ten_kh }}</td>
                                <td>{{ $item->nguoi_lien_he }}</td>
                                <td>{{ $item->sdt }}</td>
                                <td>{{ $item->email }}</td>
                                <td>{{ $item->ma_so_thue }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->active ? 'success' : 'secondary' }}">
                                        {{ $item->active ? 'Active' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-info btn-xs btn-config-group" data-id="{{ $item->id }}" data-bs-toggle="modal" data-bs-target="#configGroupModal" title="Cấu hình Nhóm hàng hóa">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </button>
                                    <a href="{{ route('admin.khach-hang.edit', $item) }}" class="btn btn-warning btn-xs" title="Sửa"><i
                                            class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.khach-hang.destroy', $item) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa khách hàng này?')">
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

    <!-- Config Group Modal -->
    <div class="modal fade" id="configGroupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:var(--radius)">
                <form method="POST" action="{{ route('admin.khach-hang.save-groups') }}">
                    @csrf
                    <input type="hidden" name="khach_hang_id" id="configKhachHangId" value="">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-layer-group me-2"></i>Cấu hình Nhóm hàng hóa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Quản lý danh sách các nhóm hàng hóa (tiền tố) cho khách hàng này.</p>
                        <table class="table table-sm" id="groupTable">
                            <thead>
                                <tr>
                                    <th>Mã nhóm (VD: TB)</th>
                                    <th>Tên nhóm (VD: Thun bản)</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddGroupRow"><i class="fa-solid fa-plus"></i> Thêm dòng</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.btn-config-group').forEach(btn => {
            btn.addEventListener('click', function() {
                const khId = this.dataset.id;
                document.getElementById('configKhachHangId').value = khId;
                
                // Fetch groups
                fetch('{{ route("admin.khach-hang.get-groups") }}?khach_hang_id=' + khId)
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.querySelector('#groupTable tbody');
                        tbody.innerHTML = '';
                        if (data.length === 0) {
                            addGroupRow();
                        } else {
                            data.forEach(item => addGroupRow(item.ma_nhom, item.ten_nhom));
                        }
                    });
            });
        });

        function addGroupRow(ma = '', ten = '') {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="ma_nhom[]" class="form-control form-control-sm" value="${ma}" required></td>
                <td><input type="text" name="ten_nhom[]" class="form-control form-control-sm" value="${ten ?? ''}"></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()"><i class="fa-solid fa-times"></i></button></td>
            `;
            document.querySelector('#groupTable tbody').appendChild(tr);
        }

        document.getElementById('btnAddGroupRow').addEventListener('click', () => addGroupRow());
    </script>
@endsection
