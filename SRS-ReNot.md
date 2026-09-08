# Software Requirements Specification (SRS)
# ReNot - Reminder & Notification Sertifikasi Pegawai

**Versi:** 1.1
**Tanggal:** 8 September 2026
**Status:** Draft - Revisi Fitur Lengkap

---

## Changelog

| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 1.0 | 7 Sep 2026 | Draft awal — brainstorming fitur & flow |
| 1.1 | 8 Sep 2026 | Penambahan fitur pendukung: profil & akun, pengaturan sistem, departemen, audit trail, approval workflow, multi-admin |

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen
Dokumen ini menjelaskan kebutuhan fungsional aplikasi **ReNot** -- sebuah sistem untuk melacak dan mengelola dokumen/sertifikasi pegawai dengan fitur reminder otomatis sebelum masa berlaku habis.

### 1.2 Latar Belakang
Pegawai memiliki berbagai dokumen sertifikasi yang memiliki masa berlaku (HSSE & Aviasi). Saat ini tidak ada sistem terpusat yang memantau status dokumen tersebut, sehingga sering terjadi dokumen expired tanpa disadari. Aplikasi ini hadir untuk mengatasi masalah tersebut.

### 1.3 Lingkup Aplikasi
- Pengelolaan data dokumen/sertifikasi pegawai
- Monitoring status dokumen (aktif, segera expired, expired)
- Reminder otomatis via email/notifikasi
- Dashboard ringkasan untuk Admin dan Pegawai
- Approval workflow dokumen yang diupload pegawai
- Manajemen departemen/divisi untuk pengelompokan pegawai
- Audit trail aktivitas perubahan data dokumen
- Pengaturan sistem yang dapat dikonfigurasi admin

### 1.4 Pengguna Sistem
| Role    | Deskripsi                                                    |
|---------|--------------------------------------------------------------|
| Admin   | Mengelola seluruh dokumen pegawai, monitoring, konfigurasi sistem, dan approval |
| Pegawai | Mengelola dokumen milik sendiri, menerima reminder           |

> **Catatan:** Pegawai **tidak** bisa mendaftar sendiri. Akun pegawai dibuat oleh Admin.
> Admin bisa lebih dari satu; akun admin dikelola melalui halaman khusus yang terpisah dari menu Pegawai.

---

## 2. Kategori Dokumen/Sertifikasi

### 2.1 HSSE (Health, Safety, Security & Environment)
| Kode           | Nama Lengkap               |
|----------------|------------------------------|
| GSI            | GSI                          |
| SI             | SI                           |
| AT             | AT                           |
| HSSE Passport  | HSSE Passport                |
| Proper         | Proper                       |
| Lainnya        | Sertifikat HSSE lainnya      |

### 2.2 Aviasi
| Kode     | Nama Lengkap               |
|----------|------------------------------|
| RDS      | RDS                          |
| PACE     | PACE                         |
| Lainnya  | Sertifikat Aviasi lainnya    |

### 2.3 Catatan Kategori
- Admin dapat **menambah, mengubah, atau menghapus** jenis sertifikasi dalam masing-masing kategori (HSSE/Aviasi).
- Jenis sertifikasi ditampilkan sebagai dropdown yang bisa dikustomisasi oleh Admin.
- Kategori utama (HSSE dan Aviasi) bersifat **tetap** dan tidak bisa dihapus.

---

## 3. Fitur & Kebutuhan Fungsional

### 3.1 Dashboard Admin

Dashboard utama Admin menampilkan ringkasan keseluruhan sistem.

| No | Fitur                                     | Deskripsi                                                        |
|----|-------------------------------------------|------------------------------------------------------------------|
| 1  | Jumlah seluruh pegawai                    | Menampilkan total pegawai yang terdaftar dalam sistem            |
| 2  | Jumlah dokumen/sertifikasi aktif          | Total dokumen yang masih berlaku                                 |
| 3  | Jumlah dokumen akan expired               | Total dokumen yang mendekati tanggal kadaluarsa (H-60 ke bawah) |
| 4  | Jumlah dokumen sudah expired              | Total dokumen yang sudah melewati tanggal kadaluarsa             |
| 5  | Jumlah dokumen pending approval           | Total dokumen yang menunggu review admin                         |
| 6  | Monitoring berdasarkan kategori HSSE      | Breakdown status dokumen khusus kategori HSSE                    |
| 7  | Monitoring berdasarkan kategori Aviasi    | Breakdown status dokumen khusus kategori Aviasi                  |
| 8  | Filter per departemen                     | Breakdown statistik difilter berdasarkan departemen/divisi       |

