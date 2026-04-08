<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký — TXC ERP</title>
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

    <div class="container position-relative" style="z-index:1;max-width:450px;">
        <div class="auth-card rounded-4 shadow-lg p-4 p-sm-5">

            {{-- Logo --}}
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                    style="width:56px;height:56px;background:linear-gradient(135deg,#f5a623,#e08e0b);box-shadow:0 4px 15px rgba(245,166,35,.3);">
                    <i class="fa-solid fa-gear text-white fs-4"></i>
                </div>
                <h4 class="fw-bold text-white mb-1">TXC <span style="color:#f5a623;">ERP</span></h4>
                <p class="text-white-50 small mb-0">Tạo tài khoản mới</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold text-white-50 text-uppercase"
                        style="letter-spacing:.5px;font-size:.75rem;">Họ và tên</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-user position-absolute text-white-50"
                            style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                        <input id="name" type="text" name="name"
                            class="form-control auth-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required autofocus autocomplete="name"
                            placeholder="Nguyễn Văn A">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-white-50 text-uppercase"
                        style="letter-spacing:.5px;font-size:.75rem;">Email</label>
                    <div class="position-relative">
                        <i class="fa-solid fa-envelope position-absolute text-white-50"
                            style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                        <input id="email" type="email" name="email"
                            class="form-control auth-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required autocomplete="username" placeholder="email@congty.com">
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

                {{-- Submit --}}
                <button type="submit" class="btn btn-auth w-100 text-white mt-2">
                    <i class="fa-solid fa-user-plus me-1"></i> Đăng ký
                </button>
            </form>

            {{-- Login link --}}
            <div class="text-center mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,.08);">
                <span class="small text-white-50">Đã có tài khoản?</span>
                <a href="{{ route('login') }}" class="small fw-semibold link-amber ms-1">Đăng nhập</a>
            </div>
        </div>
    </div>

</body>

</html>
