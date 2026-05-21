# TXC ERP Design System

Tai lieu nay mo ta giao dien hien tai cua TXC ERP trong codebase nay. Nguon tham chieu chinh la cac layout Blade dang dung: `resources/views/layouts/app.blade.php`, `resources/views/layouts/guest.blade.php`, `resources/views/auth/login.blade.php`, va `resources/views/layouts/staff.blade.php`.

## 1. Tinh than giao dien

TXC ERP la he thong van hanh noi bo cho quan tri, don hang, san xuat, kho, mua hang, tracking va phan quyen. Giao dien can uu tien toc do doc, thao tac lap lai, bang du lieu ro rang va trang thai de nhan biet.

Phong cach chinh cua admin la sang, gon, nen am nhe, surface trang, accent cam Texenco. Khong dung dark SaaS kieu Linear cho man hinh van hanh chinh. Dark/glass chi xuat hien o trang dang nhap de tao cam giac portal rieng biet.

Nguyen tac:

- Ro rang hon trang tri.
- Du lieu, bang, form va thao tac la trong tam.
- Mau cam dung de nhan dien thuong hieu va highlight hanh dong chinh, khong phu toan bo man hinh.
- Cac trang thai nghiep vu dung mau Bootstrap quen thuoc: xanh thanh cong, do loi/canh bao nang, vang cho xu ly, cyan/thong tin.
- Icon Font Awesome di cung nhan de nguoi dung scan nhanh module va action.

## 2. Brand & mau sac

### Admin app

Admin layout dung palette sang, am:

| Token | Gia tri | Vai tro |
| --- | --- | --- |
| `--primary` | `#f7941d` | Cam thuong hieu, icon active, focus, pagination active |
| `--primary-light` | `#fbb04c` | Avatar gradient, hover/highlight nhe |
| `--primary-dark` | `#e07b08` | Active nav, nhan manh trang thai cam |
| `--primary-rgb` | `247, 148, 29` | Shadow/focus/selection alpha |
| `--surface` | `#ffffff` | Sidebar, topbar, card, panel |
| `--bg` | `#fdf8f3` | Nen trang admin |
| `--text` | `#1e293b` | Text chinh |
| `--text-muted` | `#94a3b8` | Label phu, metadata, heading bang |
| `--border` | `#f0e8dc` | Border am, phan cach mem |

Mau phu dang dung:

- Hover nav/table: `#fff4e8`, `#fff0db`, `#fffaf5`.
- Table header: `#f8fafc`, hover header `#f1f5f9`.
- Body row border: `#f1f5f9`.
- Secondary text/nav: `#475569`, `#334155`.

### Auth / login

Auth dung dark warm glassmorphism:

| Mau | Vai tro |
| --- | --- |
| `#15171a`, `#24201b`, `#101113` | Nen gradient dang nhap |
| `#0f1923`, `#1a2d42` | Nen guest auth chung |
| `#f5a623` | Accent amber chinh |
| `#e08e0b` | Gradient amber dam |
| `#fbb740` | Hover amber sang |
| `#d97706` | Staff/admin alternate accent trong login |
| `rgba(255,248,236,.055)` | Auth/portal card glass surface |
| `rgba(245,166,35,.16)` | Border amber translucent |

Dark theme nay chi danh cho auth portal. Khong ap dung cho dashboard/admin CRUD.

### Staff app

Staff layout hien co nhanh xanh rieng:

| Token | Gia tri | Vai tro |
| --- | --- | --- |
| `--primary` | `#10b981` | Nut staff, border nav |
| `--primary-dark` | `#059669` | Hover staff |
| `--bg` | `#f0fdf4` | Nen staff |
| `--surface` | `#ffffff` | Card/nav |

Staff UI don gian hon admin: top navbar, container, card trang, action xanh.

## 3. Typography

Font chinh cua project la Inter tu Bunny Fonts.

- Admin: `Inter`, fallback `-apple-system, sans-serif`.
- Auth/staff: `Inter`, fallback `sans-serif`.
- Tailwind config hien van extend `Figtree`, nhung layout thuc te override bang Inter.

Kich thuoc dang dung:

