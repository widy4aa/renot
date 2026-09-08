# ReNot — Design Philosophy
**Versi:** 1.0
**Tanggal:** 8 September 2026
**Berlaku untuk:** Seluruh halaman dan komponen ReNot

---

## 0. Mengapa Dokumen Ini Ada

AI tools selalu jatuh ke default yang sama — indigo-500, card grid tiga kolom, Inter, glassmorphism — bukan karena buruk, tapi karena tidak ada yang memberi mereka keputusan sebelumnya. Dokumen ini adalah kumpulan keputusan yang dibuat oleh manusia **sebelum** satu baris kode ditulis. Setiap token di sini punya alasan, bukan tebakan.

> Aturan utama: Jika ada keputusan desain yang tidak ada di dokumen ini, **buat keputusan baru dan tambahkan di sini**. Jangan biarkan AI memilih sendiri.

---

## 1. Karakter Produk

**ReNot adalah alat kerja, bukan produk konsumer.**

Dipakai oleh admin HR dan pegawai operasional perusahaan energi (konteks Pertamina) — bukan startup tech, bukan marketplace, bukan landing page. Interface-nya harus terasa seperti **instrumen profesional yang dapat dipercaya**, bukan aplikasi yang mencoba terlihat keren.

Tiga kata yang boleh menggambarkan ReNot secara visual:

- **Terpercaya** — user harus yakin data yang ditampilkan adalah data nyata
- **Jelas** — status dokumen, angka, dan notifikasi tidak boleh ambigu
- **Tenang** — tidak ada elemen yang berteriak, tidak ada animasi yang tidak perlu

Tiga kata yang **tidak boleh** menggambarkan ReNot:

- ~~Futuristik~~ — glassmorphism, neon glow, gradient biru-ungu
- ~~Playful~~ — bounce animation, emoji, warna pastel berlebihan
- ~~Generic~~ — card grid tiga kolom, indigo-500, hero centered

---

## 2. Color Tokens

### 2.1 Palet Utama

Seluruh UI dibangun dari 5 token warna dasar. Tidak boleh ada warna di luar daftar ini kecuali untuk status semantik (lihat 2.2).

```
--color-bg          #F8F8F7   Background halaman utama (warm off-white)
--color-surface     #FFFFFF   Card, modal, panel, sidebar
--color-border      #E5E7EB   Border, divider, garis pemisah
--color-primary     #006CB8   Biru Pertamina — CTA, link, active state, focus ring
--color-danger      #ED1B2F   Merah Pertamina — expired, ditolak, hapus, alert kritis
```

### 2.2 Status Semantik (dokumen)

Warna status dokumen adalah bagian dari sistem informasi — bukan dekorasi. Setiap warna punya makna tunggal dan tidak boleh dipakai untuk hal lain.

```
Aktif           #16A34A   Hijau — dokumen berlaku, aman
Segera Expired  #D97706   Amber — perlu perhatian segera
Expired         #ED1B2F   Merah Pertamina — kritis, harus diperbarui
Pending         #6B7280   Abu — menunggu, netral
Ditolak         #DB2777   Pink-merah — tindakan gagal, perlu diperbaiki
```

### 2.3 Text Colors

```
--color-text-heading    #111827   Judul, label penting
--color-text-body       #374151   Teks paragraf, isi konten
--color-text-muted      #6B7280   Placeholder, metadata, timestamp, caption
--color-text-disabled   #9CA3AF   Elemen nonaktif
--color-text-inverse    #FFFFFF   Teks di atas background gelap / primary
```

### 2.4 Asal Warna Pertamina

`#006CB8` dan `#ED1B2F` diambil langsung dari SVG logo resmi PT Pertamina (Persero) di pertamina.com. Ini bukan interpretasi — ini hex aktual dari aset digital resmi perusahaan.

Warna ketiga Pertamina (`#ACC42A` Lime) **sengaja tidak dipakai** di versi ini karena terlalu vivid untuk interface compliance yang dipakai lama. Bisa ditambahkan di versi mendatang untuk elemen sustainability/NRE jika ada konteks yang sesuai.

### 2.5 Yang Dilarang

