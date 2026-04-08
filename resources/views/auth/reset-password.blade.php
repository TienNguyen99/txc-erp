<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-white-50 text-uppercase"
                style="letter-spacing:.5px;font-size:.75rem;">Email</label>
            <div class="position-relative">
                <i class="fa-solid fa-envelope position-absolute text-white-50"
                    style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                <input id="email" type="email" name="email"
                    class="form-control auth-input @error('email') is-invalid @enderror"
                    value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold text-white-50 text-uppercase"
                style="letter-spacing:.5px;font-size:.75rem;">Mật khẩu mới</label>
            <div class="position-relative">
                <i class="fa-solid fa-lock position-absolute text-white-50"
                    style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                <input id="password" type="password" name="password"
                    class="form-control auth-input @error('password') is-invalid @enderror" required
                    autocomplete="new-password" placeholder="••••••••">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Confirm Password --}}
        <div class="mb-3">
            <label for="password_confirmation" class="form-label small fw-semibold text-white-50 text-uppercase"
                style="letter-spacing:.5px;font-size:.75rem;">Xác nhận mật khẩu</label>
            <div class="position-relative">
                <i class="fa-solid fa-shield-halved position-absolute text-white-50"
                    style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                <input id="password_confirmation" type="password" name="password_confirmation"
                    class="form-control auth-input" required autocomplete="new-password" placeholder="••••••••">
                @error('password_confirmation')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-auth text-white">
                <i class="fa-solid fa-key me-1"></i> Đặt lại mật khẩu
            </button>
        </div>
    </form>
</x-guest-layout>
