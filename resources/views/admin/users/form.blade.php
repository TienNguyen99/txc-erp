@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none"
                style="font-size:.85rem;color:var(--primary);font-weight:500">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
            <h4 class="page-title mt-2 mb-0">
                <i class="fa-solid fa-user me-2"></i>{{ isset($user) ? 'Sửa User: ' . $user->name : 'Thêm User' }}
            </h4>
        </div>

        {{-- ══ Tabs (chỉ hiện khi edit) ══ --}}
        @if(isset($user))
        <ul class="nav nav-tabs mb-0" id="userTab" role="tablist" style="border-bottom:none">
            <li class="nav-item">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#tab-info" type="button">
                    <i class="fa-solid fa-user-pen me-1"></i>Thông tin
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="perm-tab" data-bs-toggle="tab" data-bs-target="#tab-perm" type="button">
                    <i class="fa-solid fa-shield-halved me-1"></i>Quyền riêng
                    @if(!empty($userDirectPermissions))
                        <span class="badge bg-primary ms-1" style="font-size:.7rem">{{ count($userDirectPermissions) }}</span>
                    @endif
                </button>
            </li>
        </ul>
        @endif

        <div class="tab-content">
            {{-- ══ Tab 1: Thông tin cơ bản ══ --}}
            <div class="tab-pane fade show active" id="tab-info">
                <div class="card-page" style="border-radius: isset($user) ? '0 8px 8px 8px' : '8px'">
                    @include('admin.partials.alert')
                    <form method="POST"
                        action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}">
                        @csrf
                        @if (isset($user))
                            @method('PUT')
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tên</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name ?? '') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email ?? '') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mật khẩu
                                    {{ isset($user) ? '(để trống nếu không đổi)' : '' }}</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                    {{ isset($user) ? '' : 'required' }}>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vai trò (Role)</label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    @php $currentRole = old('role', isset($user) ? $user->getRoleNames()->first() : 'staff'); @endphp
                                    <option value="staff"   {{ $currentRole === 'staff'   ? 'selected' : '' }}>Staff — Nhân viên kho</option>
                                    <option value="manager" {{ $currentRole === 'manager' ? 'selected' : '' }}>Manager — Quản lý (cần cấp quyền)</option>
                                    <option value="admin"   {{ $currentRole === 'admin'   ? 'selected' : '' }}>Admin — Toàn quyền</option>
                                </select>
                                <div class="form-text">Role cấp quyền mặc định theo nhóm. Quyền riêng có thể tuỳ chỉnh ở tab bên.</div>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Lưu thông tin</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ══ Tab 2: Quyền riêng (chỉ khi edit) ══ --}}
            @if(isset($user))
            <div class="tab-pane fade" id="tab-perm">
                <div class="card-page" style="border-radius:0 8px 8px 8px">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Quyền riêng cho <strong>{{ $user->name }}</strong></h6>
                            <small class="text-muted">Quyền riêng này <em>cộng thêm</em> vào role hiện tại. Admin có toàn quyền mặc định.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm" id="btnCheckAll">
                                <i class="fa-solid fa-check-double me-1"></i>Chọn tất cả
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnUncheckAll">
                                <i class="fa-solid fa-xmark me-1"></i>Bỏ tất cả
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.users.sync-permissions', $user) }}" id="permForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-3" id="permTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="min-width:160px">Module</th>
                                        <th class="text-center" style="width:80px">
                                            <i class="fa-solid fa-eye me-1"></i>View
                                        </th>
                                        <th class="text-center" style="width:80px">
                                            <i class="fa-solid fa-plus me-1"></i>Create
                                        </th>
                                        <th class="text-center" style="width:80px">
                                            <i class="fa-solid fa-pen me-1"></i>Edit
                                        </th>
                                        <th class="text-center" style="width:80px">
                                            <i class="fa-solid fa-trash me-1"></i>Delete
                                        </th>
                                        <th class="text-center" style="width:80px">
                                            <i class="fa-solid fa-file-import me-1"></i>Import
                                        </th>
                                        <th class="text-center" style="width:80px">
                                            <i class="fa-solid fa-file-export me-1"></i>Export
                                        </th>
                                        <th class="text-center" style="width:100px">Tất cả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $allActions = ['view','create','edit','delete','import','export'];
                                    @endphp
                                    @foreach($modules as $module => $config)
                                    <tr>
                                        <td class="fw-semibold">
                                            <span class="badge bg-light text-dark border me-1" style="font-size:.7rem">{{ $module }}</span>
                                            {{ $config['label'] }}
                                        </td>
                                        @foreach($allActions as $action)
                                            @php
                                                $permName = "{$module}.{$action}";
                                                $hasAction = in_array($action, $config['actions']);
                                                $checked = in_array($permName, $userDirectPermissions ?? []);
                                                $role = $user->roles->first();
                                                $fromRole = $role && $role->permissions->contains('name', $permName);
                                            @endphp
                                            <td class="text-center">
                                                @if($hasAction)
                                                    <div class="position-relative d-inline-block">
                                                        <input type="checkbox"
                                                            class="form-check-input perm-check perm-row-{{ $module }} perm-col-{{ $action }}"
                                                            name="permissions[]"
                                                            value="{{ $permName }}"
                                                            id="perm_{{ $module }}_{{ $action }}"
                                                            {{ $checked ? 'checked' : '' }}>
                                                        @if($user->hasRole('admin') || ($user->roles->first() && $user->roles->first()->hasPermissionTo($permName)))
                                                            <span title="Đã có từ Role" style="position:absolute;top:-6px;right:-8px;font-size:.6rem;color:var(--primary)">
                                                                <i class="fa-solid fa-circle-check"></i>
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted" style="font-size:.75rem">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        {{-- Checkbox "Chọn cả hàng" --}}
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input row-check" data-module="{{ $module }}"
                                                title="Chọn tất cả {{ $config['label'] }}">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Legend --}}
                        <div class="d-flex gap-3 mb-3" style="font-size:.8rem">
                            <span><i class="fa-solid fa-circle-check text-primary me-1"></i>= Đã có từ Role (luôn active)</span>
                            <span><i class="fa-solid fa-square-check text-success me-1"></i>= Quyền riêng bổ sung</span>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-shield-halved me-1"></i>Lưu quyền riêng
                        </button>
                        <button type="button" class="btn btn-outline-danger ms-2" id="btnClearPerms">
                            <i class="fa-solid fa-rotate-left me-1"></i>Xóa toàn bộ quyền riêng
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>{{-- end tab-content --}}
    </div>

