# Panduan Urutan Prompt untuk AI Agent (Step-by-Step Rebuild Guide)

**Nama Proyek:** Sistem Inventaris Barang Perpustakaan (V2)  
**Lokasi Direktori Baru:** `c:\xampp\htdocs\inventaris_barang_perpus`  
**Nama Database Baru:** `inventaris_barang_perpus`  
**Dokumen Referensi Utama:** `prd.md`  

---

## 📌 Aturan Emas Sebelum Memulai (PENTING!)

> [!WARNING]
> Jangan meminta AI Agent mengerjakan seluruh proyek dalam **1 kali prompt**!  
> Jika diberikan sekaligus, model AI akan menghasilkan kode yang terpotong (*stub code* / `// TODO`), rentan halusinasi, dan bisa merusak database.  
> **Gunakan metode bertahap (per tahap):** Berikan Prompt Tahap 1, tunggu sampai AI selesai dan lakukan pengetesan, lalu baru lanjutkan ke Prompt Tahap berikutnya.

### Persiapan Awal oleh Anda:
1. Pastikan folder `c:\xampp\htdocs\inventaris_barang_perpus` sudah dibuat (sudah di-*install* Laravel bersih di dalamnya).
2. Salin (*copy*) file `prd.md` dari folder lama ke dalam folder baru `c:\xampp\htdocs\inventaris_barang_perpus\prd.md`.
3. Buka folder `inventaris_barang_perpus` di editor Anda (VS Code / Antigravity / Cursor).

---

## 🚀 Urutan Prompt ke AI Agent (Tinggal Salin & Tempel)

---

### 🟢 PROMPT TAHAP 1: Konfigurasi Lingkungan & Pengecekan Database
*Tujuan: Memastikan AI Agent terhubung ke database `inventaris_barang_perpus` tanpa merusak data yang sudah ada.*

```text
Halo Agent, kita akan membangun ulang (rebuild) aplikasi web Inventaris Barang Perpustakaan dari nol menggunakan Laravel di folder ini.

DOKUMEN RUJUKAN:
Baca dan jadikan panduan utama file `prd.md` di root folder ini. pahami juga file `agents.md`

PENTING — ATURAN DATABASE:
1. Database untuk proyek ini bernama `inventaris_barang_perpus`. Database ini sudah berisi data riil (730 baris data barang di tabel items, 27 staf, 11 lokasi, 5 kategori).
2. DILARANG KERAS menjalankan `php artisan migrate:fresh` atau `migrate:reset` karena akan menghapus seluruh data yang sudah ada!
3. Seluruh perubahan skema nanti hanya boleh lewat file migration baru.

TUGAS TAHAP 1:
1. Konfigurasikan file `.env`:
   - DB_CONNECTION=mysql
   - DB_HOST=127.0.0.1
   - DB_PORT=3306
   - DB_DATABASE=inventaris_barang_perpus
   - DB_USERNAME=root
   - DB_PASSWORD=
   - APP_TIMEZONE=Asia/Jakarta
2. Jalankan skrip pengecekan singkat untuk memverifikasi bahwa koneksi database berhasil dan hitung jumlah baris di tabel `items`, `staff`, `locations`, `categories`, dan `users`.
3. Laporkan hasilnya kepadaku sebelum kita lanjut ke pembuatan skema.
```

---

### 🟢 PROMPT TAHAP 2: Migrasi Skema Baru & Transformasi Data Eksisting (Zero Data Loss)
*Tujuan: Menyesuaikan skema tabel agar memisahkan Katalog vs Unit Fisik dan Detail Transaksi tanpa menghilangkan 730 item.*

```text
Koneksi database sudah diverifikasi. Sekarang kita masuk ke TAHAP 2: Penyesuaian Skema Database.

INGAT: JANGAN JALANKAN migrate:fresh!

TUGAS TAHAP 2:
Sesuai instruksi di PRD Bagian 7 dan Bagian 12.2:
1. Buat file migration baru untuk membuat tabel `items_catalog` (kolom: id, category_id, name, brand, specification, unit_name, photo, description, is_active, timestamps).
2. Buat migration baru untuk menambahkan kolom `catalog_id` ke tabel `items` (unit fisik), lalu di dalam method `up()` migration tersebut, sertakan skrip data migration otomatis yang mengelompokkan 730 data unit di tabel `items` ke `items_catalog`, lalu isi foreign key `catalog_id` pada setiap unit fisik tersebut.
3. Buat migration baru untuk:
   - Menambahkan kolom `transaction_code`, `expected_return_date`, `rejection_reason`, dan `staff_id` ke tabel `transactions`.
   - Membuat tabel `transaction_details` (kolom: id, transaction_id, item_id, from_location_id, to_location_id, condition_before, condition_after, is_returned, returned_at, timestamps).
4. Jalankan `php artisan migrate` biasa.
5. Verifikasi bahwa 730 unit di tabel `items` sekarang semuanya sudah memiliki `catalog_id` yang valid dan tidak ada data yang hilang.
```

