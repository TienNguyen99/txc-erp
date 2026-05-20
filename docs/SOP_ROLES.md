# SOP Van Hanh Nhanh - TXC ERP

## 1) Admin
- Dau ngay: mo dashboard, kiem tra thong bao loi va ton kho am.
- Chay/kiem tra `ops:check-low-stock` neu scheduler chua chay.
- Don hang: tao/sua order, tao tracking lot, xac nhan trang thai.
- Cuoi ngay: doi soat so lieu order-tracking-kho, chot cac loi tre.

## 2) Ke Hoach / Dieu Phoi
- Tao lot tracking theo ngay giao.
- Day lenh san xuat theo lot, tranh day trung.
- Theo doi lot tre va cap nhat cong doan ngay.

## 3) San Xuat
- Quet QR lenh con.
- Bao cao `sl_dat`, `sl_hu` theo ca.
- Bao ngay khi vuot hao hut hoac thieu NVL.

## 4) Kho
- Nhap kho theo lenh/QR ngay trong ca.
- Khi dung cong Nhan vien, chi nhap so luong thuc te; he thong se tao phieu kho in duoc.
- Xuat kho theo yeu cau va ghi ro `lenh_sx`.
- Kiem tra ton kho am cuoi ca, tao bien ban dieu chinh neu can.

## 5) Mua Hang
- Lap PO tu dinh muc/BOM.
- Theo doi PO tre, cap nhat ngay nhan thuc te.
- Chot NCC tre han de canh bao ke hoach.

## 6) IT Van Hanh (1 nguoi)
- 09:00: kiem tra alert mail/telegram.
- 01:00 hang ngay: backup DB (`ops:backup-db`).
- Chu nhat 02:00: restore drill (`ops:restore-drill`).
- Moi thay doi lon: chay test luong loi (`php artisan test --filter=CoreFlowTest`).
