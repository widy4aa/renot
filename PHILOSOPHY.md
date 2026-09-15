# ReNot — Design Philosophy
**Versi:** 2.0 (Modern Bold — Production)
**Tanggal:** 11 September 2026
**Berlaku untuk:** Seluruh halaman dan komponen ReNot (role Pegawai & Admin)

---

## 0. Mengapa Dokumen Ini Ada

AI tools selalu jatuh ke default yang sama — indigo-500, card grid tiga kolom, Inter, glassmorphism — bukan karena buruk, tapi karena tidak ada yang memberi mereka keputusan sebelumnya. Dokumen ini adalah kumpulan keputusan yang dibuat oleh manusia **sebelum** satu baris kode ditulis, dan diperbarui untuk mencerminkan implementasi yang sudah disetujui client.

> Aturan utama: Jika ada keputusan desain yang tidak ada di dokumen ini, **buat keputusan baru dan tambahkan di sini**. Jangan biarkan AI memilih sendiri.

> **v2.0:** Dokumen ini diperbarui berdasarkan UI role pegawai yang sudah dibangun, diuji, dan disetujui oleh client. Semua keputusan di sini adalah keputusan final — bukan target, tapi kenyataan. UI admin yang dibangun di Sprint 4 mengikuti dokumen ini sepenuhnya.

---

## 1. Karakter Produk

**ReNot adalah alat kerja, bukan produk konsumer.**

Dipakai oleh admin HR dan pegawai operasional perusahaan energi (konteks Pertamina) — bukan startup tech, bukan marketplace, bukan landing page. Interface-nya harus terasa seperti **instrumen profesional yang dapat dipercaya**, bukan aplikasi yang mencoba terlihat keren.

Tiga kata yang boleh menggambarkan ReNot secara visual:

- **Terpercaya** — user harus yakin data yang ditampilkan adalah data nyata
- **Jelas** — status dokumen, angka, dan notifikasi tidak boleh ambigu
- **Berani** — warna brand Pertamina dipakai penuh, bukan diencerkan jadi pastel

Tiga kata yang **tidak boleh** menggambarkan ReNot:

- ~~Futuristik~~ — glassmorphism, neon glow, gradient biru-ungu
- ~~Playful~~ — bounce animation, emoji, warna pastel berlebihan
- ~~Generic~~ — indigo-500, Inter, hero centered dengan dua tombol

---

## 2. Color Tokens

### 2.1 Palet Utama

Seluruh UI dibangun dari token warna berikut. Tidak boleh ada warna di luar daftar ini kecuali untuk status semantik (lihat 2.2) dan derived colors (lihat 2.3).

```
--color-bg          #F2F2F0   Background halaman utama (warm off-white)
--color-surface     #FFFFFF   Card, modal, panel, dropdown
--color-border      #E2E8F0   Border card, divider, garis pemisah (1.5px)
--color-primary     #ED1B2F   Merah Pertamina — CTA utama, topbar, sidebar, focus border input
--color-secondary   #006CB8   Biru Pertamina — CTA sekunder, ghost button, link, badge info
--color-accent      #ACC42A   Lime Pertamina — status Aktif, badge success
```

### 2.2 Status Semantik (dokumen)

Warna status dokumen adalah bagian dari sistem informasi — bukan dekorasi. Setiap warna punya makna tunggal dan tidak boleh dipakai untuk hal lain.

```
Aktif           #ACC42A   Lime Pertamina — dokumen berlaku, aman
Segera Expired  #D97706   Amber — perlu perhatian segera
Expired         #ED1B2F   Merah Pertamina — kritis, harus diperbarui
Pending         #6B7280   Abu — menunggu, netral
Ditolak         #DB2777   Pink-merah — tindakan gagal, perlu diperbaiki
```

### 2.3 Derived Colors (badge & tipe notifikasi)

Warna turunan yang diizinkan untuk konteks badge tipe notifikasi dan chip informational. Tidak boleh dipakai di luar konteks ini.

