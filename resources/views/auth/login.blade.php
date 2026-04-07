<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập — TXC ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 50%, #3b7dd8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Figtree', sans-serif;
        }

        .login-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .2);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
        }

        .login-card h4 {
            color: #1e3a5f;
            font-weight: 700;
        }

        .login-card .form-label {
            font-weight: 500;
            color: #555;
            font-size: .9rem;
        }

        .btn-login {
            background: linear-gradient(135deg, #1e3a5f, #2d5a8e);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .6rem;
            border-radius: 8px;
            width: 100%;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #163050, #1e3a5f);
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h4 class="mb-1"><i class="fa-solid fa-warehouse me-2"></i>TXC ERP</h4>
            <p class="text-muted small mb-0">Đăng nhập để tiếp tục</p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="alert alert-success small py-2">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email"
                    class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required
                    autofocus placeholder="email@example.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input id="password" type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror" required placeholder="••••••••">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Ghi nhớ</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login"><i class="fa-solid fa-right-to-bracket me-1"></i>Đăng
                nhập</button>
        </form>
    </div>
</body>

</html>
