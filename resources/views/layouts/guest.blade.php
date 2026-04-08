<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TXC ERP') }}</title>
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

        .form-control.auth-input-simple {
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .15);
            color: #fff;
            border-radius: 12px;
            padding: .65rem 1rem;
        }

        .form-control.auth-input-simple:focus {
            background: rgba(255, 255, 255, .1);
            border-color: #f5a623;
            box-shadow: 0 0 0 .2rem rgba(245, 166, 35, .2);
            color: #fff;
        }

        .form-control.auth-input-simple::placeholder {
            color: rgba(255, 255, 255, .3);
        }

        .btn-auth {
            background: linear-gradient(135deg, #f5a623, #e08e0b);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: .7rem 1.5rem;
            transition: all .2s;
        }

        .btn-auth:hover {
            background: linear-gradient(135deg, #fbb740, #f5a623);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(245, 166, 35, .35);
        }

        .btn-outline-auth {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 12px;
            color: rgba(255, 255, 255, .5);
            font-weight: 500;
            padding: .7rem 1.5rem;
            transition: all .2s;
        }

        .btn-outline-auth:hover {
            border-color: rgba(255, 255, 255, .3);
            color: rgba(255, 255, 255, .8);
        }

        .link-amber {
            color: #f5a623;
            text-decoration: none;
        }

        .link-amber:hover {
            color: #fbb740;
        }

        .text-error {
            color: #f87171;
            font-size: .8rem;
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
                <a href="/">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3"
                        style="width:56px;height:56px;background:linear-gradient(135deg,#f5a623,#e08e0b);box-shadow:0 4px 15px rgba(245,166,35,.3);">
                        <i class="fa-solid fa-gear text-white fs-4"></i>
                    </div>
                </a>
                <h4 class="fw-bold text-white mb-1">TXC <span style="color:#f5a623;">ERP</span></h4>
            </div>

            {{ $slot }}
        </div>
    </div>

</body>

</html>