---

### 🟢 PROMPT TAHAP 3: Pembuatan Enum & Model Eloquent Lengkap dengan Relasi
*Tujuan: Menyediakan fondasi model data Laravel yang type-safe dan sesuai konvensi.*

```text
Skema database sudah siap. Sekarang kita masuk ke TAHAP 3: Pembuatan Enums dan Model Eloquent.

TUGAS TAHAP 3:
Sesuai PRD Bagian 12.1 dan 12.3:
1. Buat file PHP Backed Enums di folder `app/Enums/`:
   - `ItemCondition.php` ('Baik', 'Rusak', 'Perlu Perbaikan')
   - `ItemUnitStatus.php` ('Tersedia', 'Dipinjam', 'Dalam Perbaikan', 'Hilang', 'Nonaktif')
   - `TransactionType.php` ('borrow', 'return', 'transfer', 'maintenance')
   - `TransactionStatus.php` ('Menunggu Persetujuan', 'Dipinjam', 'Sebagian Dikembalikan', 'Dikembalikan', 'Ditolak', 'Dibatalkan', 'Selesai')
   - `UserRole.php` ('admin', 'pegawai')
2. Buat/perbarui Model Eloquent di `app/Models/` lengkap dengan `$fillable`, `$casts`, dan method relasinya:
   - `ItemCatalog`: relasi ke `category`, `units` (hasMany ke Item), dan accessor `available_units_count`.
   - `Item` (mewakili unit fisik): relasi ke `catalog`, `location`, `transactionDetails`.
   - `Transaction`: relasi ke `details`, `staff`, `user` (creator), `approver`, `parentTransaction`, `returnTransactions`.
   - `TransactionDetail`: relasi ke `transaction`, `item`, `fromLocation`, `toLocation`.
   - `Category`, `Location`, `Staff`, `User`.
3. Buat uji singkat via Tinker atau unit test sederhana untuk memastikan relasi antar model dapat dipanggil tanpa error.
```

---

### 🟢 PROMPT TAHAP 4: Layout UI Responsif, Autentikasi & Middleware Role
*Tujuan: Menyediakan halaman login, layout navigasi desktop & ponsel, serta proteksi hak akses.*

```text
Model data sudah siap. Sekarang kita masuk ke TAHAP 4: Autentikasi, Middleware Role, dan Shell Layout UI.

TUGAS TAHAP 4:
Sesuai PRD Bagian 3, 4, dan 8:
1. Setup Autentikasi:
   - Login menggunakan `username` dan `password` (bukan email).
   - Fitur "Ingat Saya" (Remember me).
   - Rate limiting dan pesan error umum tanpa membocorkan eksistensi akun.
   - Sediakan tombol submit dengan loading state agar tidak terjadi double-click.
2. Buat Middleware Otorisasi:
   - Middleware `role:admin` dan `role:pegawai`.
   - Pastikan rute yang diakses diuji di server, bukan hanya menyembunyikan menu di UI.
3. Buat Layout Antarmuka Utama (Blade):
   - Sidebar desktop yang bersih dan modern (dapat diciutkan).
   - Drawer menu navigasi pada tampilan layar ponsel (ramah layar 360 px, tanpa horizontal scroll).
   - Header yang menampilkan nama user yang sedang login, peran (Admin/Pegawai), dan tombol keluar (Logout).
   - Penanganan notifikasi toast/alert (success, error, warning).
```

---

### 🟢 PROMPT TAHAP 5: Modul Manajemen Master Data (Admin Only)
*Tujuan: Membangun CRUD Kategori, Lokasi, Staf Perpustakaan, dan Akun Pengguna.*

```text
Layout dan otentikasi sudah berjalan. Sekarang kita masuk ke TAHAP 5: Modul Master Data untuk Admin.

TUGAS TAHAP 5:
1. Buat CRUD untuk:
   - Kategori Barang (`categories`)
   - Lokasi Ruangan / Rak (`locations`)
   - Staf Perpustakaan (`staff`) — penanggung jawab operasional fisik
   - Pengguna Sistem (`users`) — akun login admin & pegawai
2. ATURAN INTEGRITAS (PRD Bagian 5 & 12):
   - Jangan gunakan hard delete jika data master sudah memiliki riwayat pemakaian. Gunakan mekanisme nonaktifkan / arsip (`is_active = false`).
   - Sediakan filter status aktif/nonaktif dan pencarian pada setiap tabel master data.
   - Gunakan `FormRequest` terpisah untuk validasi data input.
```

---