| Vai tro | Size | Weight | Ghi chu |
| --- | --- | --- | --- |
| Page title | `1.25rem` | `700` | Tieu de man hinh/module |
| Topbar title | `.9rem` | `600` | Tieu de ngu canh |
| Sidebar nav | `.83rem` | `500`/`600 active` | Navigation day, de scan |
| Sidebar section label | `.65rem` | `700` | Uppercase, letter spacing `1px` |
| Table body | `.875rem` | normal | Du lieu chinh |
| Table header | `.72rem` | `600` | Uppercase, letter spacing `.5px` |
| Button | `.82rem` | `600` | Base button |
| Button small | `.8rem` | `600` | `.btn-sm` |
| Button extra small | `.75rem` | `600` | `.btn-xs` |
| Form label | `.8rem` | `600` | Label ngan, mau slate |
| Badge | `.72rem` | `600` | Pill trang thai |

Khong dung display typography qua lon trong man hinh nghiep vu. Heading nen chat, vua du de khong lam bang/form bi day xuong qua xa.

## 4. Layout admin

Admin layout gom 3 vung co dinh:

- Sidebar trai: `240px`, fixed, nen trang, border phai.
- Topbar: cao `56px`, fixed, bat dau sau sidebar.
- Main content: margin trai theo sidebar, padding top theo topbar.

Token layout:

| Token | Gia tri |
| --- | --- |
| `--sidebar-w` | `240px` |
| `--header-h` | `56px` |
| `--radius` | `14px` |
| `--radius-sm` | `10px` |
| `--shadow` | `0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.06)` |
| `--shadow-lg` | `0 4px 24px rgba(0,0,0,.08)` |

Responsive:

- Desktop: sidebar luon hien thi, main content co margin-left `240px`.
- Mobile/tablet duoi `991px`: sidebar an bang transform, topbar va content full width.
- Khi mo sidebar mobile, dung overlay `rgba(0,0,0,.3)`.

Sidebar:

- Brand logo can giua, chieu rong logo khoang `168px`, max-height `54px`.
- Section label uppercase, nho, muted.
- Nav item co icon fixed width `18px`, gap `10px`, padding `8px 10px`, radius `10px`.
- Hover: nen cam rat nhat `#fff4e8`, text primary.
- Active: nen `#fff0db`, text `--primary-dark`, font `600`.

Topbar:

- Nen trang, border bottom `--border`.
- Toggle button icon-only, hover cam nhat.
- Avatar la vong tron `34px`, gradient cam `#f7941d` -> `#fbb04c`.

## 5. Surfaces & cards

Card chinh dung class `.card-page`:

- Background: `#ffffff`.
- Border: `1px solid var(--border)`.
- Radius: `14px`.
- Padding: `1.5rem`.
- Shadow: `--shadow`.

Card khong nen qua trang tri. Trong ERP, card chu yeu dung de nhom form, bang, filter hoac summary. Tranh long card trong card neu khong that su can.

Staff card:

- Background trang.
- Radius `16px`.
- Padding `1.5rem`.
- Shadow nhe `0 1px 3px rgba(0,0,0,.06)`.

Auth card:

- Glass surface, blur `16px`.
- Border translucent.
- Radius lon (`rounded-4` hoac `20px`).
- Chi dung o man hinh dang nhap/portal.

## 6. Buttons

Button admin duoc thiet ke dang soft/tinted, khong dung nen cam dac cho moi primary action.

Base `.btn`:

- Radius `10px`.
- Font weight `600`.
- Font size `.82rem`.
- Border none mac dinh.
- Active scale `.97`.
- Focus visible: `0 0 0 3px rgba(247,148,29,.3)`.

Button variants:

| Class | Background | Text | Hover |
| --- | --- | --- | --- |
| `.btn-primary` | `rgba(247,148,29,.14)` | `#c55f00` | bg `.24`, text `#a04c00` |
| `.btn-danger` | `rgba(239,68,68,.1)` | `#dc2626` | bg `.18`, text `#b91c1c` |
| `.btn-warning` | `rgba(245,158,11,.1)` | `#b45309` | bg `.18`, text `#92400e` |
| `.btn-success` | `rgba(16,185,129,.1)` | `#059669` | bg `.18`, text `#047857` |
| `.btn-secondary` | `rgba(100,116,139,.1)` | `#475569` | bg `.18`, text `#334155` |
| `.btn-info` | `rgba(6,182,212,.1)` | `#0891b2` | bg `.18`, text `#0e7490` |

Outline buttons:

