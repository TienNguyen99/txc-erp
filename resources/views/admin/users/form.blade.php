@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <div class="mb-4">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none"
                style="font-size:.85rem;color:var(--primary);font-weight:500">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
            </a>
            <h4 class="page-title mt-2 mb-0">
                <i class="fa-solid fa-user me-2"></i>{{ isset($user) ? 'Sửa User' : 'Thêm User' }}
            </h4>
        </div>
        <div class="card-page">
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
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i>Lưu</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ms-2">Hủy</a>
                </div>
            </form>
        </div>
    </div>
@endsection
