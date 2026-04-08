<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập — TXC ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f1923 0%, #1a2d42 50%, #0f1923 100%);
            font-family: 'Inter', sans-serif;
        }

        .gear-spin {
            animation: spin 20s linear infinite;
        }

        .gear-spin-r {
            animation: spin 25s linear infinite reverse;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .auth-card {
            background: rgba(255, 255, 255, .05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .form-control.auth-input {
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .15);
            color: #fff;
            border-radius: 12px;
            padding: .65rem 1rem .65rem 2.5rem;
        }

        .form-control.auth-input:focus {
            background: rgba(255, 255, 255, .1);
            border-color: #f5a623;
            box-shadow: 0 0 0 .2rem rgba(245, 166, 35, .2);
            color: #fff;
        }

        .form-control.auth-input::placeholder {
            color: rgba(255, 255, 255, .3);
        }

        .btn-auth {
            background: linear-gradient(135deg, #f5a623, #e08e0b);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: .7rem;
            transition: all .2s;
        }

        .btn-auth:hover {
            background: linear-gradient(135deg, #fbb740, #f5a623);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(245, 166, 35, .35);
        }

        .form-check-input:checked {
            background-color: #f5a623;
            border-color: #f5a623;
        }

        .link-amber {
            color: #f5a623;
            text-decoration: none;
        }

        .link-amber:hover {
            color: #fbb740;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center position-relative overflow-hidden">

    {{-- Gear decorations --}}
    <div class="position-fixed" style="top:-100px;left:-100px;opacity:.04;z-index:0;">
        <i class="fa-solid fa-gear gear-spin" style="font-size:280px;color:#f5a623;"></i>
    </div>
    <div class="position-fixed" style="bottom:-80px;right:-80px;opacity:.04;z-index:0;">
        <i class="fa-solid fa-gear gear-spin-r" style="font-size:220px;color:#f5a623;"></i>
    </div>
    <div class="position-fixed" style="top:40%;right:8%;opacity:.02;z-index:0;">
        <i class="fa-solid fa-gears gear-spin" style="font-size:120px;color:#f5a623;"></i>
    </div>

    <div class="container position-relative" style="z-index:1;max-width:430px;">
        <div class="auth-card rounded-4 shadow-lg p-4 p-sm-5">

            {{-- Logo --}}
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                    style="width:60px;height:60px;background:linear-gradient(135deg,#f5a623,#e08e0b);box-shadow:0 4px 15px rgba(245,166,35,.3);">
                    <i class="fa-solid fa-gear text-white fs-4"></i>
                </div>
                <h4 class="fw-bold text-white mb-1">TXC <span style="color:#f5a623;">ERP</span></h4>
                <p class="text-white-50 small mb-0">Hệ thống quản lý sản xuất cơ khí</p>
            </div>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="alert alert-success py-2 rounded-3 small">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-white-50 text-uppercase"
                        style="letter-spacing:.5px;font-size:.75rem;">Email</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-envelope position-absolute text-white-50"
                            style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                        <input id="email" type="email" name="email"
                            class="form-control auth-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required autofocus placeholder="email@congty.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold text-white-50 text-uppercase"
                        style="letter-spacing:.5px;font-size:.75rem;">Mật khẩu</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-lock position-absolute text-white-50"
                            style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                        <input id="password" type="password" name="password"
                            class="form-control auth-input @error('password') is-invalid @enderror" required
                            placeholder="••••••••">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Remember & Forgot --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                            style="border-radius:5px;">
                        <label class="form-check-label small text-white-50" for="remember">Ghi nhớ</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small link-amber">Quên mật khẩu?</a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn btn-auth w-100 text-white">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập
                </button>
            </form>

            {{-- Register link --}}
            @if (Route::has('register'))
                <div class="text-center mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,.08);">
                    <span class="small text-white-50">Chưa có tài khoản?</span>
                    <a href="{{ route('register') }}" class="small fw-semibold link-amber ms-1">Đăng ký ngay</a>
                </div>
            @endif
        </div>
    </div>

</body>

</html>