```
Badge "Pegawai" role     bg #FEE2E2, color #991B1B
Badge "Disetujui"        bg #F7FEE7, color #5a6e0f
Badge "Reminder"         bg #FEF3C7, color #92400E
Badge "Ditolak" notif    bg #FCE7F3, color #9D174D
Badge "Expired" notif    bg #FEE2E2, color #991B1B
Badge "Pending" notif    bg #F3F4F6, color #6B7280
```

### 2.4 Text Colors

```
--color-text-heading    #111827   Judul, label penting
--color-text-body       #374151   Teks paragraf, isi konten
--color-text-muted      #6B7280   Placeholder, metadata, timestamp, caption
--color-text-disabled   #9CA3AF   Elemen nonaktif, label input (uppercase)
--color-text-inverse    #FFFFFF   Teks di atas background merah / gelap
```

### 2.5 Asal Warna Pertamina

`#ED1B2F`, `#006CB8`, dan `#ACC42A` diambil langsung dari SVG logo resmi PT Pertamina (Persero) di pertamina.com. Ini bukan interpretasi — ini hex aktual dari aset digital resmi perusahaan.

Dalam v2.0, **`#ED1B2F` (Merah Pertamina) adalah warna dominan** — digunakan untuk topbar, sidebar, CTA utama, dan focus state input. `#006CB8` tetap dipakai sebagai warna sekunder untuk elemen yang perlu dibedakan dari CTA utama (ghost button, link, badge info).

### 2.6 Yang Dilarang

- ❌ Gradient apapun (linear, radial, mesh)
- ❌ `indigo-500` atau turunannya sebagai primary
- ❌ Warna di luar daftar di atas tanpa keputusan tertulis di dokumen ini
- ❌ Sidebar admin dan sidebar pegawai beda warna — keduanya harus `#ED1B2F`

---

## 3. Typography

### 3.1 Font Family

**Satu font, semua ukuran.**

```
Font: Plus Jakarta Sans
Sumber: Google Fonts
Weights yang di-load: 400 (Regular), 500 (Medium), 600 (SemiBold), 700 (Bold)
Fallback: ui-sans-serif, system-ui, sans-serif
```

Plus Jakarta Sans dipilih karena:
- Dibuat oleh desainer Indonesia (Tokotype) — kontekstual untuk produk dalam ekosistem perusahaan Indonesia
- Geometric, bersih, professional tanpa terasa steril
- Keterbacaan tinggi di ukuran kecil (label tabel, badge status)
- Tersedia gratis, tidak ada lisensi enterprise

Tidak ada font kedua. Tidak ada font display, tidak ada font monospace kecuali untuk menampilkan kode teknikal (yang tidak ada di ReNot).

### 3.2 Type Scale

```
text-[10px]   10px / 1.4    Metadata mikro — timestamp ringkas, group label sidebar
text-xs       12px / 1.5    Label input (uppercase), badge, timestamp, caption
text-sm       14px / 1.5    Body utama, isi list, deskripsi, nav item sidebar
text-base     16px / 1.6    Jam live topbar, paragraf sedang
text-lg       18px / 1.4    Sub-heading, nama seksi
text-xl       20px / 1.3    Heading halaman sekunder
text-2xl      24px / 1.2    Heading halaman utama (H1), nama "ReNot" di topbar
text-3xl      30px / 1.1    Angka total (donut chart, ringkasan)
text-4xl      36px / 1.0    Angka hitung mundur hari (DayCounter di DokumenDetail)
text-5xl      48px / 1.0    Angka besar stat card dashboard
```

### 3.3 Weight Convention

```
400 Regular      → Body text, deskripsi, teks panjang
500 Medium       → Teks notifikasi unread, item yang perlu sedikit penekanan
600 SemiBold     → Nav item sidebar, sub-heading, tombol, badge text
700 Bold         → Heading halaman, label card, nama "ReNot" di topbar, jam live
800 ExtraBold    → Angka stat card (text-5xl), angka DayCounter (text-4xl)
```

### 3.4 Label Style

Label input dan label section pendek menggunakan pola berikut:

