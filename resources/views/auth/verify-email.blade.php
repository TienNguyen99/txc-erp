<x-guest-layout>
    <p class="text-white-50 small mb-3">
        Cảm ơn bạn đã đăng ký! Vui lòng xác minh email bằng cách nhấn vào link chúng tôi vừa gửi. Nếu không nhận được,
        chúng tôi sẽ gửi lại cho bạn.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success py-2 rounded-3 small">
            Link xác minh mới đã được gửi đến email của bạn.
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-auth text-white">
                <i class="fa-solid fa-paper-plane me-1"></i> Gửi lại email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-auth">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất
            </button>
        </form>
    </div>
</x-guest-layout>
