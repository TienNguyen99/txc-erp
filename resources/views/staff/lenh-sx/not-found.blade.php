<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Không tìm thấy lệnh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body {
            background: #f0f4ff;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .not-found {
            text-align: center;
            padding: 2rem;
        }
        .not-found i {
            font-size: 4rem;
            color: #f59e0b;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="not-found">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <h5 class="fw-bold">Không tìm thấy</h5>
        <p class="text-muted">Không tìm thấy lệnh sản xuất<br>
            <strong>{{ $trackingNumber }}</strong></p>
        <p class="text-muted" style="font-size:.85rem">Vui lòng kiểm tra lại mã QR hoặc liên hệ quản lý.</p>
    </div>
</body>
</html>