### 🟢 PROMPT TAHAP 6: Modul Katalog & Unit Inventaris (Dua Tingkat)
*Tujuan: Menampilkan katalog jenis barang dan rincian unit fisik berkode serial.*

```text
Master data sudah selesai. Sekarang kita masuk ke TAHAP 6: Manajemen Katalog & Unit Inventaris Fisik.

TUGAS TAHAP 6:
Sesuai PRD Bagian 5.2:
1. Halaman Daftar Inventaris:
   - Tampilan Tingkat 1 (Katalog Barang): Menampilkan foto, nama barang, merek, kategori, spesifikasi ringkas, total unit fisik, dan badge jumlah unit 'Tersedia'.
   - Fitur pencarian instan (nama barang, merek, atau kode unit fisik) dan filter (kategori, lokasi, kondisi fisik, ketersediaan).
   - Paginasi 15 data yang tetap mempertahankan parameter filter (`appends($request->query())`).
2. Halaman Detail Katalog (Tingkat 2):
   - Menampilkan daftar seluruh unit fisik (`item_units`) milik katalog tersebut, lengkap dengan `kode_unit`, lokasi rak saat ini, kondisi (Baik, Rusak, Perlu Perbaikan), dan status ketersediaan (Tersedia, Dipinjam, Dalam Perbaikan).
3. Formulir Tambah & Edit:
   - Admin dapat menambah katalog baru beserta langsung mengenerate sejumlah unit fisik berkode unik.
   - Pengarsipan unit fisik/katalog jika sudah memiliki transaksi (tidak boleh hard delete).
```

---

### 🟢 PROMPT TAHAP 7: Alur Transaksi Inti (Peminjaman & Persetujuan)
*Tujuan: Membangun form peminjaman pegawai (model kuantitas) dan approval admin (alokasi unit).*

```text
Modul inventaris sudah siap. Sekarang kita masuk ke TAHAP 7: Alur Transaksi Peminjaman & Persetujuan (Jantung Aplikasi).

TUGAS TAHAP 7:
Sesuai PRD Bagian 5.3, 5.4, dan 12.4:
1. Form Pengajuan Pinjam (Pegawai & Admin):
   - Pegawai memilih barang dari katalog dan memasukkan jumlah kuantitas yang dibutuhkan (tidak dipaksa memilih kode serial satu per satu).
   - Memilih Staf peminjam, Lokasi tujuan, Tanggal pinjam, dan **Estimasi Tanggal Pengembalian (expected_return_date)**.
   - Transaksi disimpan dengan status `Menunggu Persetujuan` dan BELUM memotong stok unit fisik.
   - Peminjam dapat membatalkan permintaannya sendiri selama statusnya masih `Menunggu Persetujuan`.
2. Antrean Persetujuan (Khusus Admin):
   - Halaman daftar permintaan pinjaman yang menunggu verifikasi.
   - Admin membuka detail permintaan: Admin memilih/mengalokasikan kode unit fisik yang berstatus 'Tersedia' dan kondisi 'Baik' sejumlah kuantitas yang disetujui.
   - **Tombol Setujui (Approve):** Wajib menggunakan `DB::transaction()` dan `lockForUpdate()`. Status transaksi menjadi `Dipinjam`, unit fisik yang dialokasikan diubah menjadi `Dipinjam`, dan baris `transaction_details` dibuat.
   - **Tombol Tolak (Reject):** Admin wajib mengisi alasan penolakan (`rejection_reason`). Status transaksi menjadi `Ditolak`.
```

---

### 🟢 PROMPT TAHAP 8: Alur Pengembalian (Parsial & Penuh), Mutasi Massal, dan Pemeliharaan
*Tujuan: Mengakomodasi partial return, batch transfer, dan maintenance lifecycle.*

```text
Alur peminjaman sudah berfungsi. Sekarang kita masuk ke TAHAP 8: Pengembalian, Perpindahan Massal, dan Pemeliharaan Barang.

TUGAS TAHAP 8:
Sesuai PRD Bagian 5.5, 5.6, 5.7, dan 5.8:
1. Alur Pengembalian Barang:
   - Pilih transaksi pinjaman aktif (`Dipinjam` atau `Sebagian Dikembalikan`).
   - **Dukungan Partial Return:** Pengguna dapat mencentang seluruh unit atau hanya sebagian unit yang ingin dikembalikan.
   - Pemeriksaan kondisi fisik per unit: Jika 'Baik' $\rightarrow$ unit kembali 'Tersedia'. Jika 'Rusak' / 'Perlu Perbaikan' $\rightarrow$ unit berstatus 'Rusak' (tidak bisa dipinjam).
   - Jika semua unit sudah kembali $\rightarrow$ status transaksi menjadi `Dikembalikan`. Jika baru sebagian $\rightarrow$ status transaksi menjadi `Sebagian Dikembalikan`.
2. Alur Mutasi Massal (Batch Transfer - Khusus Admin):
   - Admin memilih lokasi asal, mencentang beberapa unit fisik sekaligus, lalu memilih lokasi tujuan.
   - Mencatat 1 transaksi mutasi dengan banyak detail unit, dan lokasi unit diperbarui.
3. Alur Pemeliharaan Barang Rusak (Maintenance):
   - Menu daftar unit yang berstatus 'Rusak' atau 'Perlu Perbaikan'.
   - Admin dapat mencatat unit 'Dalam Perbaikan' dan mengembalikannya ke status 'Tersedia' setelah perbaikan selesai.
4. **INTEGRITAS AUDIT:** DILARANG menyediakan tombol Hapus (*Delete*) untuk transaksi yang berstatus selesai.
```

