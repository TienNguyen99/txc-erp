<x-guest-layout>
    <p class="text-white-50 small mb-3">
        Quên mật khẩu? Không sao! Nhập email của bạn và chúng tôi sẽ gửi link đặt lại mật khẩu.
    </p>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="alert alert-success py-2 rounded-3 small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-white-50 text-uppercase"
                style="letter-spacing:.5px;font-size:.75rem;">Email</label>
            <div class="position-relative">
                <i class="fa-solid fa-envelope position-absolute text-white-50"
                    style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                <input id="email" type="email" name="email"
                    class="form-control auth-input @error('email') is-invalid @enderror" value="{{ old('email') }}"
                    required autofocus placeholder="email@congty.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mt-4">
            <a href="{{ route('login') }}" class="small link-amber">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
            <button type="submit" class="btn btn-auth text-white">
                <i class="fa-solid fa-paper-plane me-1"></i> Gửi link đặt lại
            </button>
        </div>
    </form>
</x-guest-layout>