**Flow Admin Dashboard:**
```
Admin login --> Halaman Dashboard
  |
  |--> Lihat ringkasan angka (cards/widget)
  |--> Klik card "Akan Expired" --> Masuk ke daftar dokumen akan expired
  |--> Klik card "Sudah Expired" --> Masuk ke daftar dokumen expired
  |--> Klik card "Pending Approval" --> Masuk ke halaman approval
  |--> Filter monitoring per kategori HSSE / Aviasi
  |--> Filter monitoring per departemen
```

### 3.2 Dashboard Pegawai

Dashboard pegawai menampilkan ringkasan dokumen milik pegawai yang login.

| No | Fitur                          | Deskripsi                                              |
|----|--------------------------------|--------------------------------------------------------|
| 1  | Ringkasan dokumen              | Total dokumen milik pegawai                            |
| 2  | Dokumen aktif                  | Jumlah dan daftar dokumen yang masih berlaku            |
| 3  | Dokumen mendekati expired      | Jumlah dan daftar dokumen yang akan segera kadaluarsa   |
| 4  | Dokumen expired                | Jumlah dan daftar dokumen yang sudah kadaluarsa         |
| 5  | Dokumen pending approval       | Jumlah dokumen yang sedang menunggu review admin        |
| 6  | Reminder/notifikasi            | Daftar notifikasi terbaru terkait dokumen pegawai       |

**Flow Pegawai Dashboard:**
```
Pegawai login --> Halaman Dashboard Pegawai
  |
  |--> Lihat ringkasan dokumen pribadi
  |--> Lihat daftar notifikasi/reminder
  |--> Klik dokumen --> Detail dokumen
  |--> Klik "Upload" --> Form upload dokumen baru
```

---

### 3.3 Pengelolaan Dokumen/Sertifikasi

#### 3.3.1 Fitur Admin

| No | Fitur                              | Deskripsi                                                      |
|----|------------------------------------|----------------------------------------------------------------|
| 1  | Lihat seluruh dokumen pegawai      | Menampilkan tabel semua dokumen dari seluruh pegawai           |
| 2  | Tambah dokumen/sertifikasi         | Menambahkan dokumen baru untuk pegawai tertentu                |
| 3  | Ubah data dokumen                  | Mengedit informasi dokumen yang sudah ada                      |
| 4  | Hapus dokumen                      | Menghapus dokumen dari sistem                                  |
| 5  | Lihat detail dokumen               | Menampilkan informasi lengkap satu dokumen                     |
| 6  | Upload file sertifikat             | Mengupload file fisik dokumen (PDF/gambar)                     |
| 7  | Kelola kategori & jenis sertifikasi| Menambah/mengubah/menghapus jenis sertifikasi pada dropdown    |
| 8  | Lihat dokumen akan expired         | Filter khusus dokumen yang mendekati kadaluarsa                |
| 9  | Lihat dokumen expired              | Filter khusus dokumen yang sudah kadaluarsa                    |
| 10 | Filter berdasarkan HSSE/Aviasi     | Memfilter daftar dokumen berdasarkan kategori utama            |
| 11 | Filter berdasarkan jenis sertifikasi| Memfilter berdasarkan jenis spesifik (GSI, RDS, dll)          |
| 12 | Filter berdasarkan departemen      | Memfilter daftar dokumen berdasarkan departemen pegawai        |
| 13 | Cari berdasarkan nama pegawai      | Fitur search untuk mencari dokumen berdasarkan nama            |
| 14 | Lihat tanggal kadaluarsa           | Menampilkan informasi tanggal expired pada setiap dokumen      |
| 15 | Approve / Tolak dokumen            | Review dan setujui atau tolak dokumen yang diupload pegawai    |
| 16 | Download file sertifikat           | Mengunduh file dokumen dari sistem                             |