<style>
#permTable th { font-size: .8rem; }
#permTable td { vertical-align: middle; }
.perm-check { cursor: pointer; width: 1.1rem; height: 1.1rem; }
.row-check { cursor: pointer; width: 1.1rem; height: 1.1rem; accent-color: var(--primary, #6366f1); }
.nav-tabs .nav-link { color: #666; font-size: .88rem; }
.nav-tabs .nav-link.active { font-weight: 600; color: var(--primary, #6366f1); border-bottom-color: #fff; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Chọn theo hàng (Module)
    document.querySelectorAll('.row-check').forEach(function (rowCb) {
        rowCb.addEventListener('change', function () {
            const mod = this.dataset.module;
            document.querySelectorAll('.perm-row-' + mod).forEach(cb => cb.checked = this.checked);
        });
    });

    // Sync row-check khi tick từng ô
    document.querySelectorAll('.perm-check').forEach(function (cb) {
        cb.addEventListener('change', syncRowChecks);
    });

    function syncRowChecks() {
        document.querySelectorAll('.row-check').forEach(function (rowCb) {
            const mod = rowCb.dataset.module;
            const allInRow = document.querySelectorAll('.perm-row-' + mod);
            rowCb.checked = [...allInRow].every(c => c.checked);
            rowCb.indeterminate = !rowCb.checked && [...allInRow].some(c => c.checked);
        });
    }
    syncRowChecks();

    // Nút chọn tất cả
    document.getElementById('btnCheckAll')?.addEventListener('click', function () {
        document.querySelectorAll('.perm-check').forEach(c => c.checked = true);
        document.querySelectorAll('.row-check').forEach(c => { c.checked = true; c.indeterminate = false; });
    });

    // Nút bỏ tất cả
    document.getElementById('btnUncheckAll')?.addEventListener('click', function () {
        document.querySelectorAll('.perm-check').forEach(c => c.checked = false);
        document.querySelectorAll('.row-check').forEach(c => { c.checked = false; c.indeterminate = false; });
    });

    // Nút xóa toàn bộ quyền riêng
    document.getElementById('btnClearPerms')?.addEventListener('click', function () {
        if (!confirm('Xóa toàn bộ quyền riêng của user này? (Quyền từ role vẫn giữ nguyên)')) return;
        document.querySelectorAll('.perm-check').forEach(c => c.checked = false);
        document.getElementById('permForm').submit();
    });
});
</script>
@endsection