```
font-size      : text-xs (12px)
font-weight    : font-bold (700)
text-transform : uppercase
letter-spacing : tracking-wide
color          : #9CA3AF
```

Maksimal 2 kata untuk ALL CAPS. Frasa lebih panjang tidak boleh diuppercase.

### 3.5 Yang Dilarang

- ❌ Font size di bawah 10px
- ❌ Font weight di bawah 400
- ❌ Italic kecuali untuk quote atau catatan editorial
- ❌ Font lain selain Plus Jakarta Sans tanpa keputusan tertulis
- ❌ ALL CAPS untuk frasa lebih dari 2 kata

---

## 4. Spacing & Layout

### 4.1 Spacing Scale

Mengikuti skala Tailwind (basis 4px). Tidak ada nilai spacing arbitrary.

```
4px    →  gap kecil dalam komponen (ikon + teks)
8px    →  padding internal komponen kecil (p-2)
10px   →  padding tombol vertikal (py-2.5)
12px   →  padding outer layout (p-3), gap antar panel
16px   →  padding card kecil (p-4), gap antar elemen dalam section
20px   →  padding topbar horizontal (px-5)
24px   →  padding card utama (px-6, py-5)
32px   →  margin antar section besar (space-y-6 ≈ 24px, space-y-8 ≈ 32px)
```

### 4.2 Layout

```
Background layout : #F2F2F0
Outer padding     : p-3 (12px semua sisi)
Gap antar panel   : gap-3 (12px) — antara topbar↔area bawah, sidebar↔konten
Topbar height     : h-14 (56px)
Sidebar collapsed : 64px (default saat pertama load)
Sidebar expanded  : 220px
Sidebar transition: width 200ms ease
Sidebar position  : sticky, top 12px, height calc(100vh - 90px)
Content area      : flex-1 min-w-0 (full width sisa setelah sidebar)
```

### 4.3 Grid

- **Stat card dashboard**: `grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3`
- **Dokumen grid**: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4` — diizinkan karena berisi data nyata, bukan marketing card
- **Dashboard bottom**: `lg:grid-cols-4` — notifikasi (2 col) + sertifikat (1 col) + donut chart (1 col)
- **Form & Detail**: layout 2 kolom (`lg:grid-cols-5` — 3/5 kiri + 2/5 kanan sticky)
- ❌ Tidak ada hero section tiga kolom "icon + judul + dua baris teks" untuk keperluan marketing

---

## 5. Border Radius

**Tiga nilai utama, dipakai konsisten per konteks.**

```
Radius panel besar : 16px (rounded-2xl) — topbar, sidebar, modal, avatar, card header utama
Radius default     : 12px (rounded-xl)  — card konten (.card-elevated), input, tombol, dropdown panel
Radius kecil       : 8px  (rounded-lg)  — icon container w-7 h-7 di nav, tab kecil
Radius penuh       : 9999px (rounded-full) — badge pill status, dot indikator, avatar fallback
```

Panduan per elemen:
- Topbar & Sidebar: `border-radius: 16px` (floating dalam `p-3` layout)
- `.card-elevated`: `border-radius: 12px`
- Input field: `rounded-xl` (12px)
- Tombol: `rounded-xl` (12px)
- Icon container `w-7 h-7` di nav sidebar: `rounded-lg` (8px)
- Status badge / pill: `rounded-full` (9999px)
- Modal dialog: `rounded-2xl` (16px)

---

## 6. Shadow

**Minimal dan fungsional. Komponen floating menggunakan shadow yang memperkuat hierarki layer.**

```
.card-elevated       : 0 2px 8px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.03)
.card-elevated:hover : 0 4px 16px rgba(0,0,0,0.10), 0 0 0 1px rgba(0,0,0,0.04)
Topbar               : 0 2px 16px rgba(237,27,47,0.25), 0 1px 4px rgba(0,0,0,0.08)
Dropdown panel       : 0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06)
Modal / FAQ panel    : 0 8px 32px rgba(0,0,0,0.15)
Modal overlay        : background rgba(0,0,0,0.40)
```

Topbar menggunakan shadow dengan tint merah (`rgba(237,27,47,0.25)`) — ini disengaja untuk mempertegas bahwa topbar adalah layer brand utama.

Card hover **boleh** menambah shadow secara halus. Ini adalah satu-satunya hover yang mengizinkan perubahan shadow. Stat card dashboard tambahan menggunakan `translateY(-2px)` saat hover.

Tidak ada neon glow. Tidak ada shadow berwarna selain topbar.

---

## 7. Component Rules

### 7.1 Tombol

```
Primer   → bg #ED1B2F, text #fff, hover bg #c8102e, rounded-xl, px-4 py-2.5
Sekunder → .card-elevated + text #374151, rounded-xl, px-4 py-2.5
Ghost    → bg transparent, text #ED1B2F, hover bg #FEE2E2, rounded-lg
Link     → text #ED1B2F, no background, inline
```

- Font: `text-sm font-semibold`
- Hover state: via `onmouseover`/`onmouseout` inline (konsisten dengan kode yang sudah ada)
- Ukuran kecil: `px-3 py-1.5`
- ❌ Tidak ada tombol dengan gradient
- ❌ Tidak ada pasangan dua tombol ghost bersebelahan di hero

### 7.2 Card

```css
/* Global class — WAJIB dipakai, jangan hardcode border/shadow per komponen */
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