---

### 🟢 PROMPT TAHAP 9: Dashboard Manajerial, Overdue Alert, dan Ekspor CSV
*Tujuan: Membangun dashboard analitik dan fitur ekspor laporan.*

```text
Alur transaksi sudah lengkap. Sekarang kita masuk ke TAHAP 9: Dashboard Berbasis Peran & Ekspor Laporan.

TUGAS TAHAP 9:
Sesuai PRD Bagian 6:
1. Dashboard Admin:
   - Ringkasan kartu metrik: Total Unit Aset, Unit Tersedia, Unit Dipinjam, Unit Rusak.
   - **Kartu Peringatan Terlambat (Overdue Alert):** Menghitung transaksi `Dipinjam` yang tanggal saat ini telah melewati `expected_return_date`.
   - Banner prioritas antrean persetujuan yang menunggu tindakan.
   - Peringatan stok menipis (katalog dengan sisa unit tersedia <= 5).
   - Tabel 5 transaksi terbaru.
2. Dashboard Pegawai:
   - Kartu metrik: Pinjaman Aktif Saya, Permintaan Menunggu Saya, Barang yang Jatuh Tempo Minggu Ini.
   - Tabel riwayat transaksi akun pegawai tersebut.
3. Fitur Ekspor CSV:
   - Ekspor data inventaris dan ekspor data riwayat transaksi yang mematuhi filter dan pencarian aktif.
```

---

### 🟢 PROMPT TAHAP 10: Pengujian Responsif Mobile 360px & Edge Cases Verification
*Tujuan: Memastikan aplikasi bebas bug konkurensi dan responsif di ponsel.*

```text
Fitur utama sudah selesai. Sekarang kita masuk ke TAHAP 10: Pengujian Akhir, Polish UI Ponsel, dan Skenario Edge Case.

TUGAS TAHAP 10:
1. Uji Responsif Layar Kecil (360 x 640 px):
   - Pastikan navigasi drawer berfungsi mulus.
   - Pastikan tabel data inventaris dan transaksi tampil dalam bentuk kartu ringkas (*card list*) yang rapi tanpa ada overflow horizontal pada body halaman.
2. Uji Skenario Konkurensi & Integritas:
   - Uji tombol submit pada form pinjam dan persetujuan: pastikan tidak terjadi pengiriman ganda saat tombol diklik cepat.
   - Uji jika stok barang 0: pastikan sistem menolak pengajuan pinjam.
   - Uji pengembalian bertahap: pastikan status unit yang belum dikembalikan tetap 'Dipinjam' dan sisa unit dapat dikembalikan pada penyerahan berikutnya.
   - Pastikan total unit di database tetap persis 730 unit dan tidak ada data yang terkorupsi.
```

---

## 📋 Checklist Evaluasi Hasil Akhir untuk Anda

Sebelum menganggap pekerjaan AI Agent selesai, periksa hal-hal berikut di browser Anda:

- [ ] **Koneksi Database:** Database yang dipakai adalah `inventaris_barang_perpus` dan seluruh 730 unit fisik lama terbaca lengkap.
- [ ] **Multi-Role Login:** Bisa login sebagai `admin` dan sebagai `pegawai` dengan hak akses menu yang berbeda.
- [ ] **Pengajuan Pinjam:** Pegawai hanya mengisi kuantitas tanpa dipaksa memilih kode serial unit fisik.
- [ ] **Persetujuan Admin:** Admin yang memilih nomor serial fisik saat menyetujui peminjaman.
- [ ] **Pengembalian Bertahap (*Partial Return*):** Pinjaman 2 unit bisa dikembalikan 1 unit terlebih dahulu.
- [ ] **Overdue Alert:** Transaksi yang melewati tanggal rencana kembali ditandai warna merah di dashboard.
- [ ] **Tidak Ada Tombol Hapus:** Riwayat transaksi selesai tidak bisa dihapus dari UI.
- [ ] **Tampilan Ponsel:** Aplikasi bisa dibuka di Google Chrome mode inspect (lebar 360 px) dengan nyaman.