- `.btn-outline-primary`: border `rgba(247,148,29,.4)`, text `#c55f00`.
- `.btn-outline-secondary`: border `--border`, text `#475569`.
- `.btn-outline-danger`: border `rgba(239,68,68,.3)`, text `#dc2626`.

Sizes:

- `.btn-sm`: padding `.38rem .85rem`, font `.8rem`, radius `10px`.
- `.btn-xs`: padding `.28rem .65rem`, font `.75rem`, radius `8px`.

Guidelines:

- Dung icon Font Awesome trong action quan trong hoac action nho.
- Primary action trong CRUD thuong la `.btn-primary`, nhung van la soft orange.
- Action nguy hiem dung `.btn-danger` hoac `.btn-outline-danger`.
- Auth submit dung gradient amber dac, khong dung soft button admin.
- Staff submit dung `.btn-staff` xanh dac.

## 7. Tables

Table la thanh phan trung tam cua admin.

Base:

- `.table` font size `.875rem`, margin bottom `0`.
- Header nen `#f8fafc`.
- Header border bottom `2px solid var(--border)`.
- Header text muted, uppercase, letter spacing `.5px`.
- Cell padding `.7rem 1rem`.
- Body border `#f1f5f9`.
- Hover row: `#fffaf5`.

Sorting:

- Header sortable co cursor pointer.
- Indicator mac dinh la ky hieu hai chieu o ben phai, opacity thap.
- Asc/desc dung mau `--primary`.

Guidelines:

- Can phai so luong, tien, yard, ton kho.
- Can giua STT, status nho, action icon.
- Header nen ngan, uppercase khi can.
- Dung badge cho trang thai thay vi text thuong.
- Bang nhieu cot nen dat trong `.table-responsive`.

## 8. Forms

Form admin:

- `.form-control`, `.form-select` radius `10px`.
- Border `1.5px solid var(--border)`.
- Padding `.5rem .85rem`.
- Font size `.875rem`.
- Focus border `--primary`, shadow `0 0 0 3px rgba(247,148,29,.15)`.
- Label font `.8rem`, weight `600`, color `#475569`.

Auth inputs:

- Background translucent.
- Text trang.
- Radius `12px`.
- Padding co cho cho icon: `.65rem 1rem .65rem 2.5rem`.
- Focus theo accent amber hoac alternate staff/admin.

Guidelines:

- Form nghiep vu nen gon, dung Bootstrap grid.
- Label ngan, ro nghia nghiep vu.
- Error text nen dung do, nho, dat sat field.
- Select nang cao co the dung Tom Select theo CSS da import.

## 9. Badges, alerts, status

Badge:

- Font `.72rem`, weight `600`.
- Radius `20px`.
- Padding `.35em .75em`.

Alert:

- Radius `10px`.
- Border none.
- Font `.875rem`, weight `500`.
- Success alert: nen `#ecfdf5`, text `#065f46`.

Status color intent:

| Y nghia | Mau |
| --- | --- |
| Thanh cong / du hang / hoan tat | success green |
| Loi / thieu / nguy hiem | danger red |
| Cho / canh bao | warning amber |
| Dang san xuat / thong tin | info cyan |
| Nhap / trung tinh | secondary gray |
| Action chinh / brand | primary orange |

## 10. Auth portal

Trang dang nhap la trai nghiem rieng, dung dark warm background va portal cards.

Dac diem:

- Background gradient toi co radial amber glow nhe.
- Card glass voi `backdrop-filter: blur(16px)`.
- Portal card cao toi thieu `260px`, radius `20px`, hover nang `translateY(-6px)`.
- Portal icon `72px`, radius `18px`, gradient amber.
- Logo Texenco hien thi ro o buoc chon cong.
- Co gear icon mo lam trang tri nen.

Auth khong phai style mau cho admin app. Khong chuyen dashboard sang glass/dark theo login.

## 11. Staff interface

Staff layout la giao dien rut gon cho nhan vien:

- Navbar trang, border bottom xanh `2px solid #10b981`.
- Logo can giua/trai trong navbar.
- Nen xanh rat nhat `#f0fdf4`.
- Card trang radius `16px`.
- Button staff xanh dac.

Luong staff nen toi gian so buoc, uu tien thao tac nhanh tren kho/san xuat. Khong can sidebar phuc tap nhu admin.

## 12. Icons & imagery

Project dang dung Font Awesome 6.5.1.