- Padding standar: `px-6 py-5` (card besar), `p-4` (card stat/kecil), `p-3` (card sangat kecil)
- ❌ Tidak ada glassmorphism (backdrop-filter: blur)
- ❌ Tidak ada border berwarna di card biasa (hanya untuk card status khusus)

### 7.3 Input & Form

```
background    : #F9FAFB
border        : 1.5px solid #E2E8F0
border-radius : rounded-xl (12px)
padding       : px-3 py-2.5 atau px-4 py-2.5
font-size     : text-sm (14px)
font-weight   : font-medium
color         : #111827
focus         : border-color #ED1B2F (via @focus / @blur JS handler)
error         : border-color #ED1B2F + pesan merah di bawah
```

- Label: selalu di atas input, `text-xs font-bold uppercase tracking-wide color:#9CA3AF`
- Error message: di bawah input, `color:#ED1B2F`, `text-xs`
- Required field: `<span style="color:#ED1B2F">*</span>` — bukan teks "(wajib)"
- `outline-none` dipakai, focus state via border-color JS handler

### 7.4 Badge / Status Pill

```
border-radius : rounded-full (9999px)
padding       : px-2.5 py-1 (standar), px-2 py-0.5 (kecil), px-1.5 py-0.5 (mikro)
font-size     : text-xs (12px) standar, text-[10px] untuk badge mikro di topbar
font-weight   : font-semibold atau font-bold
```

Warna mengikuti §2.2 untuk status dokumen, atau §2.3 untuk badge tipe notifikasi.

### 7.5 Tabel

```
Header row : bg #F9FAFB, text-xs uppercase tracking-wide, color #6B7280, font-weight 600
Body row   : bg #FFFFFF, border-bottom 1px solid #F3F4F6
Hover row  : bg #F9FAFB
```

- Tidak ada border kanan-kiri per cell (borderless columns)
- Tidak ada zebra striping
- Pagination di bawah tabel, sederhana

### 7.6 Sidebar Navigasi

```
bg             : #ED1B2F
border-radius  : 16px
width default  : 64px (collapsed — default saat load)
width expanded : 220px
transition     : width 200ms ease
position       : sticky, top: 12px, height: calc(100vh - 90px)

Nav item:
  default  → color rgba(255,255,255,0.85), bg transparent
  hover    → color #fff, bg rgba(255,255,255,0.12)
  active   → color #fff, bg rgba(255,255,255,0.20), font-weight 700

Icon container (w-7 h-7, rounded-lg):
  default  → bg rgba(255,255,255,0.12)
  hover    → bg rgba(255,255,255,0.22)
  active   → bg rgba(255,255,255,0.30)

Group label    : text-[10px] uppercase tracking-widest, color rgba(255,255,255,0.50)
Divider        : bg rgba(255,255,255,0.15), h-px

Badge counter (expanded)  : bg rgba(255,255,255,0.25), color #fff, rounded-full
Badge dot (collapsed)     : bg #fff, w-2 h-2, rounded-full, absolute top-1 right-1
```

