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

        /* ---- Portal cards ---- */
        .portal-card {
            background: rgba(255, 255, 255, .05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            cursor: pointer;
            transition: all .3s ease;
            min-height: 260px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .portal-card:hover {
            transform: translateY(-6px);
            border-color: rgba(255, 255, 255, .25);
        }

        .portal-card.admin-portal:hover,
        .portal-card.admin-portal.active {
            border-color: #f5a623;
            box-shadow: 0 8px 32px rgba(245, 166, 35, .2);
        }

        .portal-card.staff-portal:hover,
        .portal-card.staff-portal.active {
            border-color: #10b981;
            box-shadow: 0 8px 32px rgba(16, 185, 129, .2);
        }

        .portal-icon {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .portal-icon.admin-icon {
            background: linear-gradient(135deg, #f5a623, #e08e0b);
            box-shadow: 0 4px 15px rgba(245, 166, 35, .3);
        }

        .portal-icon.staff-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 15px rgba(16, 185, 129, .3);
        }

        /* ---- Auth form ---- */
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
            color: #fff;
        }

        .form-control.auth-input.admin-focus:focus {
            border-color: #f5a623;
            box-shadow: 0 0 0 .2rem rgba(245, 166, 35, .2);
        }

        .form-control.auth-input.staff-focus:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 .2rem rgba(16, 185, 129, .2);
        }

        .form-control.auth-input::placeholder {
            color: rgba(255, 255, 255, .3);
        }

        .btn-admin {
            background: linear-gradient(135deg, #f5a623, #e08e0b);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: .7rem;
            transition: all .2s;
        }

        .btn-admin:hover {
            background: linear-gradient(135deg, #fbb740, #f5a623);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(245, 166, 35, .35);
        }

        .btn-staff {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: .7rem;
            transition: all .2s;
        }

        .btn-staff:hover {
            background: linear-gradient(135deg, #34d399, #10b981);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, .35);
        }

        .form-check-input.admin-check:checked {
            background-color: #f5a623;
            border-color: #f5a623;
        }

        .form-check-input.staff-check:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .link-amber {
            color: #f5a623;
            text-decoration: none;
        }

        .link-amber:hover {
            color: #fbb740;
        }

        .link-green {
            color: #10b981;
            text-decoration: none;
        }

        .link-green:hover {
            color: #34d399;
        }

        .btn-back {
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .12);
            color: rgba(255, 255, 255, .5);
            border-radius: 10px;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }

        /* Animations */
        .fade-in {
            animation: fadeIn .35s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

    <div class="container position-relative" style="z-index:1;">

        {{-- ====== STEP 1: Chọn cổng ====== --}}
        <div id="portal-select">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                    style="width:56px;height:56px;background:linear-gradient(135deg,#f5a623,#e08e0b);box-shadow:0 4px 15px rgba(245,166,35,.3);">
                    <i class="fa-solid fa-gear text-white fs-5"></i>
                </div>
                <h4 class="fw-bold text-white mb-1">TXC <span style="color:#f5a623;">ERP</span></h4>
                <p class="text-white-50 small mb-0">Chọn cổng đăng nhập</p>
            </div>

            <div class="row g-3 justify-content-center" style="max-width:640px;margin:0 auto;">
                <div class="col-6">
                    <div class="portal-card admin-portal" onclick="showLogin('admin')">
                        <div class="portal-icon admin-icon">
                            <i class="fa-solid fa-shield-halved text-white fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-white mb-1">Quản trị</h5>
                        <p class="text-white-50 small mb-0">Admin &bull; Quản lý</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="portal-card staff-portal" onclick="showLogin('staff')">
                        <div class="portal-icon staff-icon">
                            <i class="fa-solid fa-warehouse text-white fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-white mb-1">Nhân viên</h5>
                        <p class="text-white-50 small mb-0">Công nhân &bull; Kho</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== STEP 2: Form đăng nhập ====== --}}
        <div id="login-form" style="display:none;max-width:430px;margin:0 auto;">
            <div class="auth-card rounded-4 shadow-lg p-4 p-sm-5 fade-in">

                {{-- Back button --}}
                <button type="button" class="btn btn-back btn-sm mb-3" onclick="showPortal()">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </button>

                {{-- Logo --}}
                <div class="text-center mb-4">
                    <div id="login-icon" class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                        style="width:60px;height:60px;">
                        <i id="login-icon-i" class="text-white fs-4"></i>
                    </div>
                    <h5 id="login-title" class="fw-bold text-white mb-1"></h5>
                    <p id="login-subtitle" class="text-white-50 small mb-0"></p>
                </div>

                {{-- Session Status --}}
                @if (session('status'))
                    <div class="alert alert-success py-2 rounded-3 small">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="login_portal" id="login_portal" value="">

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-white-50 text-uppercase"
                            style="letter-spacing:.5px;font-size:.75rem;">Email</label>
                        <div class="position-relative">
                            <i class="fa-solid fa-envelope position-absolute text-white-50"
                                style="left:12px;top:50%;transform:translateY(-50%);font-size:.85rem;"></i>
                            <input id="email" type="email" name="email"
                                class="form-control auth-input input-focus @error('email') is-invalid @enderror"
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
                                class="form-control auth-input input-focus @error('password') is-invalid @enderror"
                                required placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Remember & Forgot --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input check-portal" type="checkbox" name="remember"
                                id="remember" style="border-radius:5px;">
                            <label class="form-check-label small text-white-50" for="remember">Ghi nhớ</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" id="forgot-link" class="small">Quên mật
                                khẩu?</a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <button type="submit" id="btn-submit" class="btn w-100 text-white">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập
                    </button>
                </form>
            </div>
        </div>

    </div>

    <script>
        function showLogin(portal) {
            document.getElementById('portal-select').style.display = 'none';
            var form = document.getElementById('login-form');
            form.style.display = 'block';
            form.querySelector('.auth-card').classList.add('fade-in');

            document.getElementById('login_portal').value = portal;

            var icon = document.getElementById('login-icon');
            var iconI = document.getElementById('login-icon-i');
            var title = document.getElementById('login-title');
            var subtitle = document.getElementById('login-subtitle');
            var btn = document.getElementById('btn-submit');
            var forgot = document.getElementById('forgot-link');
            var inputs = document.querySelectorAll('.input-focus');
            var check = document.querySelector('.check-portal');

            if (portal === 'admin') {
                icon.style.background = 'linear-gradient(135deg,#f5a623,#e08e0b)';
                icon.style.boxShadow = '0 4px 15px rgba(245,166,35,.3)';
                iconI.className = 'fa-solid fa-shield-halved text-white fs-4';
                title.innerHTML = 'Quản trị viên';
                subtitle.innerHTML = 'Đăng nhập hệ thống quản lý';
                btn.className = 'btn btn-admin w-100 text-white';
                if (forgot) forgot.className = 'small link-amber';
                inputs.forEach(function(el) {
                    el.classList.remove('staff-focus');
                    el.classList.add('admin-focus');
                });
                check.classList.remove('staff-check');
                check.classList.add('admin-check');
            } else {
                icon.style.background = 'linear-gradient(135deg,#10b981,#059669)';
                icon.style.boxShadow = '0 4px 15px rgba(16,185,129,.3)';
                iconI.className = 'fa-solid fa-warehouse text-white fs-4';
                title.innerHTML = 'Nhân viên kho';
                subtitle.innerHTML = 'Đăng nhập để nhập kho';
                btn.className = 'btn btn-staff w-100 text-white';
                if (forgot) forgot.className = 'small link-green';
                inputs.forEach(function(el) {
                    el.classList.remove('admin-focus');
                    el.classList.add('staff-focus');
                });
                check.classList.remove('admin-check');
                check.classList.add('staff-check');
            }
        }

        function showPortal() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('portal-select').style.display = 'block';
        }

        // Nếu có lỗi validation, tự động mở lại form
        @if ($errors->any() || old('login_portal'))
            showLogin('{{ old('login_portal', 'admin') }}');
        @endif
    </script>
</body>

</html>