**Flow Admin Kelola Dokumen:**
```
Admin --> Menu "Dokumen"
  |
  |--> Lihat daftar semua dokumen (tabel)
  |      |--> Search by nama pegawai
  |      |--> Filter by kategori (HSSE / Aviasi)
  |      |--> Filter by jenis sertifikasi (dropdown)
  |      |--> Filter by departemen
  |      |--> Filter by status (Aktif / Segera Expired / Expired / Pending Approval)
  |      |--> Klik baris --> Detail dokumen
  |
  |--> Tombol "Tambah Dokumen"
  |      |--> Pilih pegawai
  |      |--> Isi form input dokumen
  |      |--> Upload file
  |      |--> Simpan (langsung aktif karena admin yang input)
  |
  |--> Aksi pada setiap dokumen:
         |--> Edit --> Form edit --> Simpan
         |--> Hapus --> Konfirmasi --> Hapus
         |--> Download file
         |--> Approve / Tolak (jika status Pending Approval)
```

**Flow Admin Kelola Kategori:**
```
Admin --> Menu "Pengaturan Kategori"
  |
  |--> Lihat daftar jenis sertifikasi per kategori
  |--> Tambah jenis sertifikasi baru
  |--> Edit nama jenis sertifikasi
  |--> Hapus jenis sertifikasi (dengan konfirmasi)
```

**Flow Admin Approval:**
```
Admin --> Menu "Approval" (atau notifikasi masuk)
  |
  |--> Lihat daftar dokumen dengan status "Pending Approval"
  |--> Klik dokumen --> Detail dokumen + preview file
  |--> Pilih:
         |--> "Approve" --> Status berubah menjadi Aktif/Segera Expired/Expired
         |                   sesuai perhitungan tanggal kadaluarsa
         |--> "Tolak" --> Wajib isi alasan penolakan --> Status "Ditolak"
                           --> Pegawai menerima notifikasi penolakan + alasan
```

#### 3.3.2 Fitur Pegawai

| No | Fitur                          | Deskripsi                                                          |
|----|--------------------------------|--------------------------------------------------------------------|
| 1  | Lihat dokumen milik sendiri    | Menampilkan daftar dokumen yang dimiliki pegawai                   |
| 2  | Upload dokumen/sertifikat      | Mengupload dokumen baru milik sendiri (masuk status Pending Approval) |
| 3  | Ubah data dokumen              | Mengedit informasi dokumen milik sendiri (re-trigger approval)     |
| 4  | Hapus/ganti dokumen            | Menghapus atau mengganti file dokumen                              |
| 5  | Lihat detail sertifikasi       | Menampilkan informasi lengkap satu dokumen                         |
| 6  | Terima notifikasi reminder     | Menerima reminder otomatis sebelum dokumen expired                 |
| 7  | Lihat status approval          | Melihat apakah dokumen sudah disetujui, ditolak, atau masih pending |
| 8  | Lihat alasan penolakan         | Melihat keterangan alasan jika dokumen ditolak admin               |

**Flow Pegawai Kelola Dokumen:**
```
Pegawai --> Menu "Dokumen Saya"
  |
  |--> Lihat daftar dokumen pribadi
  |      |--> Filter by status (Aktif / Segera Expired / Expired / Pending / Ditolak)
  |      |--> Klik baris --> Detail dokumen
  |
  |--> Tombol "Upload Dokumen Baru"
  |      |--> Isi form input dokumen (nama otomatis terisi)
  |      |--> Upload file
  |      |--> Simpan --> Status "Pending Approval"
  |      |--> Admin menerima notifikasi ada dokumen baru untuk direview
  |
  |--> Aksi pada setiap dokumen:
         |--> Edit --> Form edit --> Simpan --> Kembali ke "Pending Approval"
         |--> Hapus --> Konfirmasi --> Hapus
         |--> Ganti file --> Upload file baru --> Kembali ke "Pending Approval"
         |--> Download file
```

---

### 3.4 Form Input Dokumen Sertifikat

| No | Field                         | Tipe Input     | Keterangan                                       |
|----|-------------------------------|----------------|--------------------------------------------------|
| 1  | Nama Pegawai                  | Text (default) | Otomatis terisi dari data pegawai yang login/dipilih |
| 2  | Jenis Dokumen/Sertifikasi     | Dropdown       | Pilihan: HSSE / Aviasi                           |
| 3  | Kategori                      | Dropdown       | Dropdown dinamis sesuai jenis yang dipilih (GSI, RDS, dll) |
| 4  | Nomor Sertifikat              | Text           | Input manual                                     |
| 5  | Tanggal Pelaksanaan Sertifikasi| Date Picker   | Tanggal pelaksanaan/ujian                        |
| 6  | Tanggal Terbit                | Date Picker    | Tanggal sertifikat diterbitkan                    |
| 7  | Tanggal Kadaluarsa            | Date Picker    | Tanggal sertifikat berakhir masa berlakunya       |
| 8  | File Sertifikat               | File Upload    | Upload file PDF/gambar                           |
| 9  | Nomor HP                      | Text (default) | Otomatis terisi dari profil pegawai               |
| 10 | Email Pegawai                 | Text (default) | Otomatis terisi dari profil pegawai               |
| 11 | Status Dokumen                | Auto           | Dihitung otomatis; untuk pegawai selalu mulai dari "Pending Approval" |

