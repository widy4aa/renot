# DOKUMENTASI TEKNIS — ReNot
## Reminder & Notification Sertifikasi Pegawai

**Versi dokumen:** 1.1
**Terakhir diperbarui:** 8 September 2026
**Status project:** Sprint 2 selesai (Role Pegawai) + UI Redesign Modern Bold — Role Admin belum dibangun

---

## Daftar Isi

1. [Overview Project](#1-overview-project)
2. [Tech Stack](#2-tech-stack)
3. [Cara Menjalankan](#3-cara-menjalankan)
4. [Struktur Project](#4-struktur-project)
5. [Database](#5-database)
6. [Backend — Laravel](#6-backend--laravel)
7. [Frontend — Vue](#7-frontend--vue)
8. [Design System](#8-design-system)
9. [Akun Seeder](#9-akun-seeder)
10. [API Endpoints](#10-api-endpoints)
11. [Status Implementasi](#11-status-implementasi)
12. [Yang Perlu Dilanjutkan](#12-yang-perlu-dilanjutkan)

---

## 1. Overview Project

ReNot adalah aplikasi internal perusahaan (konteks Pertamina) untuk memantau masa berlaku dokumen/sertifikasi pegawai (HSSE & Aviasi) dan mengirim reminder otomatis sebelum dokumen kadaluarsa.

**Dua role:**
- **Admin** — mengelola semua pegawai, dokumen, approval, konfigurasi sistem
- **Pegawai** — mengelola dokumen milik sendiri, menerima reminder

**Dokumen referensi:**
- `SRS-ReNot.md` — Software Requirements Specification lengkap
- `database.dbml` — Database diagram (bisa dibuka di dbdiagram.io)
- `PHILOSOPHY.md` — Design system dan aturan UI v1.1 (WAJIB dibaca sebelum membuat UI baru)

---

## 2. Tech Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | Laravel | 13.x |
| Database | PostgreSQL | 18 (via Podman container) |
| Auth | Laravel Sanctum | 4.x |
| Frontend | Vue 3 (Composition API) | Latest |
| Build | Vite | 8.x |
| CSS | Tailwind CSS | v4 (`@tailwindcss/vite`) |
| State | Pinia | Latest |
| Router | Vue Router | Latest |
| HTTP | Axios | Latest |
| Excel Export | maatwebsite/excel | 4.x (require `--ignore-platform-req=ext-gd --ignore-platform-req=ext-iconv`) |
| Font | Plus Jakarta Sans | Google Fonts |

---

## 3. Cara Menjalankan

### Prerequisites

```bash
# PHP 8.5, Composer, Node 26, npm 12
# PostgreSQL via Podman (sudah running di port 5432)
podman start postgres_db
```

### Setup

```bash
cd laravel-vue-template

# Install dependencies (jika belum)
composer install
npm install

# Copy env jika belum ada
cp .env.example .env
php artisan key:generate
```

### Database

```env
# .env — kredensial PostgreSQL (Podman container)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=renot
DB_USERNAME=root
DB_PASSWORD=root
```

```bash
# Buat database jika belum ada
podman exec postgres_db psql -U root -d db -c "CREATE DATABASE renot;"

# Jalankan migration + seeder
php artisan migrate --seed

# Atau reset dari awal
php artisan migrate:fresh --seed
```

### Menjalankan

```bash
# Terminal 1 — Backend
php artisan serve

# Terminal 2 — Frontend (dev dengan HMR)
npm run dev

# Atau build production
npm run build
```

Buka: `http://localhost:8000`

### Storage (untuk upload foto/file)

```bash
php artisan storage:link
# Membuat symlink public/storage → storage/app/public
```

---

## 4. Struktur Project

```
laravel-vue-template/
├── app/
│   ├── Exports/
│   │   ├── DocumentsExport.php        ← Export Excel admin (filter: status, dept, user, category)
│   │   └── MyDocumentsExport.php      ← Export Excel pegawai (dokumen milik sendiri)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php     ← login, logout, me, forgotPassword, resetPassword
│   │   │   ├── Pegawai/
│   │   │   │   ├── DashboardController.php    ← GET /api/pegawai/dashboard
│   │   │   │   ├── DocumentController.php     ← CRUD dokumen + export + download
│   │   │   │   ├── NotificationController.php ← list, markAsRead, markAllAsRead
│   │   │   │   └── ProfileController.php      ← show, update (HP+foto), changePassword
│   │   │   └── CertificationCategoryController.php ← GET /api/certification-categories
│   │   └── Middleware/
│   │       └── EnsureRole.php         ← Middleware role guard (pakai alias 'role:pegawai')
│   └── Models/
│       ├── User.php                   ← Satu tabel untuk admin & pegawai (kolom role)
│       ├── Department.php
│       ├── CertificationCategory.php  ← HSSE & Aviasi (is_deletable=false)
│       ├── CertificationType.php      ← GSI, SI, AT, RDS, PACE, dll (bisa CRUD admin)
│       ├── Document.php               ← Status: pending_approval, ditolak, aktif, segera_expired, expired
│       ├── DocumentVersion.php        ← Riwayat file saat dokumen diganti
│       ├── Notification.php           ← In-app & email notification
│       ├── ReminderSchedule.php       ← Jadwal H-X (bisa dikonfigurasi admin dari UI)
│       ├── ReminderLog.php            ← Mencegah duplicate reminder
│       ├── EmailTemplate.php          ← Template email per jenis notifikasi
│       ├── SystemSetting.php          ← Key-value store pengaturan sistem
│       └── ActivityLog.php            ← Audit trail semua aktivitas
├── database/
│   ├── migrations/                    ← 14 migration file
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── DepartmentSeeder.php       ← 5 departemen
│       ├── CertificationCategorySeeder.php ← HSSE (6 jenis) + Aviasi (3 jenis)
│       ├── ReminderScheduleSeeder.php ← 12 jadwal (H-60 s/d H-1)
│       ├── UserSeeder.php             ← 1 admin + 3 pegawai
│       ├── DocumentSeeder.php         ← Dokumen realistis per pegawai
│       └── NotificationSeeder.php     ← Notifikasi sample per user
├── routes/
│   ├── api.php                        ← Semua API routes
│   └── web.php                        ← Catch-all → SPA (Vue Router handle)
├── public/
│   ├── og.jpg                         ← Gambar hero halaman login (panel kiri 70%)
│   └── icon.png                       ← Icon/logo aplikasi ReNot
└── resources/
    ├── css/app.css                    ← Tailwind v4 + design tokens (Modern Bold v1.1)
    ├── views/welcome.blade.php        ← SPA entry point + load Plus Jakarta Sans
    └── js/
        ├── app.js                     ← Entry point — init Pinia → fetchUser → mount router → mount app
        ├── App.vue                    ← Root component (<RouterView />)
        ├── bootstrap.js               ← Axios setup + CSRF
        ├── router/index.js            ← Vue Router + route guards (guestOnly, requiresAuth, role)
        ├── stores/
        │   └── auth.js                ← Pinia auth store
        ├── composables/
        │   └── useDocumentHelpers.js  ← Helper: statusConfig (dengan badgeStyle inline), formatDate, formatFileSize, daysUntilExpiry
        ├── components/
        │   ├── AppSidebar.vue         ← Sidebar vertikal kiri, full height, merah Pertamina, profil+logout+FAQ di bawah
        │   └── AppTopbar.vue          ← Topbar merah Pertamina, logo+nama, bell notif dropdown, nama user (tanpa dropdown)
        ├── layouts/
        │   ├── PegawaiLayout.vue      ← Layout role pegawai (flex row: sidebar kiri + main)
        │   └── AdminLayout.vue        ← Layout role admin (flex row: sidebar kiri + main)
        └── pages/
            ├── auth/
            │   ├── Login.vue          ← Layout 70/30, gambar kiri, form kanan
            │   ├── ForgotPassword.vue ← Konsisten dengan design system #006CB8
            │   └── ResetPassword.vue  ← Konsisten dengan design system #006CB8
            ├── pegawai/
            │   ├── Dashboard.vue      ← Header avatar + 6 stat card + quick actions + 2 kolom (notifikasi + sertifikat terakhir)
            │   ├── Dokumen.vue        ← List dokumen + filter status + export Excel
            │   ├── DokumenDetail.vue  ← Detail + status + riwayat file
            │   ├── DokumenForm.vue    ← Upload/edit dokumen (kategori dinamis, drag & drop)
            │   ├── Notifikasi.vue     ← List semua notifikasi + mark read
            │   └── Profile.vue        ← Edit HP + foto + ganti password
            └── admin/
                └── Dashboard.vue      ← Placeholder "Selamat datang" (belum dibangun penuh)
```

---

## 5. Database

**21 tabel** di database `renot`. Semua sudah ter-migrate.

### Tabel Utama

| Tabel | Keterangan |
|---|---|
| `users` | Admin & pegawai dalam satu tabel. Kolom `role`: `admin`/`pegawai`. Kolom tambahan: `employee_number`, `phone`, `avatar`, `department_id`, `is_active`, `created_by` |
| `departments` | Master data departemen/divisi |
| `certification_categories` | HSSE & Aviasi. `is_deletable=false` artinya tidak bisa dihapus |
| `certification_types` | Jenis sertifikat per kategori (GSI, SI, AT, RDS, PACE, dll). Bisa CRUD oleh admin |
| `documents` | Dokumen sertifikasi pegawai. Status: `pending_approval`, `ditolak`, `aktif`, `segera_expired`, `expired` |
| `document_versions` | Riwayat file saat dokumen diganti (hanya file, bukan data) |
| `notifications` | Notifikasi in-app & email. Channel: `in_app`, `email` |
| `reminder_schedules` | Jadwal H-X yang bisa dikonfigurasi admin (H-60, H-45, ..., H-1) |
| `reminder_logs` | Mencatat reminder yang sudah terkirim (unique constraint mencegah duplikat) |
| `email_templates` | Template email per tipe notifikasi, bisa diedit admin |
| `system_settings` | Key-value store (max file size, allowed types, dll) |
| `activity_logs` | Audit trail semua aktivitas CRUD dokumen |

### Logika Status Dokumen

```
Baru upload pegawai → pending_approval
Ditolak admin       → ditolak (dengan rejection_reason)
Approved admin      → dihitung dari expiry_date:
  expiry_date > H+60 → aktif
  expiry_date ≤ H+60 → segera_expired
  expiry_date < hari ini → expired

Dokumen yang diinput langsung oleh admin → langsung aktif (skip approval)
Dokumen yang diedit/file diganti pegawai → kembali ke pending_approval
```

---

## 6. Backend — Laravel

### Auth Flow

```
POST /api/login
  → cek email + password
  → cek is_active (jika false → 422 "Akun dinonaktifkan")
  → buat Sanctum token
  → return { token, user: { id, name, email, role, employee_number, phone, avatar (Storage::url), department } }

GET /api/me (auth:sanctum)
  → return user + unread_notifications_count

POST /api/forgot-password → kirim link reset ke email
POST /api/reset-password  → proses token + simpan password baru
POST /api/logout          → hapus current token
```

### Middleware

**`EnsureRole`** — `app/Http/Middleware/EnsureRole.php`
- Alias: `role`
- Dipakai di route: `Route::middleware('role:pegawai')`
- Cek `$request->user()->role === 'pegawai'`
- Return 403 jika tidak cocok

Didaftarkan di `bootstrap/app.php`:
```php
$middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
```

### Penting: Avatar URL

Avatar **wajib** dikembalikan sebagai `Storage::url($user->avatar)`, bukan path mentah.

```php
// BENAR
'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
// → /storage/avatars/xxx.jpg

// SALAH
'avatar' => $user->avatar,
// → avatars/xxx.jpg (tidak bisa diakses browser)
```

### Export Excel

Menggunakan `maatwebsite/excel` v4 (diinstall dengan `--ignore-platform-req`):

- `MyDocumentsExport` — untuk pegawai, filter by status
- `DocumentsExport` — untuk admin, filter by status/dept/user/category

Header berwarna: hijau untuk pegawai, biru untuk admin.

---

## 7. Frontend — Vue

### Cara Kerja Auth

`app.js` melakukan `auth.fetchUser()` **sebelum** router di-mount:

```js
auth.fetchUser().finally(async () => {
    const { default: router } = await import('./router');
    app.use(router);
    app.mount('#app');
});
```

Ini penting — jangan ubah urutan ini. Jika router di-mount dulu sebelum `fetchUser()` selesai, route guard akan salah membaca state auth.

### Route Guard

Di `router/index.js`:

```js
// guestOnly: true → redirect ke dashboard jika sudah login
// requiresAuth: true → redirect ke login jika belum login
// role: 'admin'|'pegawai' → redirect jika role tidak cocok
```

### Auth Store (`stores/auth.js`)

```js
// State
user            // Object user yang login
token           // Sanctum token (tersimpan di localStorage)
isAuthenticated // computed: !!token
isAdmin         // computed: user?.role === 'admin'
isPegawai       // computed: user?.role === 'pegawai'

// Actions
login(credentials)         // POST /api/login
logout()                   // POST /api/logout + clearAuth()
fetchUser()                // GET /api/me (untuk restore session)
updateProfile(formData)    // POST /api/pegawai/profile (multipart)
forgotPassword(email)      // POST /api/forgot-password
resetPassword(payload)     // POST /api/reset-password
```

### Layout Struktur

```
PegawaiLayout.vue / AdminLayout.vue
├── AppTopbar.vue      ← Full width, merah Pertamina #ED1B2F, logo+nama kiri, bell+nama user kanan
└── [flex row]
    ├── AppSidebar.vue ← Vertikal kiri, full height calc(100vh - 90px), sticky top:12px
    │   ├── nav items  ← Menu utama dengan icon container rounded-lg
    │   └── bottom     ← Bantuan (FAQ popup) + Profil + Keluar (modal konfirmasi)
    └── <main>         ← Konten halaman (RouterView)
```

### AppTopbar.vue

- Background: **Merah Pertamina `#ED1B2F`**, `border-radius: 16px`, shadow merah subtle
- Kiri: logo icon (container putih transparan) + "ReNot" bold + divider + "PERTAMINA" uppercase
- Kanan: bell notifikasi (dropdown 5 terbaru, mark read) + divider + avatar/initial + nama user
- **Tidak ada user dropdown** — profil dan logout dipindah ke sidebar
- Badge notif: white pulse animation saat ada unread
- Props: `user`, `unreadCount`, `notifRoute`
- Emits: `notif-read`

### AppSidebar.vue

- Background: **Merah Pertamina `#ED1B2F`** flat, `border-radius: 16px`, lebar 220px
- `position: sticky; top: 12px; height: calc(100vh - 90px)` — mengikuti scroll, full tinggi layar
- Setiap nav item punya **icon container** `w-7 h-7 rounded-lg` background `rgba(255,255,255,0.12)`
- Active item: background `rgba(255,255,255,0.20)`, font-weight 700
- **Di bagian bawah sidebar** (setelah divider):
  - **Bantuan** — toggle FAQ panel popup ke atas sidebar (3 FAQ card)
  - **Profil** — RouterLink ke halaman profil
  - **Keluar** — membuka modal konfirmasi logout
- **Modal logout**: overlay gelap, icon, teks konfirmasi, tombol Batal + Ya Keluar
- Props: `menuGroups`, `user`, `profileRoute`
- Emits: `logout`

### Dashboard Pegawai (`pages/pegawai/Dashboard.vue`)

Struktur halaman dashboard:

```
1. Header card   ← Avatar initial merah, nama, chips (role + no. pegawai + departemen), tanggal hari ini
2. Stat cards    ← 6 card grid, masing-masing punya icon container + angka + top accent bar per warna status
3. Quick actions ← Tombol "Upload Dokumen" (merah) + "Semua Dokumen" (card-elevated)
4. Bottom 2 col  ← Notifikasi Terbaru (2/3 lebar) | Sertifikat Terakhir (1/3 lebar)
```

**Sertifikat Terakhir** diambil dari `GET /api/pegawai/documents` (4 dokumen terbaru), bukan dari endpoint dashboard. Ini fetch terpisah di dalam `fetchDashboard()`.

### useDocumentHelpers.js

Composable reusable untuk semua halaman dokumen. Sejak v1.1, menambahkan `badgeStyle` (inline style string) dan `accentColor` per status:

```js
const { getStatusConfig, formatDate, formatFileSize, daysUntilExpiry } = useDocumentHelpers();

getStatusConfig('aktif')
// {
//   label: 'Aktif',
//   badge: 'bg-lime-100 text-lime-700',
//   badgeStyle: 'background:#F7FEE7; color:#5a6e0f;',
//   dot: 'bg-lime-500',
//   dotStyle: 'background:#ACC42A;',
//   accentColor: '#ACC42A'
// }

formatDate('2026-09-08')        // "8 September 2026"
formatFileSize(1048576)         // "1.0 MB"
daysUntilExpiry('2026-10-01')  // 23 (hari tersisa, negatif jika sudah lewat)
```

### Global CSS Class: `.card-elevated`

Didefinisikan di `resources/css/app.css`, dipakai di semua halaman untuk card yang tampil jelas di atas background:

```css
.card-elevated {
    background: #FFFFFF;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.03);
}
.card-elevated:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.10), 0 0 0 1px rgba(0,0,0,0.04);
}
```

---

## 8. Design System

**WAJIB baca `PHILOSOPHY.md` v1.1 sebelum membuat UI baru.**

### Token Warna Utama (v1.1 — Modern Bold)

```
Background halaman  #F2F2F0   abu sangat muda warm
Surface (card)      #FFFFFF
Border              #E2E8F0   (lebih gelap dari sebelumnya untuk card-elevated)

Pertamina Blue      #006CB8   CTA sekunder, link, focus ring
Pertamina Red       #ED1B2F   Topbar, sidebar, CTA utama, danger
Pertamina Lime      #ACC42A   Status Aktif, accent sukses (resmi digunakan sejak v1.1)

Text heading        #111827
Text body           #374151
Text muted          #6B7280
Text disabled       #9CA3AF
```

### Status Dokumen (diperbarui v1.1)

```
aktif            → Lime Pertamina  #ACC42A   (sebelumnya #16A34A hijau generic)
segera_expired   → Amber           #D97706
expired          → Merah Pertamina #ED1B2F
pending_approval → Abu             #6B7280
ditolak          → Pink            #DB2777
```

### Font

**Plus Jakarta Sans** — di-load di `welcome.blade.php` via Google Fonts.
Weights: 400, 500, 600, 700.

### Topbar & Sidebar

Keduanya menggunakan **Merah Pertamina `#ED1B2F`** sebagai background:

```css
/* Topbar */
background: #ED1B2F;
border-radius: 16px;
box-shadow: 0 2px 16px rgba(237,27,47,0.25), 0 1px 4px rgba(0,0,0,0.08);

/* Sidebar */
background: #ED1B2F;
border-radius: 16px;
width: 220px;
height: calc(100vh - 90px);
position: sticky;
top: 12px;
```

Semua teks di dalam topbar dan sidebar menggunakan putih (`#ffffff`) dengan opacity bervariasi untuk hierarki:
- Teks utama/aktif: `#ffffff` (100%)
- Teks default: `rgba(255,255,255,0.85)`
- Label group: `rgba(255,255,255,0.50)`
- Divider: `rgba(255,255,255,0.15)`

### Yang Dilarang (dari PHILOSOPHY.md v1.1)

- Gradient apapun di komponen UI (topbar/sidebar sudah flat, bukan gradient)
- `indigo-500` sebagai primary
- Card grid 3 kolom "icon + judul + 2 baris" generic
- Bounce/spring animation
- Font size < 12px
- Warna di luar token yang terdefinisi

---

## 9. Akun Seeder

Semua password: `password`

| Role | Nama | Email | No. Pegawai | Departemen |
|---|---|---|---|---|
| Admin | Administrator | admin@renot.app | — | — |
| Pegawai | Budi Santoso | budi@renot.app | EMP-001 | HSSE |
| Pegawai | Siti Rahayu | siti@renot.app | EMP-002 | Aviasi |
| Pegawai | Ahmad Fauzi | ahmad@renot.app | EMP-003 | Operasional |

**Budi Santoso** punya avatar yang sudah diupload (untuk testing foto profil di navbar).

---

## 10. API Endpoints

### Public (tanpa auth)

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/api/login` | Login, return token + user |
| POST | `/api/forgot-password` | Kirim link reset password |
| POST | `/api/reset-password` | Proses reset password |

### Protected (auth:sanctum)

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/me` | Data user yang login + unread_count |
| POST | `/api/logout` | Logout (hapus token) |
| GET | `/api/certification-categories` | Daftar kategori + jenis (untuk dropdown form) |

### Pegawai (auth:sanctum + role:pegawai)

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/pegawai/dashboard` | Stats dokumen + 5 notifikasi terbaru |
| GET | `/api/pegawai/profile` | Data profil |
| POST | `/api/pegawai/profile` | Update HP + foto (multipart/form-data) |
| PUT | `/api/pegawai/password` | Ganti password |
| GET | `/api/pegawai/documents` | List dokumen (`?status=aktif`) — dipakai juga oleh Dashboard untuk card Sertifikat Terakhir |
| POST | `/api/pegawai/documents` | Upload dokumen baru |
| GET | `/api/pegawai/documents/export` | Export Excel (`?status=aktif`) |
| GET | `/api/pegawai/documents/{id}` | Detail dokumen |
| POST | `/api/pegawai/documents/{id}` | Edit dokumen |
| DELETE | `/api/pegawai/documents/{id}` | Hapus dokumen |
| GET | `/api/pegawai/documents/{id}/download` | Download file |
| GET | `/api/pegawai/notifications` | List notifikasi + unread_count |
| POST | `/api/pegawai/notifications/read-all` | Tandai semua dibaca |
| POST | `/api/pegawai/notifications/{id}/read` | Tandai satu dibaca |

### Admin (belum dibangun)

Endpoint admin belum ada. Layout dan placeholder dashboard sudah ada, menu sidebar admin sudah terdefinisi (Dashboard, Dokumen, Approval, Pegawai, Departemen, Kategori Sertifikasi, Pengaturan Sistem, Audit Trail).

---

## 11. Status Implementasi

### Selesai ✅

| Fitur | Keterangan |
|---|---|
| Setup project | Laravel 13 + PostgreSQL + Vue 3 + Tailwind v4 |
| Database migrations | 14 tabel, semua ter-migrate |
| Models & Relationships | 12 model, semua relasi sudah didefinisikan |
| Seeders | User, departemen, kategori, jadwal reminder, dokumen, notifikasi |
| Auth | Login, logout, lupa password, reset password, redirect by role |
| Middleware role | EnsureRole untuk guard endpoint |
| UI Redesign — Modern Bold | Topbar + sidebar merah Pertamina, card-elevated, warna Lime #ACC42A untuk status aktif |
| Layout sistem | AppTopbar (merah, tanpa user dropdown) + AppSidebar (vertikal kiri, full height, profil+logout+FAQ di bawah) |
| Halaman Login | Layout 70/30, gambar kiri, form kanan, konsisten design system |
| ForgotPassword & ResetPassword | Diperbarui: warna Pertamina konsisten, layout card elevated |
| Dashboard Pegawai | Header avatar + 6 stat card (dengan icon per status) + quick actions + 2 kolom (notifikasi + sertifikat terakhir) |
| Dokumen Saya | List + filter status + upload + edit + hapus + download + export Excel |
| Detail Dokumen | Status badge, alasan tolak, riwayat file versi |
| Form Dokumen | Dropdown kategori dinamis, drag & drop file |
| Notifikasi Pegawai | List + mark read + mark all + link ke dokumen |
| Profil Pegawai | Edit HP + foto + ganti password |
| Export Excel | Per pegawai (filter status) + endpoint admin siap |
| Modal Logout | Konfirmasi "Yakin ingin keluar?" dengan dua tombol di sidebar |
| Global `.card-elevated` | CSS class reusable untuk semua card dengan border tegas + shadow |

### Belum Dibangun ❌

| Fitur | Prioritas |
|---|---|
| Dashboard Admin (stats + breakdown HSSE/Aviasi + filter dept) | Tinggi |
| Approval dokumen (approve/tolak + alasan) | Tinggi |
| CRUD Pegawai (tambah, edit, nonaktifkan, reset password) | Tinggi |
| CRUD Departemen | Sedang |
| CRUD Kategori Sertifikasi | Sedang |
| Kelola Admin (multi-admin) | Sedang |
| Pengaturan Sistem (jadwal reminder, template email, upload settings) | Sedang |
| Audit Trail UI | Sedang |
| Notifikasi Admin (bell + halaman) | Sedang |
| Scheduled Job reminder (cron harian kirim email + notif otomatis) | Tinggi |
| Kirim email reminder otomatis | Tinggi |
| Export Excel Admin (semua pegawai + filter) | Sedang |

---

## 12. Yang Perlu Dilanjutkan

### Prioritas Pertama — Dashboard & Approval Admin

1. **Buat controller admin** di `app/Http/Controllers/Admin/`
2. **Tambah route group** `middleware('role:admin')->prefix('admin')` di `api.php`
3. **Dashboard admin endpoint** — query stats total pegawai, dokumen per status, breakdown per kategori HSSE/Aviasi, filter per departemen
4. **Approval endpoint** — `POST /api/admin/documents/{id}/approve` dan `POST /api/admin/documents/{id}/reject`
5. **Update halaman** `pages/admin/Dashboard.vue` dengan stat cards (ikuti pola Dashboard pegawai)
6. **Buat halaman** `pages/admin/Approval.vue`

### Prioritas Kedua — CRUD Master Data

7. `pages/admin/Pegawai.vue` + `PegawaiForm.vue`
8. `pages/admin/Departemen.vue`
9. `pages/admin/Kategori.vue`

### Prioritas Ketiga — Sistem Otomatis

10. **Laravel Scheduled Command** untuk kirim reminder harian
    - Query dokumen approved yang `expiry_date` cocok dengan jadwal di `reminder_schedules`
    - Insert ke `notifications` (in_app + email)
    - Insert ke `reminder_logs` (prevent duplicate)
11. **Email template** — pakai `email_templates` dari DB, parse placeholder `{Nama Pegawai}` dll

### Catatan Penting untuk Developer Berikutnya

- **Saat membuat UI baru**, selalu baca `PHILOSOPHY.md` v1.1 terlebih dahulu. Topbar dan sidebar sudah merah — komponen baru harus konsisten dengan palet ini.
- **Sidebar admin** di `AdminLayout.vue` sudah punya 8 menu siap (Dashboard, Dokumen, Approval, Pegawai, Departemen, Kategori, Pengaturan, Audit Trail) — tinggal buat halaman dan route-nya.
- **`DocumentsExport.php`** sudah siap untuk export admin — tinggal buat endpoint dan tombol di UI.
- **`EnsureRole` middleware** sudah ada — untuk admin cukup pakai `middleware('role:admin')`.
- **`Storage::url()`** wajib digunakan setiap kali return URL avatar/file dari backend.
- **`.card-elevated`** adalah class global yang harus dipakai untuk semua card konten. Jangan hardcode `border` dan `box-shadow` per komponen.
- **Warna status Aktif** sekarang `#ACC42A` (Lime Pertamina), bukan `#16A34A`. Ini sudah diupdate di `useDocumentHelpers.js` dan terdokumentasi di `PHILOSOPHY.md`.
- **Dashboard menggunakan 2 API call** saat load: `GET /api/pegawai/dashboard` untuk stats + notifikasi, dan `GET /api/pegawai/documents` untuk card sertifikat terakhir. Ini by design — endpoint dashboard tidak mengembalikan list dokumen lengkap.

---

*Dokumen ini diperbarui pada 8 September 2026 setelah UI Redesign Modern Bold. Update dokumen ini setiap kali ada perubahan signifikan pada arsitektur, UI, atau fitur.*
