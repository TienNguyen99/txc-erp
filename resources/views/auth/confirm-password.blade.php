<x-guest-layout>
    <p class="text-white-50 small mb-3">
        Đây là khu vực bảo mật. Vui lòng xác nhận mật khẩu trước khi tiếp tục.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold text-white-50 text-uppercase"
                style="letter-spacing:.5px;font-size:.75rem;">Mật khẩu</label>
            <div class="position-relative">
                <i class="fa-solid fa-lock position-absolute text-white-50"
                    style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                <input id="password" type="password" name="password"
                    class="form-control auth-input @error('password') is-invalid @enderror" required
                    autocomplete="current-password" placeholder="••••••••">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-auth text-white">
                <i class="fa-solid fa-check-circle me-1"></i> Xác nhận
            </button>
        </div>
    </form>
</x-guest-layout>