Patterns:

- Sidebar: icon module truoc nhan.
- Buttons: icon nho truoc text cho action nhu them, luu, loc, in, dang xuat.
- Auth portal: icon lon trong o portal.
- Status/action icon nen dung cung mau voi trang thai.

Logo chinh: `storage/logo-texenco.png`.

Khong dung SVG hero/illustration kieu marketing cho admin. Hinh anh neu co nen phuc vu nghiep vu: logo, chung tu, preview, san pham, kho, san xuat.

## 13. Spacing & radius

Spacing hien tai:

- Page content: `24px`.
- Card padding: `1.5rem`.
- Sidebar section: `18px 12px 4px`.
- Nav item gap: `10px`.
- Topbar horizontal padding: `24px`.

Radius:

- Main card: `14px`.
- Small controls/nav/button: `10px`.
- Extra small button: `8px`.
- Auth input/button: `12px`.
- Auth portal card: `20px`.
- Badge: pill `20px`.

Guidelines:

- Giao dien nghiep vu dung radius vua phai.
- Khong dung bo goc qua lon cho bang/filter/form admin.
- Giu khoang cach theo nhip Bootstrap (`.mb-3`, `.g-3`, `.p-4`) de thong nhat.

## 14. Responsive rules

Admin:

- Duoi `991px`, sidebar tro thanh drawer.
- Topbar full width.
- Content khong giu margin trai.
- Overlay dong sidebar khi click ngoai.

Tables/forms:

- Bang nhieu cot can `.table-responsive`.
- Form filter nen wrap theo grid, tranh overflow ngang.
- Text trong button khong duoc tran; dung `.btn-sm`, icon-only co `title` khi can.

Auth:

- Portal cards hien dung 2 cot `.col-6`; can kiem tra mobile de khong qua chat neu them text dai.

## 15. Development guidelines

Khi them man hinh admin moi:

1. Extend `layouts.app`.
2. Dat noi dung trong `.page-content`.
3. Dung `.card-page` cho nhom noi dung chinh.
4. Dung `.page-title` cho tieu de.
5. Dung `.btn-primary` soft orange cho action chinh.
6. Dung `.table`, `.table-hover`, `.table-responsive` cho danh sach.
7. Dung form control Bootstrap mac dinh de huong token focus/radius.
8. Dung Font Awesome icon nhat quan voi module.
9. Can phai du lieu so, can giua action/status, giu label ngan.
10. Khong dua style dark Linear/indigo vao admin.

Khi them man hinh auth:

1. Dung `layouts.guest` hoac pattern tu `auth/login.blade.php`.
2. Giu nen dark warm va accent amber.
3. Input dung class `auth-input`.
4. Submit dung gradient amber.
5. Khong dung card trang admin trong auth.

Khi them man hinh staff:

1. Extend `layouts.staff`.
2. Dung `.staff-card`.
3. Action chinh dung `.btn-staff`.
4. Giu flow toi gian va de thao tac.

## 16. Khong nen lam

- Khong dung palette Linear: `#08090a`, indigo `#5e6ad2`, violet `#7170ff` cho admin.
- Khong bien admin thanh landing page/marketing page.
- Khong dung qua nhieu gradient trong man hinh nghiep vu.
- Khong lam dung shadow dam hoac glassmorphism ngoai auth.
- Khong tao component co mau rieng le neu token hien co dap ung duoc.
- Khong dung text mo ta UI dai trong man hinh chinh; ERP can thao tac nhanh.

## 17. Quick reference

Admin core:

```css
:root {
    --primary: #f7941d;
    --primary-light: #fbb04c;
    --primary-dark: #e07b08;
    --primary-rgb: 247, 148, 29;
    --surface: #ffffff;
    --bg: #fdf8f3;
    --text: #1e293b;
    --text-muted: #94a3b8;
    --border: #f0e8dc;
    --sidebar-w: 240px;
    --header-h: 56px;
    --radius: 14px;
    --radius-sm: 10px;
}
```

Admin primary button:

```css
.btn-primary {
    background: rgba(247, 148, 29, .14);
    color: #c55f00;
}

.btn-primary:hover {
    background: rgba(247, 148, 29, .24);
    color: #a04c00;
}
```

Admin card:

```css
.card-page {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #f0e8dc;
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.06);
    padding: 1.5rem;
}
```