**Flow Pengisian Form:**
```
Buka form input dokumen
  |
  |--> Field default terisi otomatis (nama, HP, email)
  |--> Pilih jenis dokumen (HSSE / Aviasi)
  |      |--> Dropdown kategori berubah sesuai jenis yang dipilih
  |             HSSE --> GSI, SI, AT, HSSE Passport, Proper, Lainnya
  |             Aviasi --> RDS, PACE, Lainnya
  |--> Isi nomor sertifikat
  |--> Pilih tanggal pelaksanaan
  |--> Pilih tanggal terbit
  |--> Pilih tanggal kadaluarsa
  |--> Upload file sertifikat
  |--> Simpan
         |--> Jika Admin yang input --> Langsung aktif, status dihitung dari tanggal
         |--> Jika Pegawai yang input --> Status "Pending Approval"
```

### 3.5 Logika Status Dokumen

Status dokumen dihitung otomatis berdasarkan selisih antara tanggal kadaluarsa dan tanggal hari ini:

| Status           | Kondisi                                    | Warna Indikator |
|------------------|--------------------------------------------|-----------------|
| Pending Approval | Baru diupload pegawai, menunggu review     | Abu-abu         |
| Ditolak          | Ditolak admin, perlu diperbaiki pegawai    | Merah muda      |
| Aktif            | Tanggal kadaluarsa > H+60 dari hari ini    | Hijau           |
| Segera Expired   | Tanggal kadaluarsa <= H+60 dari hari ini   | Kuning/Oranye   |
| Expired          | Tanggal kadaluarsa < hari ini              | Merah           |

> Status **Aktif / Segera Expired / Expired** hanya berlaku setelah dokumen disetujui (Approved) oleh Admin. Dokumen berstatus Pending atau Ditolak tidak masuk hitungan reminder.

---

### 3.6 Sistem Notifikasi & Reminder

#### 3.6.1 Jadwal Reminder

Jadwal reminder **dapat dikonfigurasi oleh Admin** melalui halaman Pengaturan Sistem. Default awal:

| Tahap | Waktu Sebelum Expired | Kategori Urgensi |
|-------|-----------------------|------------------|
| 1     | H-60                  | Rendah           |
| 2     | H-45                  | Rendah           |
| 3     | H-30                  | Sedang           |
| 4     | H-20                  | Sedang           |
| 5     | H-10                  | Tinggi           |
| 6     | H-7                   | Tinggi           |
| 7     | H-6                   | Tinggi           |
| 8     | H-5                   | Tinggi           |
| 9     | H-4                   | Tinggi           |
| 10    | H-3                   | Sangat Tinggi    |
| 11    | H-2                   | Sangat Tinggi    |
| 12    | H-1                   | Sangat Tinggi    |

#### 3.6.2 Penerima Notifikasi

| Penerima | Jenis Notifikasi                                |
|----------|-------------------------------------------------|
| Pegawai  | Reminder dokumen miliknya akan expired           |
| Admin    | Reminder dokumen pegawai akan expired            |
| Admin    | Informasi dokumen pegawai yang sudah expired     |
| Admin    | Notifikasi ada dokumen baru yang perlu di-review |
| Pegawai  | Notifikasi dokumen disetujui atau ditolak        |

#### 3.6.3 Channel Notifikasi

| Channel                   | Deskripsi                                       |
|---------------------------|-------------------------------------------------|
| Email                     | Email otomatis ke pegawai dan admin              |
| Notifikasi Dalam Aplikasi | Badge/bell notification di dalam aplikasi        |

#### 3.6.4 Isi Email Reminder

Setiap email reminder berisi informasi berikut:

