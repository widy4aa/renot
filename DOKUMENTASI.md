# DOKUMENTASI TEKNIS — ReNot
## Reminder & Notification Sertifikasi Pegawai

**Versi dokumen:** 3.1
**Terakhir diperbarui:** 8 Oktober 2026
**Status project:** Sprint 6 — Security Hardening, Rate Limiting, Pentest Fixes

---

## Daftar Isi

1. [Overview Project](#1-overview-project)
2. [Tech Stack](#2-tech-stack)
3. [Cara Menjalankan](#3-cara-menjalankan)
4. [Deployment — Docker / Podman](#4-deployment--docker--podman)
5. [Struktur Project](#5-struktur-project)
6. [Database](#6-database)
7. [Backend — Laravel](#7-backend--laravel)
8. [Frontend — Vue](#8-frontend--vue)
9. [Design System](#9-design-system)
10. [Akun Seeder](#10-akun-seeder)
11. [API Endpoints](#11-api-endpoints)
12. [Status Implementasi](#12-status-implementasi)
13. [Catatan untuk Developer](#13-catatan-untuk-developer)

---

## 1. Overview Project

ReNot adalah aplikasi internal perusahaan (konteks Pertamina) untuk memantau masa berlaku dokumen/sertifikasi pegawai (HSSE & Aviasi) dan mengirim reminder otomatis sebelum dokumen kadaluarsa.

**Dua role:**
- **Admin** — mengelola semua pegawai, dokumen, approval, approval pendaftaran, kirim email manual, konfigurasi sistem
- **Pegawai** — mendaftar mandiri, mengelola dokumen milik sendiri, menerima reminder

**Dokumen referensi:**
- `SRS-ReNot.md` — Software Requirements Specification lengkap
- `database.dbml` — Database diagram (bisa dibuka di dbdiagram.io)
- `PHILOSOPHY.md` — Design system dan aturan UI v2.0 (WAJIB dibaca sebelum membuat UI baru)

---

## 2. Tech Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | Laravel | 13.x |
| PHP | PHP | 8.4 |
| Database | PostgreSQL | 16 (via Docker/Podman container) |
| Auth | Laravel Sanctum | 4.x |
| Frontend | Vue 3 (Composition API) | Latest |
| Build | Vite | 8.x |
| CSS | Tailwind CSS | v4 (`@tailwindcss/vite`) |
| State | Pinia | Latest |
| Router | Vue Router | Latest |
| HTTP | Axios | Latest |
| Excel Export | maatwebsite/excel | 4.x |
| Font | Plus Jakarta Sans | Google Fonts |
| Container | Docker / Podman | — |
| Web Server | Nginx + PHP-FPM | Alpine |
| Process Manager | Supervisord | — |

---

## 3. Cara Menjalankan

### Mode Development (lokal)

```bash
cd laravel-vue-template

# Install dependencies
composer install
npm install

# Copy env
cp .env.example .env
php artisan key:generate

# Jalankan database PostgreSQL via Podman
podman start postgres_db

# Migration + seed
php artisan migrate --seed

# Storage symlink
php artisan storage:link

# Terminal 1 — Backend
php artisan serve

# Terminal 2 — Frontend (HMR)
npm run dev
```

Buka: `http://localhost:8000`

### Kredensial Database (dev)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=renot
DB_USERNAME=root
DB_PASSWORD=root
```

### Menjalankan Scheduled Reminder Manual

```bash
php artisan reminders:send
```

---

## 4. Deployment — Docker / Podman

### Struktur file Docker

```
laravel-vue-template/
├── Dockerfile                  ← Multi-stage build (composer → node → php:8.4-fpm-alpine)
├── docker-compose.yml          ← 2 service: app + db
├── .env.docker                 ← Env production untuk Docker (tidak di-commit, lihat .env.docker.example)
├── .env.docker.example         ← Template kosong — di-commit sebagai referensi tim
├── .dockerignore
└── docker/
    ├── nginx/default.conf      ← Nginx port 8000, SPA catch-all, storage alias, upload max 5M
    ├── php/php.ini             ← upload 5M, post 8M, memory 256M, opcache
    ├── supervisord.conf        ← Manage php-fpm + nginx dalam 1 container
    └── start.sh                ← Entrypoint: wait DB → migrate → cache → start
```

### Setup .env Docker (wajib sebelum pertama kali deploy)

```bash
# Copy template dan isi nilai yang sesuai
cp .env.docker.example .env.docker

# Edit .env.docker — minimal isi:
#   APP_KEY=base64:...  (generate: php artisan key:generate --show)
#   DB_PASSWORD=...
#   MAIL_USERNAME=... MAIL_PASSWORD=...
```

### Build & Run

```bash
cd laravel-vue-template

# Build image dan jalankan semua service
podman-compose up -d --build

# Lihat log
podman-compose logs -f app
podman-compose logs -f db

# Seed database (pertama kali setelah container jalan)
podman-compose exec app php artisan db:seed

# Reset database
podman-compose exec app php artisan migrate:fresh --seed

# Stop semua container
podman-compose down
```

> **Gunakan `podman-compose`** (bukan `podman compose`) — tool yang tersedia di sistem ini.

### Akses setelah deploy

| Service | URL |
|---|---|
| Aplikasi ReNot | `http://127.0.0.1:8000` |
| PostgreSQL (host) | `127.0.0.1:5433` ← port 5433 (bukan 5432, untuk hindari konflik dengan PostgreSQL lokal) |

### Catatan Podman

- Semua image pakai prefix `docker.io/` (wajib jika `registries.conf` tidak punya unqualified-search)
- `podman-compose` kompatibel dengan `docker-compose.yml` v3
- Storage avatar dan file sertifikat di-mount ke named volume — tidak hilang saat container di-recreate
- **Port PostgreSQL di host adalah `5433`** (container internal tetap 5432). App service tetap pakai `DB_HOST=db` + `DB_PORT=5432` — port mapping hanya untuk akses dari luar container.
- Env diambil dari `.env.docker` via `env_file:` — jangan hardcode credentials di `docker-compose.yml`

### Scheduler di Production

Untuk menjalankan `reminders:send` secara otomatis setiap hari, tambahkan cron di server:

```bash
# Edit crontab
crontab -e

# Tambahkan baris ini (jalankan setiap hari pukul 07:00 WIB)
0 0 * * * cd /path/to/laravel-vue-template && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Struktur Project

```
laravel-vue-template/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── SendReminders.php         ← Command reminder harian
│   ├── Exports/
│   │   ├── AdminDocumentsExport.php      ← Export Excel admin (ikut filter)
│   │   └── MyDocumentsExport.php         ← Export Excel pegawai
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── ApprovalController.php      ← List dokumen pending approval
│   │   │   │   ├── AuditController.php          ← Log aktivitas (server-side pagination)
│   │   │   │   ├── DashboardController.php      ← Stats global + pending + expiring + pendaftaran
│   │   │   │   ├── DepartemenController.php     ← CRUD departemen
│   │   │   │   ├── DocumentController.php       ← CRUD dokumen semua pegawai + approve/reject
│   │   │   │   ├── KategoriController.php       ← CRUD jenis sertifikasi per kategori
│   │   │   │   ├── ManajemenAdminController.php ← CRUD akun admin
│   │   │   │   ├── ManualNotificationController.php ← Kirim email manual ke pegawai
│   │   │   │   ├── NotificationController.php   ← Notifikasi admin
│   │   │   │   ├── PegawaiController.php        ← CRUD pegawai + toggle aktif + reset password
│   │   │   │   ├── PengaturanController.php     ← Jadwal reminder + template email + SMTP
│   │   │   │   └── RegistrationController.php   ← Approve/reject pendaftaran pegawai
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php           ← Login, register, forgot/reset password
│   │   │   ├── Pegawai/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── DocumentController.php
│   │   │   │   ├── NotificationController.php
│   │   │   │   └── ProfileController.php
│   │   │   ├── CertificationCategoryController.php
│   │   │   └── DepartmentController.php         ← List departemen publik (untuk form register)
│   │   └── Middleware/
│   │       └── EnsureRole.php
│   ├── Mail/
│   │   ├── ReminderMail.php             ← Email reminder/approve/reject dokumen
│   │   ├── RegistrationMail.php         ← Email approve/reject pendaftaran
│   │   └── ManualNotificationMail.php   ← Email manual dari admin
│   └── Models/ (13 model)
├── database/
│   ├── migrations/                    ← 17 migration file
│   └── seeders/                       ← 8 seeder (berurut: master → config → user → transaksional)
│       ├── DatabaseSeeder.php         ← Orchestrator — urutan seed yang benar
│       ├── DepartmentSeeder.php       ← 1. Master: departemen
│       ├── CertificationCategorySeeder.php ← 2. Master: kategori + jenis sertifikasi
│       ├── ReminderScheduleSeeder.php ← 3. Config: jadwal reminder H-60 s/d H-1
│       ├── EmailTemplateSeeder.php    ← 4. Config: 7 template email
│       ├── UserSeeder.php             ← 5. User: admin + 3 pegawai (dengan avatar)
│       ├── DocumentSeeder.php         ← 6. Dokumen: 14 dokumen + dummy file dari /sertif/
│       └── NotificationSeeder.php     ← 7. Notifikasi: 13 notifikasi dummy
├── docker/                            ← Konfigurasi Docker/Podman
├── routes/
│   ├── api.php                        ← Semua API endpoint (~100 route)
│   ├── console.php                    ← Scheduled command (reminders:send daily)
│   └── web.php                        ← Catch-all → SPA
└── resources/
    ├── css/app.css                    ← Tailwind v4 + design tokens
    └── js/
        ├── app.js
        ├── router/index.js
        ├── stores/auth.js             ← Pinia store: login, register, logout, fetchUser
        ├── composables/
        │   ├── useDocumentHelpers.js  ← statusConfig, formatDate, formatFileSize, daysUntilExpiry
        │   └── useFaq.js             ← FAQ dinamis per halaman (inject/provide)
        ├── components/
        │   ├── AppSidebar.vue        ← Sidebar merah, collapsible, FAQ modal popup
        │   └── AppTopbar.vue         ← Jam live, bell notifikasi, dropdown
        ├── layouts/
        │   ├── PegawaiLayout.vue     ← provide setPageFaqs, fetch unread count
        │   └── AdminLayout.vue       ← provide setPageFaqs, fetch unread + pending registrations
        └── pages/
            ├── auth/
            │   ├── Login.vue          ← Login + link ke Register
            │   ├── Register.vue       ← Pendaftaran mandiri pegawai
            │   ├── ForgotPassword.vue
            │   └── ResetPassword.vue
            ├── pegawai/
            │   ├── Dashboard.vue
            │   ├── Dokumen.vue
            │   ├── DokumenDetail.vue
            │   ├── DokumenForm.vue
            │   ├── Notifikasi.vue
            │   └── Profile.vue
            └── admin/
                ├── Dashboard.vue           ← Stats + pending docs + expiring + pendaftaran baru
                ├── Approval.vue            ← List pending, modal approve/tolak
                ├── Pendaftaran.vue         ← List pendaftaran baru, approve/tolak
                ├── Dokumen.vue             ← Semua dokumen + filter departemen
                ├── DokumenDetail.vue       ← Detail + tombol approve/tolak/edit
                ├── DokumenForm.vue         ← Tambah/edit + pilih pegawai
                ├── Notifikasi.vue
                ├── Profile.vue
                ├── Pegawai.vue             ← List pegawai + toggle aktif + reset pwd + kirim email
                ├── PegawaiForm.vue         ← Form tambah/edit pegawai
                ├── Departemen.vue          ← CRUD departemen via modal
                ├── Kategori.vue            ← CRUD jenis sertifikasi per kategori
                ├── Audit.vue               ← Log aktivitas + pagination server-side
                ├── ManajemenAdmin.vue      ← CRUD akun admin
                ├── Pengaturan.vue          ← Jadwal reminder + template email + SMTP
                └── AdminPlaceholder.vue   ← Komponen "Segera Hadir" reusable
```

---

## 6. Database

**21 tabel** di database `renot`. Semua sudah ter-migrate.

### Tabel Utama

| Tabel | Keterangan |
|---|---|
| `users` | Admin & pegawai dalam satu tabel. Kolom `role`: `admin`/`pegawai`. Kolom `registration_status`: `approved`/`pending`/`rejected`. |
| `departments` | Master data departemen/divisi |
| `certification_categories` | HSSE & Aviasi. `is_deletable=false` |
| `certification_types` | Jenis sertifikat per kategori (GSI, SI, AT, RDS, PACE, dll) |
| `documents` | Dokumen sertifikasi. Status: `pending_approval`, `ditolak`, `aktif`, `segera_expired`, `expired` |
| `document_versions` | Riwayat file saat dokumen diganti |
| `notifications` | Notifikasi in-app & email |
| `reminder_schedules` | Jadwal H-X (H-60 s/d H-1), bisa ditambah/hapus via UI |
| `reminder_logs` | Prevent duplicate reminder — unique index `(document_id, reminder_schedule_id)` |
| `email_templates` | Template email per tipe notifikasi dengan placeholder |
| `system_settings` | Key-value store pengaturan sistem |
| `activity_logs` | Audit trail semua aktivitas dengan old_values/new_values JSONB |
| `mail_configs` | Profile SMTP yang dikelola admin. Password terenkripsi. Satu aktif pada satu waktu. |

### Kolom Registrasi di Tabel `users`

```
registration_status          VARCHAR(20) default 'approved'
registration_rejected_reason TEXT nullable
registered_at                TIMESTAMP nullable
```

- User yang dibuat admin → `registration_status = 'approved'` (default)
- User yang self-register → `registration_status = 'pending'`, `is_active = false`
- Admin approve → `registration_status = 'approved'`, `is_active = true`
- Admin reject → `registration_status = 'rejected'`, alasan disimpan
- Pegawai yang ditolak bisa mendaftar ulang (akun rejected dihapus otomatis)

### Logika Status Dokumen

```
Baru upload pegawai → pending_approval
Ditolak admin       → ditolak (dengan rejection_reason)
Approved admin      → dihitung dari expiry_date:
  expiry_date > H+60 → aktif
  expiry_date ≤ H+60 → segera_expired
  expiry_date < hari ini → expired

Dokumen diinput langsung admin → langsung aktif (skip approval)
Dokumen diedit/file diganti pegawai → kembali ke pending_approval
```

### Scheduled Reminder Logic

```
php artisan reminders:send (dijalankan daily via schedule)
  ├── Update dokumen aktif/segera_expired yang expiry_date < today → expired
  │   └── Kirim notifikasi in_app "Dokumen Kadaluarsa" ke pegawai
  └── Untuk setiap reminder_schedule aktif (H-X):
      ├── Cari dokumen dengan expiry_date = today + X hari
      ├── Skip jika sudah ada di reminder_logs (deduplication)
      ├── Kirim notifikasi in_app ke pegawai
      └── Catat ke reminder_logs
```

---

## 7. Backend — Laravel

### Auth Flow

```
POST /api/register → pendaftaran baru (status pending, tidak bisa login)
POST /api/login    → cek registration_status + is_active → token + user
GET  /api/me       → user + unread_notifications_count
POST /api/logout   → hapus token
```

#### Flow Registrasi Mandiri

```
Pegawai buka /register
  → Isi form: nama, email, NIP, HP, departemen, password
  → Submit → akun dibuat (is_active=false, registration_status=pending)
  → Semua admin terima notifikasi in-app "Pendaftaran Baru"
  → Admin buka halaman Pendaftaran → approve/reject
    → Approve: is_active=true, registration_status=approved, email dikirim
    → Reject: registration_rejected_reason diisi, email dikirim
  → Pegawai bisa login setelah approved
  → Jika rejected, pegawai bisa daftar ulang (akun lama dihapus otomatis)
```

#### Login — Pengecekan Registration Status

```
1. Cek email + password
2. Jika registration_status = 'pending' → 422 "Menunggu persetujuan admin"
3. Jika registration_status = 'rejected' → 422 "Pendaftaran ditolak"
4. Jika is_active = false → 422 "Akun dinonaktifkan"
5. Sukses → return token + user
```

### Middleware

**`EnsureRole`** — alias `role`, cek `$request->user()->role`
- `middleware('role:pegawai')` untuk route pegawai
- `middleware('role:admin')` untuk route admin

Untuk API, unauthenticated request return **401 JSON** (bukan redirect ke login).

### Admin Controllers

| Controller | Fungsi Utama |
|---|---|
| `DashboardController` | Stats global: total pegawai, dokumen, pending, expired, **pendaftaran baru**. List 5 pending + 5 expiring soon + **5 pendaftaran terbaru**. |
| `ApprovalController` | List dokumen `pending_approval` dengan filter kategori + departemen |
| `RegistrationController` | **List/approve/reject pendaftaran pegawai baru** |
| `DocumentController` | CRUD semua dokumen + approve + reject + download + export Excel (ikut filter). **Max upload 2 MB.** |
| `PegawaiController` | CRUD pegawai + toggleActive + resetPassword |
| `ManualNotificationController` | **Kirim email manual ke pegawai tertentu (subject + body custom)** |
| `DepartemenController` | CRUD departemen. Hapus dicegah jika ada pegawai aktif. |
| `KategoriController` | CRUD jenis sertifikasi per kategori. Hapus dicegah jika dipakai dokumen. |
| `AuditController` | List activity_logs dengan server-side pagination + filter |
| `ManajemenAdminController` | CRUD akun admin. Tidak bisa hapus diri sendiri. |
| `NotificationController` | Notifikasi in-app admin + markAsRead + markAllAsRead |
| `PengaturanController` | CRUD reminder_schedules + toggle aktif. CRUD email_templates. CRUD mail config SMTP (profile, activate, test). |

### Approve vs Reject Logic

```php
// Approve: status dihitung dari expiry_date
$document->status      = $document->computeStatus(); // aktif/segera_expired/expired
$document->approved_by = $admin->id;
$document->approved_at = now();
// → Kirim notifikasi in_app ke pegawai
// → Catat ActivityLog

// Reject: alasan wajib diisi
$document->status           = Document::STATUS_DITOLAK;
$document->rejection_reason = $request->rejection_reason;
// → Kirim notifikasi in_app ke pegawai
// → Catat ActivityLog
```

### DocumentController Admin — Perbedaan vs Pegawai

- Admin bisa akses **semua** dokumen, pegawai hanya milik sendiri
- Admin input dokumen → **langsung aktif** (auto-approve), pegawai → pending_approval
- Admin edit dokumen yang sudah approved → status **dihitung ulang** dari expiry_date baru
- Pegawai edit → selalu kembali ke pending_approval

### Preview File

- **Image** (jpg/png/webp) — akses via `/storage/{file_path}` langsung (storage public)
- **PDF** — fetch via axios dengan Bearer token → buat Blob URL → render di `<embed>`
- **Download** — fetch via axios dengan Bearer token → trigger download via Blob

---

## 8. Frontend — Vue

### FAQ Dinamis Per Halaman

Setiap halaman set FAQ kontekstual yang muncul sebagai modal popup dari sidebar:

```js
import { useFaq } from '@/composables/useFaq.js';

// Cara penggunaan: pass array langsung ke useFaq()
// FAQ otomatis di-set saat onMounted dan di-reset saat unmount
useFaq([
    { q: 'Pertanyaan?', a: 'Jawaban.', icon: 'M...' },
]);
```

Alur: `PegawaiLayout`/`AdminLayout` provide `setPageFaqs` → halaman inject via `useFaq()` → `AppSidebar` tampilkan sebagai modal popup dengan backdrop blur.

### Sidebar

- Default **collapsed** (64px). Toggle expand/collapse via tombol chevron.
- Width expanded: 220px. Transition: 200ms ease.
- Tombol Bantuan → FAQ modal popup (bukan inline, bukan absolute)
- Badge unread count pada menu Notifikasi (admin & pegawai)

### Topbar

- Background `#ED1B2F`, border-radius 16px
- Bell notifikasi: dropdown 5 terbaru, mark-as-read, ping animation saat ada unread
- Kanan: jam live `HH:MM` update setiap detik

### Layout Admin

Sama persis dengan pegawai. `AdminLayout.vue` provide `setPageFaqs`, fetch unread count dari `/api/admin/notifications`.

### Dashboard Admin

```
Header card: avatar biru + nama + badge "Admin HR" + tanggal

7 Stat card:
  Total Pegawai (klik → admin.pegawai) | Total Dokumen | Pending | Expired | Segera Expired | Aktif | Pendaftaran Baru

4 panel bawah (grid-cols-4):
  Pendaftaran Menunggu (5 terbaru) | Menunggu Approval (5 terbaru) | Segera Kadaluarsa (5 terbaru) | Aktivitas Terkini (5 terbaru)
```

### Halaman Dokumen Admin

- Filter: search nama pegawai/sertifikat, status, kategori, jenis, **departemen**, range expiry
- Semua filter **client-side** dari satu fetch
- Tombol Approve langsung dari list (hanya muncul jika pending)
- Export Excel ikut filter aktif

### Halaman Approval

- Hanya menampilkan dokumen `pending_approval`
- Filter: search, kategori, departemen
- Setelah approve/tolak → baris dihapus dari list secara reactive

### Halaman Audit Trail

- **Server-side pagination** (satu-satunya halaman yang menggunakan ini)
- 20 entri per halaman
- Expand row untuk lihat `old_values` vs `new_values`

### Halaman Pegawai

- Badge jumlah dokumen, expired, dan pending per baris pegawai
- Toggle aktif/nonaktif langsung dari list
- Reset password via modal
- **Kirim email manual** via modal (subject + body, dikirim ke pegawai yang dipilih)
- Form tambah/edit: nama, email, NIP, HP, departemen, password (hanya saat tambah), toggle status

### Composables

| File | Fungsi |
|---|---|
| `useDocumentHelpers.js` | `getStatusConfig`, `formatDate`, `formatFileSize`, `daysUntilExpiry` |
| `useFaq.js` | `useFaq([...items])` — set FAQ kontekstual sidebar per halaman, auto-reset on unmount |

### Global CSS Class: `.card-elevated`

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

## 9. Design System

**WAJIB baca `PHILOSOPHY.md` v2.0 sebelum membuat UI baru.**

Philosophy v2.0 mencerminkan UI yang sudah dibangun dan disetujui client. Semua halaman baru harus konsisten.

### Token Warna Utama

```
Background halaman  #F2F2F0   (warm off-white)
Surface (card)      #FFFFFF
Border              #E2E8F0   (1.5px)

Pertamina Red       #ED1B2F   CTA utama, topbar, sidebar, focus border input
Pertamina Blue      #006CB8   CTA sekunder, ghost button, link, badge info
Pertamina Lime      #ACC42A   Status Aktif

Text heading        #111827
Text body           #374151
Text muted          #6B7280
Text disabled       #9CA3AF   (juga dipakai untuk label input uppercase)
```

### Hierarki Warna CTA

- **Merah `#ED1B2F`** = CTA utama (Upload, Simpan, Hapus, tombol utama semua halaman)
- **Biru `#006CB8`** = CTA sekunder / ghost (link "Lihat Detail", button secondary)
- Jangan balik hierarki ini.

### Status Dokumen

```
aktif            → #ACC42A  Lime Pertamina
segera_expired   → #D97706  Amber
expired          → #ED1B2F  Merah Pertamina
pending_approval → #6B7280  Abu
ditolak          → #DB2777  Pink
```

### Input Fields

```
background    : #F9FAFB
border        : 1.5px solid #E2E8F0
border-radius : rounded-xl (12px)
padding       : px-3 py-2.5 atau px-4 py-2.5
font          : text-sm font-medium color #111827
focus         : border-color #ED1B2F (via @focus/@blur JS handler)
label         : text-xs font-bold uppercase tracking-wide color #9CA3AF
```

### Border Radius

```
16px (rounded-2xl) → topbar, sidebar, modal, avatar, card header utama
12px (rounded-xl)  → .card-elevated, input, tombol, dropdown panel
 8px (rounded-lg)  → icon container w-7 h-7 di nav
9999px (rounded-full) → badge pill, dot indikator
```

### Yang Dilarang

- Gradient apapun
- `indigo-500` sebagai primary
- Bounce/spring animation
- Font < 10px
- Warna di luar token yang terdefinisi
- Sidebar admin berbeda warna dari sidebar pegawai

---

## 10. Akun Seeder

Semua password: `password`

| Role | Nama | Email | No. Pegawai | Departemen | Avatar |
|---|---|---|---|---|---|
| Admin | Administrator | admin@renot.app | — | — | — |
| Pegawai | Budi Santoso | budi@renot.app | EMP-001 | HSSE | Ada (dari sertif/) |
| Pegawai | Siti Rahayu | siti@renot.app | EMP-002 | Aviasi | Ada (dari sertif/) |
| Pegawai | Ahmad Fauzi | ahmad@renot.app | EMP-003 | Operasional | Ada (dari sertif/) |

### Urutan Seeder (`DatabaseSeeder.php`)

```
1. Master Data (independen)
   ├── DepartmentSeeder            → HSSE, Aviasi, Operasional, Teknik, Administrasi
   └── CertificationCategorySeeder → HSSE (6 tipe) + Aviasi (3 tipe)

2. Konfigurasi Sistem (independen)
   ├── ReminderScheduleSeeder      → 12 jadwal (H-60 s/d H-1)
   └── EmailTemplateSeeder         → 7 template email (5 dokumen + 2 registrasi)

3. User (butuh departemen)
   └── UserSeeder                  → 1 admin + 3 pegawai (dengan avatar dari sertif/)

4. Data Transaksional (butuh user + cert types)
   ├── DocumentSeeder              → 14 dokumen (file di-copy dari sertif/ ke storage)
   └── NotificationSeeder          → 13 notifikasi dummy
```

**Semua dokumen** sudah punya file dummy (gambar sertifikat) dari folder `/sertif/` — tombol Download dan Preview aktif.

**Avatar pegawai** diambil dari folder `/sertif/` dan di-copy ke `storage/app/public/avatars/` saat seeding.

**Seeder aman dijalankan ulang** — menggunakan `insertOrIgnore` dan `updateOrCreate` di mana perlu.

---

## 11. API Endpoints

### Public (tanpa auth)

_Semua endpoint public ini dibatasi oleh rate limiting: 5 request per 1 menit per IP._

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/api/login` | Login, return token + user |
| POST | `/api/register` | Pendaftaran mandiri pegawai (status pending) |
| POST | `/api/forgot-password` | Kirim link reset password |
| POST | `/api/reset-password` | Proses reset password |
| GET | `/api/departments` | Daftar departemen (untuk dropdown form registrasi) |

### Protected (auth:sanctum)

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/me` | Data user yang login + unread_count |
| POST | `/api/logout` | Logout (hapus token) |
| GET | `/api/certification-categories` | Daftar kategori + jenis |

### Admin (auth:sanctum + role:admin)

#### Dashboard & Approval

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/dashboard` | Stats global + 5 pending + 5 expiring soon + **5 pendaftaran baru** |
| GET | `/api/admin/approval` | List dokumen pending_approval (filter kategori, departemen) |

#### Pendaftaran Akun

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/registrations` | List pendaftaran pending (filter: search) |
| POST | `/api/admin/registrations/{id}/approve` | Setujui pendaftaran → aktifkan akun + kirim email |
| POST | `/api/admin/registrations/{id}/reject` | Tolak pendaftaran + alasan wajib + kirim email |

#### Notifikasi Admin & Email Manual

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/notifications` | List notifikasi + unread_count |
| POST | `/api/admin/notifications/read-all` | Tandai semua dibaca |
| POST | `/api/admin/notifications/{id}/read` | Tandai satu dibaca |
| POST | `/api/admin/send-notification` | Kirim email manual ke pegawai (user_id + subject + body) |

#### Dokumen

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/documents` | Semua dokumen (filter: status, kategori, jenis, departemen, search, exp range) |
| POST | `/api/admin/documents` | Tambah dokumen untuk pegawai (langsung aktif). **Max file 2 MB.** |
| GET | `/api/admin/documents/export` | Export Excel (ikut filter) |
| GET | `/api/admin/documents/{id}` | Detail dokumen |
| POST | `/api/admin/documents/{id}` | Edit dokumen |
| DELETE | `/api/admin/documents/{id}` | Hapus dokumen |
| GET | `/api/admin/documents/{id}/download` | Download file |
| POST | `/api/admin/documents/{id}/approve` | Approve → status dihitung dari expiry_date |
| POST | `/api/admin/documents/{id}/reject` | Tolak + alasan wajib |

#### Pegawai

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/pegawai` | List pegawai (filter: search, departemen, status aktif) |
| POST | `/api/admin/pegawai` | Buat akun pegawai baru |
| GET | `/api/admin/pegawai/{id}` | Detail pegawai |
| POST | `/api/admin/pegawai/{id}` | Update data pegawai |
| DELETE | `/api/admin/pegawai/{id}` | Hapus pegawai + semua dokumennya |
| POST | `/api/admin/pegawai/{id}/toggle-active` | Aktifkan / nonaktifkan akun |
| POST | `/api/admin/pegawai/{id}/reset-password` | Reset password pegawai |

#### Departemen

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/departemen` | List departemen + jumlah pegawai |
| POST | `/api/admin/departemen` | Tambah departemen |
| PUT | `/api/admin/departemen/{id}` | Edit nama departemen |
| DELETE | `/api/admin/departemen/{id}` | Hapus (gagal jika ada pegawai aktif) |

#### Kategori Sertifikasi

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/kategori` | List kategori + jenis |
| POST | `/api/admin/kategori/{cat}/types` | Tambah jenis sertifikasi |
| PUT | `/api/admin/kategori/{cat}/types/{type}` | Edit jenis sertifikasi |
| DELETE | `/api/admin/kategori/{cat}/types/{type}` | Hapus (gagal jika dipakai dokumen) |

#### Audit Trail

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/audit` | Log aktivitas (server-side pagination, filter: search, activity_type, date_from, date_to) |
| GET | `/api/admin/audit/types` | List tipe aktivitas yang tersedia |

#### Manajemen Admin

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/admins` | List semua akun admin |
| POST | `/api/admin/admins` | Buat admin baru |
| PUT | `/api/admin/admins/{id}` | Update nama/email admin |
| DELETE | `/api/admin/admins/{id}` | Hapus admin (tidak bisa hapus diri sendiri) |
| POST | `/api/admin/admins/{id}/reset-password` | Reset password admin lain |

#### Pengaturan Sistem

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/pengaturan/reminders` | List jadwal reminder |
| POST | `/api/admin/pengaturan/reminders` | Tambah jadwal baru (`days_before`, `urgency`: rendah/sedang/tinggi/sangat_tinggi) |
| PATCH | `/api/admin/pengaturan/reminders/{id}/toggle` | Toggle aktif/nonaktif jadwal |
| DELETE | `/api/admin/pengaturan/reminders/{id}` | Hapus jadwal |
| GET | `/api/admin/pengaturan/email-templates` | List template email |
| PUT | `/api/admin/pengaturan/email-templates/{id}` | Update template email (`subject`, `body`) |
| GET | `/api/admin/pengaturan/mail-configs` | List profile SMTP (password tidak dikembalikan) |
| POST | `/api/admin/pengaturan/mail-configs` | Tambah profile SMTP baru |
| PUT | `/api/admin/pengaturan/mail-configs/{id}` | Update profile SMTP (password opsional — kosong = pakai password lama) |
| DELETE | `/api/admin/pengaturan/mail-configs/{id}` | Hapus profile (gagal jika sedang aktif) |
| POST | `/api/admin/pengaturan/mail-configs/{id}/activate` | Jadikan profile ini aktif (nonaktifkan yang lain) |
| POST | `/api/admin/pengaturan/mail-configs/{id}/test` | Kirim email uji ke `test_email` tanpa mengaktifkan profile |

### Pegawai (auth:sanctum + role:pegawai)

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/pegawai/dashboard` | Stats dokumen + 5 notifikasi terbaru |
| GET | `/api/pegawai/profile` | Data profil |
| POST | `/api/pegawai/profile` | Update HP + foto |
| PUT | `/api/pegawai/password` | Ganti password |
| GET | `/api/pegawai/documents` | List dokumen (semua, client-side filter) |
| POST | `/api/pegawai/documents` | Upload dokumen baru |
| GET | `/api/pegawai/documents/export` | Export Excel |
| GET | `/api/pegawai/documents/{id}` | Detail dokumen (+ `file_path`, `file_mime`) |
| POST | `/api/pegawai/documents/{id}` | Edit dokumen |
| DELETE | `/api/pegawai/documents/{id}` | Hapus dokumen |
| GET | `/api/pegawai/documents/{id}/download` | Download file (auth via Bearer token) |
| GET | `/api/pegawai/notifications` | List notifikasi + unread_count |
| POST | `/api/pegawai/notifications/read-all` | Tandai semua dibaca |
| POST | `/api/pegawai/notifications/{id}/read` | Tandai satu dibaca |

---

## 12. Status Implementasi

### Selesai ✅

#### Infrastructure & Auth
| Fitur | Keterangan |
|---|---|
| Setup project | Laravel 13 + PostgreSQL + Vue 3 + Tailwind v4 |
| Database | 17 migration, 13 model, semua relasi |
| Seeders | 8 seeder berurut: master → config → user (dengan avatar) → data transaksional |
| Auth | Login, **self-register**, logout, forgot/reset password, redirect by role |
| Middleware | EnsureRole, API 401 JSON (bukan redirect login) |
| Docker/Podman | Multi-stage Dockerfile, docker-compose.yml, nginx, supervisord, start.sh |
| Upload Limit | **Max 2 MB** (Laravel validation). PHP 5 MB, Nginx 5 MB. |

#### Role Pegawai
| Fitur | Keterangan |
|---|---|
| **Registrasi Mandiri** | Form registrasi publik → akun pending → admin approve → bisa login |
| UI — Modern Bold | Topbar + sidebar merah `#ED1B2F`, `.card-elevated`, Lime `#ACC42A` |
| Sidebar FAQ | FAQ dinamis per halaman, FAQ panel = modal popup dengan backdrop blur |
| Dashboard | 6 stat card, donut chart SVG, search, 4-panel bawah |
| Dokumen Saya | Filter multi client-side, toggle grid/list, export Excel |
| Detail Dokumen | 2 kolom: info kiri + preview file kanan (image/PDF/download) |
| Form Dokumen | 2 kolom: form kiri + preview real-time kanan |
| Notifikasi | Card per item, icon besar, badge tipe, border accent unread |
| Profil | 2 kolom: data diri kiri + keamanan kanan |

#### Role Admin
| Fitur | Keterangan |
|---|---|
| Dashboard | Stats global + list pending approval + list expiring soon + **list pendaftaran baru** |
| **Pendaftaran** | List pendaftaran pending, approve/reject dengan alasan, email otomatis |
| Dokumen | CRUD semua dokumen, filter + departemen, export Excel ikut filter |
| Detail Dokumen | Tombol approve/tolak di header, info pegawai |
| Form Dokumen | Dropdown pilih pegawai, auto-aktif saat admin input |
| Approval | List pending, filter kategori + departemen, modal approve + tolak |
| Pegawai | CRUD, toggle aktif, reset password, badge dokumen per baris, **kirim email manual** |
| Form Pegawai | Nama, email, NIP, HP, departemen, password, toggle status |
| Departemen | CRUD via modal, tampilkan jumlah pegawai, hapus diblokir jika ada pegawai aktif |
| Kategori Sertifikasi | 2 panel HSSE & Aviasi, edit inline, hapus diblokir jika dipakai dokumen |
| Audit Trail | Server-side pagination, expand row old vs new values, filter |
| Manajemen Admin | CRUD akun admin, hapus diblokir untuk akun sendiri |
| Pengaturan Sistem | Tab: Jadwal Reminder (toggle aktif) + Template Email + **Mail Config SMTP** |
| Notifikasi Admin | List + mark as read (bell di topbar aktif) |
| Profil Admin | Ganti password |

#### Backend Automation
| Fitur | Keterangan |
|---|---|
| Scheduled Reminder | `php artisan reminders:send` — daily via `routes/console.php` |
| Deduplication | Cek `reminder_logs` sebelum kirim, skip jika sudah dikirim |
| Auto-update Expired | Update status aktif/segera_expired → expired saat command berjalan |
| Mail Config SMTP | Admin kelola profile SMTP via UI. Email approve/reject otomatis dikirim ke pegawai. |
| Email Reminder | Saat approve/reject dokumen, email dikirim ke pegawai menggunakan active mail config |
| **Email Registrasi** | Email otomatis saat pendaftaran di-approve atau di-reject |
| **Email Manual** | Admin bisa kirim email custom ke pegawai dari halaman Pegawai |

#### Keamanan & Security Hardening
| Fitur | Keterangan |
|---|---|
| Rate Limiting | `throttle:5,1` (5 req/menit) pada endpoint auth (`/login`, `/register`, `/forgot-password`, `/reset-password`) mencegah brute-force |
| Password Security | Menggunakan cast `hashed`. Double-hash bug sudah di-fix di semua form |
| Mass Assignment Protection | `role`, `is_active`, dan `registration_status` dilindungi dari injeksi parameter |
| Database Security | Port PostgreSQL `5433` dihapus dari host expose, hanya bisa diakses dalam container network. |
| Data Privacy | Path internal `file_path` dihapus dari JSON API response, diganti `file_url`. Password SMTP `mail_password` di-hidden. |
| Server Hardening | Header `X-Powered-By` dinonaktifkan (`expose_php = Off`). Debug mode error dan stack trace dimatikan di production. |
| SQL Injection Protection | Wildcard `%` dan `_` di-escape otomatis pada query pencarian ILIKE |

#### Tipe Notifikasi

| Tipe | Channel | Penerima | Keterangan |
|---|---|---|---|
| `reminder_akan_expired` | in_app + email | Pegawai | Reminder H-X sebelum kadaluarsa |
| `dokumen_expired` | in_app + email | Pegawai + Admin | Dokumen sudah expired |
| `dokumen_pending` | in_app + email | Admin | Dokumen baru menunggu review |
| `dokumen_approved` | in_app + email | Pegawai | Dokumen disetujui |
| `dokumen_ditolak` | in_app + email | Pegawai | Dokumen ditolak + alasan |
| `registrasi_baru` | in_app | Admin | Pendaftaran baru masuk |
| `registrasi_approved` | in_app + email | Pegawai | Pendaftaran disetujui |
| `notifikasi_manual` | in_app + email | Pegawai | Email manual dari admin |

#### Template Email (7 template)

| Tipe | Placeholder |
|---|---|
| `reminder_akan_expired` | `{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}, {{hari_tersisa}}, {{departemen}}` |
| `dokumen_expired` | `{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}, {{departemen}}` |
| `dokumen_approved` | `{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}, {{departemen}}` |
| `dokumen_ditolak` | `{{nama}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{alasan_penolakan}}, {{departemen}}` |
| `dokumen_pending` | `{{nama}}, {{departemen}}, {{sertifikasi}}, {{nomor_sertifikat}}, {{tanggal_kadaluarsa}}` |
| `registrasi_approved` | `{{nama}}, {{email}}, {{nip}}, {{departemen}}` |
| `registrasi_rejected` | `{{nama}}, {{email}}, {{nip}}, {{departemen}}, {{alasan_penolakan}}` |

### Belum Diimplementasi ❌

| Fitur | Keterangan |
|---|---|
| Export Excel Admin di Dashboard | Export sudah ada di halaman Dokumen, belum di Dashboard. |
| Bulk upload dokumen | Admin upload banyak dokumen sekaligus via template Excel |
| WhatsApp notification | Selain email, reminder juga dikirim via WhatsApp |
| Calendar view | Tampilan kalender untuk melihat jadwal expired dokumen |

---

## 13. Catatan untuk Developer

### Aturan Wajib

- **Sebelum membuat UI baru** — baca `PHILOSOPHY.md` v2.0.
- **`file_path` dan `file_mime`** sudah di-return di response API dokumen — gunakan untuk preview, jangan hardcode extension.
- **Preview image** via `/storage/{file_path}` langsung. **Preview/download PDF** harus via axios + Bearer token → Blob.
- **Semua filter Dokumen** berjalan client-side dari satu fetch awal — jangan tambah request per filter.
- **`useFaq([...])`** — wajib dipanggil di setiap halaman baru untuk set FAQ kontekstual.
- **`.card-elevated`** adalah class global — jangan hardcode border/shadow per komponen.
- **`Storage::url()`** wajib untuk semua URL file/avatar dari backend.
- **Upload file max 2 MB** (2048 KB). Format: `pdf, jpg, jpeg, png`.
- **Semua user seeder** harus include `registration_status: 'approved'` agar tidak terblokir saat login.

### Docker

- Image pakai prefix `docker.io/` karena `registries.conf` tidak punya unqualified-search. Jangan hapus prefix ini.
- Named volume `storage_public` menyimpan avatar + file sertifikat. Jangan mount ulang tanpa backup.
- **Env production** diambil dari `.env.docker` (bukan `.env`). Copy dari `.env.docker.example` lalu isi credentials.
- **Port PostgreSQL host tidak terekspos**. Di dalam container-to-container tetap pakai `DB_HOST=db` + port 5432.
- Jangan hardcode credentials di `docker-compose.yml` — gunakan `env_file: .env.docker` atau format `${VAR:-default}`.
- **Upload limit**: Laravel 2 MB, PHP 5 MB, Nginx 5 MB. Laravel yang menolak duluan dengan pesan error yang jelas.

### Batas Upload File

| Layer | Batas | File |
|---|---|---|
| Laravel validation | **2 MB** (2048 KB) | `DocumentController` admin & pegawai |
| PHP `upload_max_filesize` | 5 MB | `docker/php/php.ini` |
| PHP `post_max_size` | 8 MB | `docker/php/php.ini` |
| Nginx `client_max_body_size` | 5 MB | `docker/nginx/default.conf` |
| Avatar (profil) | 2 MB | `ProfileController` |

### Backup

```bash
# Backup database
podman-compose exec db pg_dump -U root renot > backup_db_$(date +%Y%m%d).sql

# Backup file upload (sertifikat + avatar)
podman volume export renot_storage_public > backup_storage_$(date +%Y%m%d).tar

# Restore database
podman-compose exec -T db psql -U root renot < backup_db_20261007.sql

# Restore file upload
podman volume import renot_storage_public backup_storage_20261007.tar
```

### File .env

| File | Fungsi | Di Git? |
|---|---|---|
| `.env` | Development lokal | Tidak (gitignored) |
| `.env.example` | Template dev (di-copy ke `.env`) | Ya |
| `.env.docker` | Production Docker | Tidak (gitignored) |
| `.env.docker.example` | Template deploy Docker | Ya |

### Pola Admin Controller

Semua controller admin mengikuti pola yang sama:
- `abort_if($model->role !== 'expected_role', 404)` untuk validasi role
- `ActivityLog::record(...)` setelah setiap operasi mutasi
- Response selalu JSON, tidak ada redirect

### Pola Frontend Admin

- Semua data di-fetch sekali saat `onMounted`, filter berjalan client-side
- **Kecuali Audit Trail** — satu-satunya halaman dengan server-side pagination
- Modal selalu pakai `backdrop-filter:blur(2px)` + `background:rgba(0,0,0,0.40)`
- `useFaq([...])` dipanggil di setiap halaman admin

### Menambah Tipe Reminder Baru

1. Admin buka Pengaturan Sistem → Tab Jadwal Reminder → klik Tambah
2. Masukkan H-X dan tingkat urgensi
3. Command `reminders:send` akan otomatis memproses jadwal baru

### Menambah Template Email

1. Template dibuat secara otomatis oleh sistem saat notifikasi jenis baru pertama kali dikirim
2. Edit via Pengaturan Sistem → Tab Template Email
3. Gunakan placeholder `{Nama Pegawai}`, `{Nama Sertifikasi}`, dll

---

*Dokumen ini diperbarui pada 8 Oktober 2026. Versi 3.1: tambah security hardening (rate limiting 5 req/min, mass assignment fix, double-hash fix, hapus expose DB port, hapus X-Powered-By header, hide internal file_path dan mail_password).*