FAQ panel: muncul di `absolute bottom-full`, background `#fff`, border `#E5E7EB`, `rounded-2xl`, shadow `0 8px 32px rgba(0,0,0,0.15)`.

### 7.7 Topbar

```
bg             : #ED1B2F
height         : h-14 (56px)
border-radius  : 16px
padding        : px-5
shadow         : 0 2px 16px rgba(237,27,47,0.25), 0 1px 4px rgba(0,0,0,0.08)

Kiri:
  - Logo container: w-10 h-10 rounded-xl bg #fff + icon.png h-7 w-7
  - Teks "ReNot": text-2xl font-bold color #fff
  - Divider: w-px h-5, bg rgba(255,255,255,0.40)
  - Teks "Pertamina": text-base font-bold uppercase tracking-widest, color rgba(255,255,255,0.90)

Kanan:
  - Bell button: w-9 h-9 rounded-xl, hover bg rgba(255,255,255,0.15)
  - Unread indicator: dot putih w-2 h-2 + animate-ping saat ada notif unread
  - Divider: w-px h-5, bg rgba(255,255,255,0.30)
  - Jam live: text-base font-bold tabular-nums color #fff, update setiap detik
```

Notifikasi dropdown: `w-80 rounded-2xl bg-white`, border `#E5E7EB`, shadow panel (lihat §6). Header `#FAFAFA`, footer `#FAFAFA`.

---

## 8. Motion & Animation

**Minimal, fungsional, tidak mengejutkan.**

```
Transisi warna/border    : 150ms ease
Transisi background      : 150ms ease
Transisi shadow card     : 200ms ease (.card-elevated hover)
Fade modal masuk         : 200ms ease-out
Fade modal keluar        : 150ms ease-in
Slide dropdown masuk     : 150ms ease-out (opacity + translateY + scale)
Slide dropdown keluar    : 100ms ease-in
Sidebar width            : 200ms ease (collapse/expand)
Stat card hover          : translateY(-2px) + shadow lebih dalam, 200ms ease
animate-pulse            : Skeleton loading (Tailwind default)
animate-ping             : Badge dot notif unread di bell topbar
animate-spin             : Spinner loading saat fetch async
```

- ❌ Tidak ada bounce
- ❌ Tidak ada spring animation
- ❌ Tidak ada counter angka yang berputar saat load
- Selalu hormati `prefers-reduced-motion`

---

## 9. Ikonografi

**Heroicons Outline** — satu set ikon, konsisten.

```
Ukuran nav sidebar   : 16x16px (w-4 h-4) — di dalam icon container w-7 h-7
Ukuran default       : 20x20px (w-5 h-5) — bell topbar, tombol dengan ikon
Ukuran medium        : 24x24px (w-6 h-6) — empty state icon, modal icon
Ukuran stat card     : 28x28px (w-7 h-7) — icon di dalam container w-12 h-12
Stroke width default : 1.75px (bukan 1.5) — lebih tebal untuk keterbacaan di atas background merah
Stroke width tipis   : 1.5px — untuk ikon di empty state dan dropdown
```

- ❌ Tidak ada ikon filled dan outline dicampur di halaman yang sama
- ❌ Tidak ada emoji sebagai ikon UI
- ❌ Tidak ada ikon dekoratif yang tidak punya fungsi
- Loading spinner (`animate-spin`): kombinasi circle stroke + path fill diizinkan khusus untuk elemen ini

---

## 10. Empty State & Loading

### Empty State