```
----------------------------------------------------------
Subjek: [Reminder] Sertifikat {Nama Sertifikasi} Akan
        Expired dalam {X} Hari

Isi Email:
----------------------------------------------------------
Yth. {Nama Pegawai},

Berikut informasi sertifikasi Anda yang akan segera
berakhir masa berlakunya:

  Nama Sertifikasi  : {Nama Sertifikasi}
  Nomor Sertifikat   : {Nomor Sertifikat}
  Tanggal Expired    : {Tanggal Expired}
  Status             : {Segera Expired / Expired}
  Sisa Waktu         : {X hari lagi}

Harap segera melakukan langkah-langkah berikut untuk
memperbarui dokumen Anda:

  1. Persiapkan persyaratan perpanjangan sertifikasi
  2. Hubungi bagian terkait untuk penjadwalan ulang
     sertifikasi
  3. Setelah mendapatkan sertifikat baru, upload dokumen
     terbaru ke sistem ReNot

Jika Anda sudah memperbarui sertifikasi ini, silakan
abaikan email ini dan pastikan dokumen terbaru sudah
diupload ke sistem.

Terima kasih.

Salam,
Tim Admin ReNot
----------------------------------------------------------
```

> Template email dapat diedit oleh Admin melalui halaman Pengaturan Sistem, menggunakan placeholder seperti `{Nama Pegawai}`, `{Nama Sertifikasi}`, dll.

**Flow Notifikasi:**
```
Sistem cek harian (scheduled job)
  |
  |--> Ambil semua dokumen approved yang tanggal kadaluarsanya
  |    sesuai jadwal reminder
  |
  |--> Untuk setiap dokumen yang cocok:
  |      |--> Kirim email ke pegawai pemilik dokumen
  |      |--> Kirim email ke admin
  |      |--> Buat notifikasi dalam aplikasi
  |
  |--> Untuk dokumen yang sudah expired:
         |--> Kirim notifikasi ke admin bahwa dokumen sudah expired
         |--> Update status dokumen menjadi "Expired"
```

---

## 4. Fitur Profil & Akun

### 4.1 Profil Pegawai

Setiap pegawai dapat melihat dan mengedit data diri mereka sendiri.

| No | Fitur              | Deskripsi                                                           |
|----|--------------------|---------------------------------------------------------------------|
| 1  | Lihat profil       | Menampilkan nama, email, nomor HP, departemen, foto profil          |
| 2  | Edit profil        | Mengubah nomor HP dan foto profil (nama & email diubah oleh admin)  |
| 3  | Ganti password     | Mengubah password sendiri dengan verifikasi password lama           |
| 4  | Lupa password      | Reset password via link yang dikirim ke email                       |

**Catatan:** Data dari profil pegawai (nomor HP, email) digunakan secara otomatis pada form input dokumen dan pengiriman notifikasi.

### 4.2 Manajemen Akun Admin

Halaman khusus untuk mengelola akun admin, **terpisah** dari menu Pegawai.

| No | Fitur                  | Deskripsi                                               |
|----|------------------------|---------------------------------------------------------|
| 1  | Lihat daftar admin     | Menampilkan semua akun admin yang terdaftar             |
| 2  | Tambah admin baru      | Membuat akun admin baru                                 |
| 3  | Edit data admin        | Mengubah nama, email admin                              |
| 4  | Nonaktifkan/hapus admin| Menonaktifkan atau menghapus akun admin                 |
| 5  | Reset password admin   | Admin utama bisa reset password admin lain              |

---

## 5. Manajemen Pegawai

### 5.1 Data Pegawai

| No | Field           | Tipe       | Keterangan                                      |
|----|-----------------|------------|-------------------------------------------------|
| 1  | Nomor Pegawai   | Text       | Unik per pegawai, diisi admin saat pendaftaran  |
| 2  | Nama Lengkap    | Text       | Nama resmi pegawai                              |
| 3  | Email           | Email      | Dipakai untuk login dan notifikasi              |
| 4  | Nomor HP        | Text       | Dipakai untuk notifikasi (WhatsApp jika ada)    |
| 5  | Departemen      | Dropdown   | Pilihan dari master data departemen             |
| 6  | Foto Profil     | File       | Opsional                                        |
| 7  | Status Akun     | Toggle     | Aktif / Nonaktif                                |

### 5.2 Fitur Admin - Kelola Pegawai

