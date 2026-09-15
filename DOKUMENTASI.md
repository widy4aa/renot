# DOKUMENTASI TEKNIS — ReNot
## Reminder & Notification Sertifikasi Pegawai

**Versi dokumen:** 2.0
**Terakhir diperbarui:** 11 September 2026
**Status project:** Sprint 4 selesai — Seluruh fitur Admin & Pegawai selesai dibangun

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
- **Admin** — mengelola semua pegawai, dokumen, approval, konfigurasi sistem
- **Pegawai** — mengelola dokumen milik sendiri, menerima reminder

**Dokumen referensi:**
- `SRS-ReNot.md` — Software Requirements Specification lengkap
- `database.dbml` — Database diagram (bisa dibuka di dbdiagram.io)
- `PHILOSOPHY.md` — Design system dan aturan UI v2.0 (WAJIB dibaca sebelum membuat UI baru)

---

## 2. Tech Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | Laravel | 13.x |
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
├── Dockerfile                  ← Multi-stage build (composer → node → php-fpm-nginx)
├── docker-compose.yml          ← 2 service: app + db
├── .dockerignore
└── docker/
    ├── nginx/default.conf      ← Nginx port 8000, SPA catch-all, storage alias
    ├── php/php.ini             ← upload 10M, memory 256M, opcache
    ├── supervisord.conf        ← Manage php-fpm + nginx dalam 1 container
    └── start.sh                ← Entrypoint: wait DB → migrate → cache → start
```

### Build & Run

```bash
cd laravel-vue-template

# Build image dan jalankan semua service
podman compose up -d --build

# Lihat log
podman compose logs -f app
podman compose logs -f db

# Seed database (pertama kali setelah container jalan)
podman compose exec app php artisan db:seed

# Reset database
podman compose exec app php artisan migrate:fresh --seed

# Stop semua container
podman compose down
```

### Akses setelah deploy

| Service | URL |
|---|---|
| Aplikasi ReNot | `http://127.0.0.1:8000` |
| PostgreSQL | `127.0.0.1:5432` |

### Catatan Podman

- Semua image pakai prefix `docker.io/` (wajib jika `registries.conf` tidak punya unqualified-search)
- `podman-compose` kompatibel dengan `docker-compose.yml` v3
- Storage avatar dan file sertifikat di-mount ke named volume — tidak hilang saat container di-recreate
- `APP_KEY` di `docker-compose.yml` sudah di-set — jangan generate ulang kecuali fresh deploy

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
│   │   │   │   ├── DashboardController.php      ← Stats global + pending + expiring
│   │   │   │   ├── DepartemenController.php     ← CRUD departemen
│   │   │   │   ├── DocumentController.php       ← CRUD dokumen semua pegawai + approve/reject
│   │   │   │   ├── KategoriController.php       ← CRUD jenis sertifikasi per kategori
│   │   │   │   ├── ManajemenAdminController.php ← CRUD akun admin
│   │   │   │   ├── NotificationController.php   ← Notifikasi admin
│   │   │   │   ├── PegawaiController.php        ← CRUD pegawai + toggle aktif + reset password
│   │   │   │   └── PengaturanController.php     ← Jadwal reminder + template email
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php
│   │   │   ├── Pegawai/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── DocumentController.php
│   │   │   │   ├── NotificationController.php
│   │   │   │   └── ProfileController.php
│   │   │   └── CertificationCategoryController.php
│   │   └── Middleware/
│   │       └── EnsureRole.php
│   └── Models/ (12 model)
├── database/
│   ├── migrations/                    ← 16 migration file
│   └── seeders/
│       ├── DocumentSeeder.php         ← Dokumen + dummy file dari /sertif/
│       └── ... (6 seeder lainnya)
├── docker/                            ← Konfigurasi Docker/Podman
├── routes/
│   ├── api.php                        ← Semua API endpoint
│   ├── console.php                    ← Scheduled command (reminders:send daily)
│   └── web.php                        ← Catch-all → SPA
└── resources/
    ├── css/app.css                    ← Tailwind v4 + design tokens
    └── js/
        ├── app.js
        ├── router/index.js
        ├── stores/auth.js
        ├── composables/
        │   ├── useDocumentHelpers.js  ← statusConfig, formatDate, formatFileSize, daysUntilExpiry
        │   └── useFaq.js             ← FAQ dinamis per halaman (inject/provide)
        ├── components/
        │   ├── AppSidebar.vue        ← Sidebar merah, collapsible, FAQ modal popup
        │   └── AppTopbar.vue         ← Jam live, bell notifikasi, dropdown
        ├── layouts/
        │   ├── PegawaiLayout.vue     ← provide setPageFaqs, fetch unread count
        │   └── AdminLayout.vue       ← provide setPageFaqs, fetch unread count admin
        └── pages/
            ├── auth/
            │   ├── Login.vue
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
                ├── Dashboard.vue           ← Stats global + pending + expiring soon
                ├── Approval.vue            ← List pending, modal approve/tolak
                ├── Dokumen.vue             ← Semua dokumen + filter departemen
                ├── DokumenDetail.vue       ← Detail + tombol approve/tolak/edit
                ├── DokumenForm.vue         ← Tambah/edit + pilih pegawai
                ├── Notifikasi.vue
                ├── Profile.vue
                ├── Pegawai.vue             ← List pegawai + toggle aktif + reset pwd
                ├── PegawaiForm.vue         ← Form tambah/edit pegawai
                ├── Departemen.vue          ← CRUD departemen via modal
                ├── Kategori.vue            ← CRUD jenis sertifikasi per kategori
                ├── Audit.vue               ← Log aktivitas + pagination server-side
                ├── ManajemenAdmin.vue      ← CRUD akun admin
                ├── Pengaturan.vue          ← Jadwal reminder + template email
                └── AdminPlaceholder.vue   ← Komponen "Segera Hadir" reusable