```
Konteks dropdown kecil (topbar notif) : container w-12 h-12 rounded-2xl bg #F3F4F6
Konteks halaman dokumen               : container w-14 h-14 rounded-2xl bg #F3F4F6
Konteks halaman notifikasi            : container w-16 h-16 rounded-2xl bg #F3F4F6

Ikon dalam container : w-6 h-6, color #D1D5DB, stroke 1.5
Judul                : text-sm font-medium, color #6B7280
Deskripsi            : text-xs, color #9CA3AF (opsional)
```

Selalu ada empty state yang dirancang. Tidak boleh ada halaman yang menampilkan list/tabel kosong tanpa pesan apapun.

### Loading / Skeleton

```
Blok utama   : bg-gray-200
Blok sekunder: bg-gray-100
Radius       : sama dengan elemen yang digantikan
Animasi      : animate-pulse (Tailwind)
```

Skeleton harus menyerupai bentuk konten yang akan muncul. Contoh: stat card skeleton = grid 6 kolom dengan blok label + blok angka per card.

---

## 11. Accessibility Baseline

- Semua teks harus memenuhi WCAG AA minimum sebisa mungkin (4.5:1 untuk teks normal, 3:1 untuk teks besar)
- Teks pada background `#ED1B2F` menggunakan `#ffffff` atau minimum `rgba(255,255,255,0.85)`
- Setiap input yang punya label harus dihubungkan via `for`/`id` — kecuali search bar inline yang punya `placeholder` deskriptif
- Warna status tidak boleh jadi satu-satunya indikator — selalu tambah teks atau ikon di samping warna
- Tabel harus punya `<th scope>` yang benar
- Tombol icon-only harus punya atribut `title` untuk tooltip keyboard/screenreader

---

## 12. Yang Selalu Dilarang (Anti-Pattern List)

| Dilarang | Alasan |
|---|---|
| Gradient apapun | Tidak ada konteks yang membenarkan gradient di aplikasi compliance |
| Glassmorphism / backdrop-blur | Dekoratif, berat di GPU, tidak meningkatkan keterbacaan |
| `indigo-500` sebagai primary | Default AI slop — tidak ada hubungan dengan brand |
| Hero tiga kolom "icon + judul + 2 baris" | Pattern marketing page, bukan aplikasi data |
| Bounce / spring animation | Tidak profesional untuk konteks enterprise |
| Font size < 10px | Aksesibilitas — terutama untuk pegawai yang mungkin lebih tua |
| Warna di luar token yang terdaftar | Menciptakan inkonsistensi dan design debt |
| Tombol pasangan primer + ghost di hero | Pattern marketing page, bukan aplikasi |
| Neon glow | Dekoratif tanpa fungsi |
| Teks ALL CAPS lebih dari 2 kata | Melelahkan dibaca, terasa teriak |
| Emoji sebagai ikon UI | Tidak konsisten antar platform/OS |
| Sidebar admin berbeda warna dari sidebar pegawai | Konsistensi brand — keduanya `#ED1B2F` |

---

## 13. Referensi

| Referensi | Untuk apa |
|---|---|
| [pertamina.com](https://www.pertamina.com/) | Sumber warna brand (#ED1B2F, #006CB8, #ACC42A) |
| [WCAG 2.1 AA](https://www.w3.org/TR/WCAG21/) | Standar aksesibilitas minimum |
| [Heroicons](https://heroicons.com/) | Library ikon |
| [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans) | Font utama |
| [Tailwind CSS](https://tailwindcss.com/) | Utility framework |
| UI Role Pegawai ReNot (Sprint 3) | **Sumber kebenaran utama** — implementasi yang sudah disetujui client |
| UI Role Admin ReNot (Sprint 4) | Mengikuti philosophy ini — konsisten dengan pegawai |

---

*Dokumen ini adalah keputusan desain yang hidup. Setiap perubahan harus didiskusikan, dicatat alasannya, dan di-commit bersama dengan perubahan kode yang mengikutinya. Jangan ubah token tanpa memperbarui dokumen ini.*

**Terakhir diverifikasi:** 11 September 2026 — seluruh UI admin (Sprint 4) dikonfirmasi konsisten dengan philosophy ini.