| No | Fitur              | Deskripsi                                               |
|----|--------------------|---------------------------------------------------------|
| 1  | Lihat daftar pegawai | Tabel semua pegawai dengan filter departemen          |
| 2  | Tambah pegawai     | Buat akun pegawai baru (admin yang mendaftarkan)        |
| 3  | Edit data pegawai  | Ubah data pegawai termasuk departemen                   |
| 4  | Nonaktifkan pegawai| Menonaktifkan akun tanpa menghapus data dokumen         |
| 5  | Hapus pegawai      | Menghapus akun dan seluruh dokumen terkait (konfirmasi) |
| 6  | Reset password     | Admin bisa reset password pegawai                       |

---

## 6. Manajemen Departemen

### 6.1 Deskripsi
Admin dapat mengelola master data departemen/divisi. Departemen digunakan untuk:
- Mengelompokkan pegawai
- Filter daftar dokumen dan statistik dashboard per departemen

### 6.2 Fitur

| No | Fitur                | Deskripsi                                             |
|----|----------------------|-------------------------------------------------------|
| 1  | Lihat daftar departemen | Menampilkan semua departemen yang terdaftar        |
| 2  | Tambah departemen    | Membuat departemen/divisi baru                        |
| 3  | Edit departemen      | Mengubah nama departemen                              |
| 4  | Hapus departemen     | Menghapus departemen (hanya jika tidak ada pegawai aktif di dalamnya) |

---

## 7. Pengaturan Sistem (Admin)

Halaman khusus Admin untuk mengkonfigurasi perilaku sistem tanpa perlu mengubah kode.

### 7.1 Pengaturan Jadwal Reminder

| No | Fitur                         | Deskripsi                                              |
|----|-------------------------------|--------------------------------------------------------|
| 1  | Lihat jadwal reminder aktif   | Menampilkan daftar H-X yang sedang aktif               |
| 2  | Tambah jadwal reminder        | Menambah hari reminder baru (misal H-90)               |
| 3  | Hapus jadwal reminder         | Menghapus salah satu jadwal reminder                   |
| 4  | Aktifkan/nonaktifkan jadwal   | Toggle on/off per jadwal tanpa menghapusnya            |

### 7.2 Template Email

| No | Fitur                  | Deskripsi                                                        |
|----|------------------------|------------------------------------------------------------------|
| 1  | Lihat template email   | Menampilkan template email yang sedang digunakan                 |
| 2  | Edit template email    | Mengubah isi email dengan rich text editor                       |
| 3  | Lihat placeholder      | Panduan placeholder yang tersedia (`{Nama Pegawai}`, dll)        |
| 4  | Preview email          | Melihat tampilan email dengan data contoh sebelum disimpan       |

### 7.3 Pengaturan Upload File

| No | Fitur                      | Deskripsi                                                    |
|----|----------------------------|--------------------------------------------------------------|
| 1  | Batas ukuran file          | Admin set ukuran maksimum file upload (default: 5 MB)        |
| 2  | Tipe file yang diizinkan   | Admin pilih format yang boleh diupload (PDF, JPG, PNG, dll)  |

---

## 8. Audit Trail (Log Aktivitas)

Sistem mencatat setiap aktivitas penting untuk keperluan compliance dan keamanan.

### 8.1 Aktivitas yang Dicatat

| No | Aktivitas                         | Keterangan                                          |
|----|-----------------------------------|-----------------------------------------------------|
| 1  | Upload dokumen baru               | Siapa, dokumen apa, kapan                           |
| 2  | Edit data dokumen                 | Siapa, dokumen apa, field apa yang berubah, kapan   |
| 3  | Hapus dokumen                     | Siapa, dokumen apa, kapan                           |
| 4  | Approve / Tolak dokumen           | Siapa admin, dokumen apa, kapan, alasan jika ditolak|
| 5  | Ganti file dokumen                | Siapa, dokumen apa, kapan                           |
| 6  | Tambah/edit/hapus pegawai         | Siapa admin, pegawai mana, kapan                    |
| 7  | Login & logout                    | Siapa, kapan, dari IP berapa                        |

### 8.2 Tampilan Audit Trail

| No | Fitur                    | Deskripsi                                                   |
|----|--------------------------|-------------------------------------------------------------|
| 1  | Lihat log aktivitas      | Tabel log seluruh aktivitas (hanya Admin)                   |
| 2  | Filter by aktor          | Filter berdasarkan siapa yang melakukan aksi                |
| 3  | Filter by tipe aktivitas | Filter berdasarkan jenis aksi (upload, edit, hapus, dll)    |
| 4  | Filter by tanggal        | Filter berdasarkan rentang tanggal                          |
| 5  | Filter by dokumen/pegawai| Lihat semua log untuk satu dokumen atau satu pegawai        |