- ❌ Gradient apapun (linear, radial, mesh)
- ❌ `indigo-500` atau turunannya sebagai primary
- ❌ Warna di luar daftar di atas tanpa keputusan tertulis
- ❌ Opacity warna sebagai cara menambah varian baru
- ❌ Warna berbeda untuk role yang sama (sidebar admin dan sidebar pegawai harus sama)

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
text-xs     12px / 1.5    Label, badge, timestamp, caption
text-sm     14px / 1.5    Body utama, isi tabel, deskripsi
text-base   16px / 1.6    Paragraf panjang (jarang dipakai di app)
text-lg     18px / 1.4    Sub-heading, nama seksi
text-xl     20px / 1.3    Heading halaman sekunder
text-2xl    24px / 1.2    Heading halaman utama (H1)
text-3xl    30px / 1.1    Angka besar di stat card
```

### 3.3 Weight Convention

```
400 Regular    → Body text, deskripsi, teks panjang
500 Medium     → Label input, navigasi sidebar, badge text
600 SemiBold   → Sub-heading, nama kolom tabel, tombol
700 Bold       → Heading halaman, angka stat card, alert title
```

### 3.4 Yang Dilarang

- ❌ ALL CAPS untuk konten — hanya boleh untuk label pendek (max 2 kata) dan uppercase dengan letter-spacing
- ❌ Font size di bawah 12px
- ❌ Font weight di bawah 400
- ❌ Italic kecuali untuk quote atau catatan editorial
- ❌ Font lain selain Plus Jakarta Sans tanpa keputusan tertulis

---

## 4. Spacing & Layout

### 4.1 Spacing Scale

Mengikuti skala Tailwind (basis 4px). Tidak ada nilai spacing arbitrary.

```
4px    →  gap kecil dalam komponen (ikon + teks)
8px    →  padding internal komponen kecil
12px   →  padding badge, pill, chip
16px   →  padding card, gap antar elemen dalam section
20px   →  padding container sedang
24px   →  gap antar card, padding section
32px   →  margin antar section besar
```

### 4.2 Layout

```
Sidebar width     : 240px (fixed, tidak collapsible di versi ini)
Content max-width : tidak ada (full width dalam main area)
Content padding   : 24px semua sisi
Header height     : 56px
```

### 4.3 Grid

- **Stat card dashboard**: `grid-cols-2` di mobile, `grid-cols-3` atau `grid-cols-6` di desktop — angka disesuaikan jumlah card, bukan dipaksakan tiga kolom
- **Form**: Single column, max-width 672px (42rem)
- **Tabel**: Full width content area
- ❌ Tidak ada hero section tiga kolom "icon + judul + dua baris teks"

---

## 5. Border Radius

**Satu nilai, konsisten.**

```
Radius default   : 8px   (card, input, tombol, modal)
Radius kecil     : 4px   (badge, tag, chip, tooltip)
Radius penuh     : 9999px (avatar, toggle, pill status)
```

Bukan 12px yang terlalu friendly, bukan 2px yang terlalu kaku. 8px adalah titik tengah yang terasa enterprise tapi tidak dingin.

---

## 6. Shadow

**Minimal dan fungsional.**

```
shadow-none   : Elemen flat — tabel, sidebar, navigasi
shadow-sm     : Card di atas background — elevasi paling umum
shadow-md     : Dropdown, popover, date picker — elemen mengambang
shadow-lg     : Modal, dialog — layer tertinggi
```

Tidak ada shadow berwarna. Tidak ada neon glow. Tidak ada shadow multiple layered untuk kesan "depth".

---

## 7. Component Rules

### 7.1 Tombol

```
Primer   → bg #006CB8, text putih, hover bg #005a9e
Sekunder → bg putih, border #E5E7EB, text #374151, hover bg #F9FAFB
Bahaya   → bg #ED1B2F, text putih, hover bg #c8102e
Ghost    → bg transparent, text #006CB8, hover bg #EFF6FF
```

- Ukuran: `px-4 py-2` (default), `px-3 py-1.5` (small)
- Radius: 8px
- Font: 14px SemiBold
- ❌ Tidak ada tombol dengan gradient
- ❌ Tidak ada pasangan dua tombol ghost bersebelahan di hero

### 7.2 Card

```
bg: #FFFFFF
border: 1px solid #E5E7EB
border-radius: 8px
padding: 20px atau 24px
shadow: shadow-sm
```

- ❌ Tidak ada glassmorphism (backdrop-filter: blur)
- ❌ Tidak ada border berwarna di card biasa (hanya untuk card status khusus)
- ❌ Tidak ada hover yang mengubah shadow secara dramatis

### 7.3 Input & Form

```
border: 1px solid #D1D5DB
border-radius: 8px
padding: 8px 12px
font-size: 14px
focus: ring 2px #006CB8, border transparent
error: border #ED1B2F, ring #ED1B2F
```

- Label selalu di atas input, bukan floating label
- Error message di bawah input, warna `#ED1B2F`, font-size 12px
- Required field tandai dengan `*` merah, bukan teks "(wajib)"

### 7.4 Badge / Status Pill

```
border-radius: 9999px (pill)
padding: 2px 10px
font-size: 12px
font-weight: 500
```

Warna mengikuti tabel status semantik di 2.2. Tidak boleh ada badge berwarna di luar daftar itu.

### 7.5 Tabel

```
Header row : bg #F9FAFB, font 12px uppercase tracking-wide, text #6B7280, font-weight 600
Body row   : bg #FFFFFF, border-bottom 1px #F3F4F6
Hover row  : bg #F9FAFB
```

- Tidak ada border kanan-kiri per cell (borderless columns)
- Tidak ada zebra striping
- Pagination di bawah tabel, sederhana

### 7.6 Sidebar Navigasi