```

---

## 6. Database

**21 tabel** di database `renot`. Semua sudah ter-migrate.

### Tabel Utama

| Tabel | Keterangan |
|---|---|
| `users` | Admin & pegawai dalam satu tabel. Kolom `role`: `admin`/`pegawai`. |
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
POST /api/login → token + user (avatar via Storage::url)
GET  /api/me    → user + unread_notifications_count
POST /api/logout → hapus token
```

### Middleware

**`EnsureRole`** — alias `role`, cek `$request->user()->role`
- `middleware('role:pegawai')` untuk route pegawai
- `middleware('role:admin')` untuk route admin

Untuk API, unauthenticated request return **401 JSON** (bukan redirect ke login).

### Admin Controllers

| Controller | Fungsi Utama |
|---|---|
| `DashboardController` | Stats global: total pegawai, dokumen, pending, expired. List 5 pending + 5 expiring soon. |
| `ApprovalController` | List dokumen `pending_approval` dengan filter kategori + departemen |
| `DocumentController` | CRUD semua dokumen + approve + reject + download + export Excel (ikut filter) |
| `PegawaiController` | CRUD pegawai + toggleActive + resetPassword |
| `DepartemenController` | CRUD departemen. Hapus dicegah jika ada pegawai aktif. |
| `KategoriController` | CRUD jenis sertifikasi per kategori. Hapus dicegah jika dipakai dokumen. |
| `AuditController` | List activity_logs dengan server-side pagination + filter |
| `ManajemenAdminController` | CRUD akun admin. Tidak bisa hapus diri sendiri. |
| `NotificationController` | Notifikasi in-app admin + markAsRead + markAllAsRead |
| `PengaturanController` | CRUD reminder_schedules + toggle aktif. CRUD email_templates. |

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

6 Stat card (grid-cols-6):
  Total Pegawai (klik → admin.pegawai) | Total Dokumen | Pending | Expired | Segera Expired | Aktif

2 panel bawah (grid-cols-2):
  Menunggu Approval (5 terbaru) | Segera Kadaluarsa (5 terbaru)
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

| Role | Nama | Email | No. Pegawai | Departemen |
|---|---|---|---|---|
| Admin | Administrator | admin@renot.app | — | — |
| Pegawai | Budi Santoso | budi@renot.app | EMP-001 | HSSE |
| Pegawai | Siti Rahayu | siti@renot.app | EMP-002 | Aviasi |
| Pegawai | Ahmad Fauzi | ahmad@renot.app | EMP-003 | Operasional |

**Semua dokumen** sudah punya file dummy (gambar sertifikat) dari folder `/sertif/` — tombol Download dan Preview aktif.

