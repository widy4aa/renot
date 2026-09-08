# DOKUMENTASI TEKNIS — ReNot
## Reminder & Notification Sertifikasi Pegawai

**Versi dokumen:** 1.0
**Terakhir diperbarui:** 8 September 2026
**Status project:** Sprint 2 — Role Pegawai selesai, Role Admin belum dibangun

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
- `PHILOSOPHY.md` — Design system dan aturan UI (WAJIB dibaca sebelum membuat UI baru)

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
    ├── css/app.css                    ← Tailwind v4 + design tokens
    ├── views/welcome.blade.php        ← SPA entry point + load Plus Jakarta Sans
    └── js/
        ├── app.js                     ← Entry point — init Pinia → fetchUser → mount router → mount app
        ├── App.vue                    ← Root component (<RouterView />)
        ├── bootstrap.js               ← Axios setup + CSRF
        ├── router/index.js            ← Vue Router + route guards (guestOnly, requiresAuth, role)
        ├── stores/
        │   └── auth.js                ← Pinia auth store
        ├── composables/
        │   └── useDocumentHelpers.js  ← Helper: statusConfig, formatDate, formatFileSize, daysUntilExpiry
        ├── components/
        │   ├── AppSidebar.vue         ← Sidebar reusable (fluid collapse, icon-only, localStorage)
        │   └── AppTopbar.vue          ← Topbar reusable (logo, bell notif dropdown, user dropdown)
        ├── layouts/
        │   ├── PegawaiLayout.vue      ← Layout role pegawai
        │   └── AdminLayout.vue        ← Layout role admin
        └── pages/
            ├── auth/
            │   ├── Login.vue          ← Layout 70/30, gambar kiri, form kanan, FAQ help button
            │   ├── ForgotPassword.vue
            │   └── ResetPassword.vue
            ├── pegawai/
            │   ├── Dashboard.vue      ← 6 stat card + 5 notifikasi terbaru
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
PegawaiLayout.vue
├── AppTopbar.vue         ← Logo kiri, bell notif dropdown, user dropdown
│   (full width, floating card, rounded-2xl, shadow)
└── [flex row]
    ├── [placeholder div] ← Lebar sama dengan sidebar (untuk spacing)
    │   └── AppSidebar.vue ← Fixed, top:50%, translateY(-50%) → center vertical
    └── <main>            ← Konten halaman
        └── <RouterView />
```

**Sidebar fixed center:**
```css
position: fixed;
top: 50%;
transform: translateY(-50%);
```
Placeholder div di sebelahnya punya `width` yang sama dengan sidebar (reactive via `@width-change` emit).

### AppSidebar.vue

- **Collapsed state** tersimpan di `localStorage` key `renot_sidebar_collapsed`
- Saat toggle, emit `@width-change` dengan lebar baru (200px atau 56px)
- Tombol toggle `←/→` di bagian bawah sidebar
- Tooltip via `title` attribute saat collapsed

### AppTopbar.vue

- Logo + nama "ReNot" di kiri
- Bell notifikasi: fetch 5 terbaru dari `/api/pegawai/notifications`, bisa mark read, lihat semua
- User dropdown: info user + role badge + link profil + logout

### useDocumentHelpers.js

Composable reusable untuk semua halaman dokumen:

```js
const { getStatusConfig, formatDate, formatFileSize, daysUntilExpiry } = useDocumentHelpers();

getStatusConfig('aktif')        // { label, badge, dot } — warna per status
formatDate('2026-09-08')        // "8 September 2026"
formatFileSize(1048576)         // "1.0 MB"
daysUntilExpiry('2026-10-01')  // 23 (hari tersisa, negatif jika sudah lewat)
```

---

## 8. Design System

**WAJIB baca `PHILOSOPHY.md` sebelum membuat UI baru.**

### Token Warna Utama

```
Background halaman  #F0F0EE   abu sangat muda (bukan putih)
Surface (card)      #FFFFFF
Border              #E5E7EB
Primary (CTA, link) #006CB8   Biru Pertamina
Danger (expired)    #ED1B2F   Merah Pertamina
Text heading        #111827
Text body           #374151
Text muted          #9CA3AF
```

### Status Dokumen

```
aktif            → hijau   #16A34A
segera_expired   → amber   #D97706
expired          → merah   #ED1B2F
pending_approval → abu     #6B7280
ditolak          → pink    #DB2777
```

### Font

**Plus Jakarta Sans** — di-load di `welcome.blade.php` via Google Fonts.
Weights: 400, 500, 600, 700.

### Komponen Floating

Sidebar dan topbar menggunakan:
```css
border-radius: 16px;
box-shadow: 0 1px 4px rgba(0,0,0,0.08);
```

Background halaman `#F0F0EE` agar kontras dengan card putih.

### Yang Dilarang (dari PHILOSOPHY.md)

- Gradient apapun
- Glassmorphism / backdrop-blur
- `indigo-500` sebagai primary
- Card grid 3 kolom "icon + judul + 2 baris"
- Bounce/spring animation
- Font size < 12px

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
| GET | `/api/pegawai/documents` | List dokumen (`?status=aktif`) |
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

Endpoint admin belum ada. Hanya layout dan placeholder dashboard yang sudah ada.

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
| Layout sistem | AppTopbar + AppSidebar (floating, fluid collapse) |
| Halaman Login | Layout 70/30, gambar kiri, form kanan, FAQ button |
| Dashboard Pegawai | 6 stat card + 5 notif terbaru, link ke detail |
| Dokumen Saya | List + filter status + upload + edit + hapus + download + export Excel |
| Detail Dokumen | Status badge, alasan tolak, riwayat file versi |
| Form Dokumen | Dropdown kategori dinamis, drag & drop file |
| Notifikasi Pegawai | List + mark read + mark all + link ke dokumen |
| Profil Pegawai | Edit HP + foto + ganti password |
| Export Excel | Per pegawai (filter status) + endpoint admin siap |

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
5. **Update halaman** `pages/admin/Dashboard.vue` dengan stat cards
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

### Catatan Penting

- **Saat membuat UI baru**, selalu baca `PHILOSOPHY.md` terlebih dahulu. Jangan pakai warna di luar token yang sudah didefinisikan.
- **Sidebar admin** di `AdminLayout.vue` sudah punya 6 menu siap (Dashboard, Dokumen, Approval, Pegawai, Departemen, Kategori, Pengaturan, Audit Trail) — tinggal buat halaman dan route-nya.
- **`DocumentsExport.php`** sudah siap untuk export admin — tinggal buat endpoint dan tombol di UI.
- **`EnsureRole` middleware** sudah ada — untuk admin cukup pakai `middleware('role:admin')`.
- **Jangan lupa** `Storage::url()` setiap kali return URL avatar/file dari backend.

---

*Dokumen ini dibuat otomatis pada 8 September 2026. Update dokumen ini setiap kali ada perubahan signifikan pada arsitektur atau fitur.*