---

## 9. Flow Utama Aplikasi

### 9.1 Flow Registrasi & Login
```
User membuka aplikasi
  |
  |--> Halaman Login
  |      |--> Masukkan email & password
  |      |--> Sistem validasi
  |      |--> Jika Admin --> Redirect ke Admin Dashboard
  |      |--> Jika Pegawai --> Redirect ke Pegawai Dashboard
  |
  |--> Lupa Password
  |      |--> Masukkan email
  |      |--> Sistem kirim link reset ke email
  |      |--> Klik link --> Form password baru --> Simpan
  |
  |--> (Pegawai didaftarkan oleh Admin, tidak ada self-register)
```

### 9.2 Flow Admin Lengkap
```
Admin Login
  |
  |--> Dashboard Admin
  |      |--> Lihat ringkasan statistik
  |      |--> Quick access ke dokumen bermasalah & pending approval
  |
  |--> Menu Pegawai
  |      |--> Lihat daftar pegawai (filter by departemen)
  |      |--> Tambah pegawai baru
  |      |--> Edit data pegawai
  |      |--> Nonaktifkan / Hapus pegawai
  |      |--> Reset password pegawai
  |
  |--> Menu Dokumen
  |      |--> Lihat semua dokumen
  |      |--> CRUD dokumen
  |      |--> Filter & search (status, kategori, departemen, nama)
  |      |--> Upload file
  |      |--> Approve / Tolak dokumen pending
  |
  |--> Menu Departemen
  |      |--> Kelola master data departemen/divisi
  |
  |--> Menu Pengaturan Kategori
  |      |--> Kelola jenis sertifikasi HSSE
  |      |--> Kelola jenis sertifikasi Aviasi
  |
  |--> Menu Notifikasi
  |      |--> Lihat riwayat notifikasi
  |      |--> Lihat dokumen yang akan/sudah expired
  |
  |--> Menu Pengaturan Sistem
  |      |--> Kelola jadwal reminder
  |      |--> Edit template email
  |      |--> Atur batas & tipe file upload
  |
  |--> Menu Manajemen Admin
  |      |--> Kelola akun admin lain
  |
  |--> Menu Audit Trail
         |--> Lihat log aktivitas seluruh sistem
```

### 9.3 Flow Pegawai Lengkap
```
Pegawai Login
  |
  |--> Dashboard Pegawai
  |      |--> Lihat ringkasan dokumen pribadi
  |      |--> Lihat notifikasi terbaru
  |
  |--> Menu Dokumen Saya
  |      |--> Lihat daftar dokumen (filter by status)
  |      |--> Upload dokumen baru --> Pending Approval
  |      |--> Edit dokumen --> Re-trigger Pending Approval
  |      |--> Hapus/ganti dokumen
  |      |--> Download dokumen
  |      |--> Lihat status & alasan jika ditolak
  |
  |--> Menu Notifikasi
  |      |--> Lihat semua reminder
  |      |--> Tandai sudah dibaca
  |
  |--> Profil Saya
         |--> Edit nomor HP & foto profil
         |--> Ganti password
```

---

## 10. Aturan Bisnis (Business Rules)

| No | Aturan                                                                                         |
|----|------------------------------------------------------------------------------------------------|
| 1  | Pegawai hanya bisa melihat dan mengelola dokumen miliknya sendiri                              |
| 2  | Admin bisa melihat dan mengelola dokumen seluruh pegawai                                       |
| 3  | Status dokumen (Aktif/Segera Expired/Expired) hanya berlaku setelah dokumen disetujui admin    |
| 4  | Dokumen yang baru diupload pegawai selalu berstatus "Pending Approval"                         |
| 5  | Dokumen yang diedit atau diganti file-nya oleh pegawai kembali ke "Pending Approval"           |
| 6  | Dokumen yang diinput langsung oleh Admin langsung aktif (tidak perlu approval)                 |
| 7  | Reminder hanya dikirim untuk dokumen berstatus Aktif atau Segera Expired (sudah approved)      |
| 8  | Jadwal reminder dapat dikonfigurasi admin; perubahan berlaku untuk reminder berikutnya          |
| 9  | Pegawai didaftarkan oleh Admin; tidak ada registrasi mandiri                                   |
| 10 | Kategori utama (HSSE & Aviasi) bersifat tetap, tidak bisa dihapus                             |
| 11 | Jenis sertifikasi di bawah kategori utama bisa ditambah/diubah/dihapus oleh Admin             |
| 12 | Departemen hanya bisa dihapus jika tidak ada pegawai aktif yang tergabung                     |
| 13 | Seluruh aktivitas CRUD dokumen dicatat dalam audit trail                                       |
| 14 | File yang diupload harus sesuai tipe dan ukuran yang dikonfigurasi admin                       |
| 15 | Email dan nomor HP pegawai harus valid untuk pengiriman notifikasi                             |