---

## 11. API Endpoints

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
| GET | `/api/certification-categories` | Daftar kategori + jenis |

### Admin (auth:sanctum + role:admin)

#### Dashboard & Approval

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/dashboard` | Stats global + 5 pending + 5 expiring soon |
| GET | `/api/admin/approval` | List dokumen pending_approval (filter kategori, departemen) |

#### Dokumen

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/documents` | Semua dokumen (filter: status, kategori, jenis, departemen, search, exp range) |
| POST | `/api/admin/documents` | Tambah dokumen untuk pegawai (langsung aktif) |
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

#### Notifikasi Admin

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/admin/notifications` | List notifikasi + unread_count |
| POST | `/api/admin/notifications/read-all` | Tandai semua dibaca |
| POST | `/api/admin/notifications/{id}/read` | Tandai satu dibaca |

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
| POST | `/api/admin/pengaturan/reminders` | Tambah jadwal baru |
| PATCH | `/api/admin/pengaturan/reminders/{id}/toggle` | Toggle aktif/nonaktif |
| DELETE | `/api/admin/pengaturan/reminders/{id}` | Hapus jadwal |
| GET | `/api/admin/pengaturan/email-templates` | List template email |
| PUT | `/api/admin/pengaturan/email-templates/{id}` | Update template email |

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
| Database | 16 migration, 12 model, semua relasi |
| Seeders | User, departemen, kategori, reminder, dokumen + dummy file, notifikasi |
| Auth | Login, logout, forgot/reset password, redirect by role |
| Middleware | EnsureRole, API 401 JSON (bukan redirect login) |
| Docker/Podman | Multi-stage Dockerfile, docker-compose.yml, nginx, supervisord, start.sh |

#### Role Pegawai
| Fitur | Keterangan |
|---|---|
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
| Dashboard | Stats global + list pending approval + list expiring soon |
| Dokumen | CRUD semua dokumen, filter + departemen, export Excel ikut filter |
| Detail Dokumen | Tombol approve/tolak di header, info pegawai |
| Form Dokumen | Dropdown pilih pegawai, auto-aktif saat admin input |
| Approval | List pending, filter kategori + departemen, modal approve + tolak |
| Pegawai | CRUD, toggle aktif, reset password, badge dokumen per baris |
| Form Pegawai | Nama, email, NIP, HP, departemen, password, toggle status |
| Departemen | CRUD via modal, tampilkan jumlah pegawai, hapus diblokir jika ada pegawai aktif |
| Kategori Sertifikasi | 2 panel HSSE & Aviasi, edit inline, hapus diblokir jika dipakai dokumen |
| Audit Trail | Server-side pagination, expand row old vs new values, filter |
| Manajemen Admin | CRUD akun admin, hapus diblokir untuk akun sendiri |
| Pengaturan Sistem | Tab: Jadwal Reminder (toggle aktif) + Template Email |
| Notifikasi Admin | List + mark as read (bell di topbar aktif) |
| Profil Admin | Ganti password |

#### Backend Automation
| Fitur | Keterangan |
|---|---|
| Scheduled Reminder | `php artisan reminders:send` — daily via `routes/console.php` |
| Deduplication | Cek `reminder_logs` sebelum kirim, skip jika sudah dikirim |
| Auto-update Expired | Update status aktif/segera_expired → expired saat command berjalan |

### Belum Diimplementasi ❌

| Fitur | Keterangan |
|---|---|
| Kirim email reminder | Backend command hanya kirim notifikasi in-app. Email butuh konfigurasi SMTP/mailer. |
| Export Excel Admin di Dashboard | Export sudah ada di halaman Dokumen, belum di Dashboard. |

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

### Docker

- Image pakai prefix `docker.io/` karena `registries.conf` tidak punya unqualified-search. Jangan hapus prefix ini.
- Named volume `storage_public` menyimpan avatar + file sertifikat. Jangan mount ulang tanpa backup.
- `APP_KEY` di `docker-compose.yml` sudah di-set — jangan generate ulang kecuali fresh deploy.

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

*Dokumen ini diperbarui pada 11 September 2026. Versi 2.0: dokumentasi lengkap Sprint 1–4, mencakup seluruh fitur admin dan pegawai yang sudah selesai diimplementasi.*