```
bg: #FFFFFF
border-right: 1px solid #E5E7EB
width: 240px

Menu item:
  default  → text #6B7280, bg transparent
  hover    → text #111827, bg #F9FAFB
  active   → text #006CB8, bg #EFF6FF, font-weight 600
  icon     → 16x16px, stroke, warna sama dengan teks
```

- Active indicator: background biru muda `#EFF6FF`, teks biru `#006CB8`
- Tidak ada indicator garis kiri (left border) — background sudah cukup
- Badge unread: bg `#ED1B2F`, teks putih, pill kecil

---

## 8. Motion & Animation

**Hampir tidak ada.**

```
Transisi warna/border : 150ms ease
Transisi background   : 150ms ease
Fade modal masuk      : 200ms ease
Slide dropdown        : 150ms ease-out
```

- ❌ Tidak ada bounce
- ❌ Tidak ada spring animation
- ❌ Tidak ada skeleton pulse yang terlalu mencolok (gunakan subtle opacity 0.5–1)
- ❌ Tidak ada counter angka yang berputar saat load
- Selalu hormati `prefers-reduced-motion`

---

## 9. Ikonografi

**Heroicons Outline** — satu set ikon, konsisten.

```
Ukuran default : 20x20px (w-5 h-5)
Ukuran kecil   : 16x16px (w-4 h-4) — di dalam tombol, badge
Ukuran besar   : 24x24px (w-6 h-6) — ilustrasi state kosong
Stroke width   : 1.5px (default Heroicons)
Warna          : Ikut warna teks parent element
```

- ❌ Tidak ada ikon filled dan outline dicampur di halaman yang sama
- ❌ Tidak ada emoji sebagai ikon UI
- ❌ Tidak ada ikon dekoratif yang tidak punya fungsi

---

## 10. Empty State & Loading

### Empty State

```
Ikon    : 40x40px, text-gray-300
Judul   : text-sm, text-gray-500
Deskripsi: text-xs, text-gray-400 (opsional)
CTA     : link teks atau tombol sekunder kecil
```

Selalu ada empty state yang dirancang. Tidak boleh ada halaman yang menampilkan tabel/list kosong tanpa pesan apapun.

### Loading / Skeleton

```
bg: #F3F4F6 (gray-100)
border-radius: sama dengan elemen yang digantikan
animasi: pulse opacity sederhana (animate-pulse Tailwind)
```

Skeleton harus menyerupai bentuk konten yang akan muncul. Tidak boleh hanya satu blok abu-abu memanjang untuk semua jenis konten.

---

## 11. Accessibility Baseline

- Semua teks harus memenuhi WCAG AA minimum (4.5:1 untuk teks normal, 3:1 untuk teks besar)
- Setiap elemen interaktif harus punya focus state yang terlihat (`ring-2 ring-blue-500`)
- Semua input harus punya label yang terhubung via `for`/`id`
- Warna status tidak boleh jadi satu-satunya indikator — selalu tambah teks atau ikon
- Tabel harus punya `<th scope>` yang benar

---

## 12. Yang Selalu Dilarang (Anti-Pattern List)

Ini adalah daftar hal yang tidak boleh muncul di ReNot, kapanpun, dengan alasan apapun:

| Dilarang | Alasan |
|---|---|
| Gradient apapun | Tidak ada konteks yang membenarkan gradient di aplikasi compliance |
| Glassmorphism / backdrop-blur | Dekoratif, berat di GPU, tidak meningkatkan keterbacaan |
| `indigo-500` sebagai primary | Default AI slop — tidak ada hubungan dengan brand |
| Card grid tiga kolom "icon + judul + 2 baris" | Pattern paling umum AI, tidak ada nilai informasi tambahan |
| Bounce / spring animation | Tidak profesional untuk konteks enterprise |
| Font size < 12px | Aksesibilitas — terutama untuk pegawai yang mungkin lebih tua |
| Warna di luar token yang terdaftar | Menciptakan inkonsistensi dan debt desain |
| Tombol pasangan primer + ghost di hero | Pattern marketing page, bukan aplikasi |
| Shadow berwarna / neon glow | Dekoratif tanpa fungsi |
| Teks ALL CAPS lebih dari 2 kata | Melelahkan dibaca, terasa teriak |
| Emoji sebagai ikon UI | Tidak konsisten antar platform/OS |

---

## 13. Referensi

| Referensi | Untuk apa |
|---|---|
| [pertamina.com](https://www.pertamina.com/) | Sumber warna brand (#006CB8, #ED1B2F) |
| [WCAG 2.1 AA](https://www.w3.org/TR/WCAG21/) | Standar aksesibilitas minimum |
| [Heroicons](https://heroicons.com/) | Library ikon |
| [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans) | Font utama |
| [Tailwind CSS](https://tailwindcss.com/) | Utility framework |
| *AI Design Slop — Mohit Phogat (2026)* | Dasar filosofi dokumen ini |

---

*Dokumen ini adalah keputusan desain yang hidup. Setiap perubahan harus didiskusikan, dicatat alasannya, dan di-commit bersama dengan perubahan kode yang mengikutinya. Jangan ubah token tanpa memperbarui dokumen ini.*