---

## 11. Perkiraan Halaman/Screen

| No | Halaman                        | Akses         |
|----|--------------------------------|---------------|
| 1  | Login                          | Semua         |
| 2  | Lupa Password / Reset Password | Semua         |
| 3  | Dashboard Admin                | Admin         |
| 4  | Dashboard Pegawai              | Pegawai       |
| 5  | Daftar Semua Dokumen           | Admin         |
| 6  | Daftar Dokumen Saya            | Pegawai       |
| 7  | Detail Dokumen                 | Admin/Pegawai |
| 8  | Form Tambah/Edit Dokumen       | Admin/Pegawai |
| 9  | Halaman Approval Dokumen       | Admin         |
| 10 | Daftar Pegawai                 | Admin         |
| 11 | Form Tambah/Edit Pegawai       | Admin         |
| 12 | Daftar Departemen              | Admin         |
| 13 | Pengaturan Kategori Sertifikasi| Admin         |
| 14 | Pengaturan Sistem              | Admin         |
| 15 | Manajemen Admin                | Admin         |
| 16 | Audit Trail / Log Aktivitas    | Admin         |
| 17 | Halaman Notifikasi             | Admin/Pegawai |
| 18 | Profil Pegawai                 | Pegawai       |
| 19 | Ganti Password                 | Admin/Pegawai |

---

## 12. Ide & Catatan Brainstorming

### 12.1 Fitur Tambahan yang Bisa Dipertimbangkan
- **Export laporan** — Admin bisa export daftar dokumen ke Excel/PDF untuk pelaporan
- **Bulk upload** — Admin bisa upload banyak dokumen sekaligus via template Excel
- **WhatsApp notification** — Selain email, reminder juga dikirim via WhatsApp
- **Calendar view** — Tampilan kalender untuk melihat jadwal expired dokumen
- **Color-coded timeline** — Visualisasi timeline dokumen dengan warna sesuai urgensi
- **Statistik tren** — Grafik tren dokumen expired per bulan/kuartal
- **Role Supervisor** — Atasan bisa memantau dokumen pegawai di bawahnya (field supervisor sudah bisa ditambah ke data pegawai jika dibutuhkan)

### 12.2 Pertanyaan yang Sudah Dijawab
| Pertanyaan | Keputusan |
|---|---|
| Apakah pegawai perlu approval admin setelah upload? | **Ya** — upload pegawai selalu masuk Pending Approval |
| Apakah jadwal reminder bisa diubah dari UI? | **Ya** — melalui halaman Pengaturan Sistem |
| Apakah perlu departemen dari awal? | **Ya** — master data departemen dan filter tersedia dari awal |
| Apakah perlu audit trail? | **Ya** — dicatat untuk semua aktivitas CRUD dokumen |
| Apakah perlu riwayat versi dokumen? | **Tidak** — upload baru menggantikan yang lama |
| Apakah perlu multi-admin? | **Ya** — ada halaman manajemen admin terpisah |
| Apakah perlu field supervisor di data pegawai? | **Tidak sekarang** — bisa ditambah nanti jika role Supervisor diimplementasi |

### 12.3 Pertanyaan yang Belum Dibahas
1. Berapa batas ukuran file upload default yang diinginkan?
2. Format file apa saja yang akan diizinkan (PDF saja, atau termasuk JPG/PNG)?
3. Apakah reminder juga perlu dikirim setelah dokumen expired (misal H+7, H+30)?
4. Apakah satu pegawai bisa memiliki lebih dari satu dokumen dengan jenis yang sama?
5. Apakah perlu fitur cetak/print sertifikat dari sistem?

---

*Versi 1.1 — Dokumen ini belum mencakup aspek teknikal seperti database schema, API design, atau infrastruktur server.*
