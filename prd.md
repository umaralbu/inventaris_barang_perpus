# Product Requirement Document (PRD) — Sistem Inventaris Barang Perpustakaan (V2 - Redesign)

**Dokumen Versi:** 2.0 (Revisi Komprehensif Berdasarkan Review Senior Developer)  
**Status:** Disetujui untuk Desain UI & Implementasi Engineering  
**Produk:** Aplikasi Web Internal Manajemen Aset & Inventaris Perpustakaan  
**Audiens:** Tim Desain UI/UX (Figcraft), Tim Pengembang Web (Fullstack Laravel), & Product Owner  
**Bahasa Antarmuka:** Bahasa Indonesia  

---

## 1. Ringkasan Eksekutif & Latar Belakang

Sistem Inventaris Barang Perpustakaan adalah aplikasi web internal yang digunakan untuk mengelola seluruh aset fisik perpustakaan (buku khusus, perangkat elektronik, proyektor, mebel, alat tulis kantor, dsb.), mencatat siklus hidup barang, melayani permohonan peminjaman oleh pegawai, memproses persetujuan, mencatat pengembalian (termasuk pengembalian bertahap), serta mendokumentasikan pemindahan lokasi dan pemeliharaan fisik barang.

PRD versi 2.0 ini menyempurnakan draf sebelumnya (`context.md`) dengan mengintegrasikan hasil analisis arsitektur pada `issue.md`. Fokus utama perbaikan adalah:
1. **Pemisahan Peran Request vs Fulfillment:** Pegawai mengajukan barang berdasarkan jumlah kebutuhan tanpa dipaksa memilih kode unit serial di awal, sementara Admin mengalokasikan unit fisik saat persetujuan.
2. **Dukungan Pengembalian Bertahap (*Partial Return*):** Transaksi dapat dikembalikan sebagian tanpa merusak pelacakan unit yang masih dipinjam.
3. **Integritas Jejak Audit (*Immutable Audit Trail*):** Menghilangkan opsi penghapusan riwayat transaksi selesai guna kepatuhan audit aset.
4. **Alur Pemeliharaan Barang Rusak:** Menutup celah siklus hidup barang yang rusak/perlu perbaikan agar memiliki prosedur perbaikan dan pemulihan status.
5. **Estimasi Pengembalian (*Expected Return Date*):** Memantau aset yang menunggak (*overdue*) secara visual di dashboard.
6. **Perpindahan Lokasi Massal (*Batch Transfer*):** Memudahkan pemindahan beberapa unit sekaligus dalam satu nomor transaksi mutasi.

---

## 2. Masalah yang Diselesaikan

| Masalah Sebelumnya | Dampak Negatif | Solusi pada Versi 2.0 |
| :--- | :--- | :--- |
| **Pemilihan serial unit di formulir pegawai** | Beban input tinggi; rawan tabrakan pemilihan kode unit yang sama oleh 2 peminjam (*race condition*). | Pegawai hanya mengajukan **katalog & jumlah unit**. Admin menetapkan kode unit fisik saat barang diserahkan (*Fulfillment*). |
| **Tidak ada pengembalian parsial** | Jika meminjam 5 unit dan 2 selesai lebih awal, peminjam harus menunggu kelimanya selesai atau data transaksi rusak. | Struktur transaksi menggunakan relasi **Header-Detail**. Status pelacakan berada pada tingkat unit fisik. |
| **Status "Dipindahkan" bersifat permanen** | Barang yang dipindah ruangan kehilangan status ketersediaannya. | Perpindahan adalah **kejadian/mutasi**. Status ketersediaan barang tetap *Tersedia*. |
| **Ada fitur hapus transaksi selesai** | Menghilangkan jejak historis pemegang aset, menyulitkan audit saat terjadi kehilangan. | Transaksi yang sudah selesai bersifat **permanen (*immutable*)**. Hanya transaksi *draft/pending* yang boleh dibatalkan. |
| **Tidak ada estimasi tanggal kembali** | Barang dipinjam tanpa batas waktu; admin tidak tahu barang mana yang menunggak. | Menambahkan kolom `tanggal_rencana_kembali` dan kartu *Overdue Alert* di dashboard. |
| **Barang rusak menjadi data mati** | Barang yang dikembalikan dalam kondisi rusak menggantung tanpa alur perbaikan. | Disediakan alur status perbaikan (*Dalam Perbaikan* -> *Selesai Diperbaiki / Tersedia*). |

---

## 3. Pengguna dan Matriks Hak Akses (RBAC)

Sistem membedakan dua entitas pengguna:
1. **Akun Sistem (`users`):** Digunakan untuk masuk (*login*) ke aplikasi berbasis peran (*role*).
2. **Staf Operasional (`staff`):** Pegawai/karyawan fisik yang memegang atau bertanggung jawab atas barang di dunia nyata. Identitas akun login mencatat **siapa yang menginput sistem**, sedangkan staf mencatat **siapa penanggung jawab fisik barang**.

### Matriks Akses Fitur:

| Modul / Tindakan | Administrator | Pegawai | Catatan Aturan Otorisasi |
| :--- | :---: | :---: | :--- |
| **Masuk & Profil Akun** | Ya | Ya | Login menggunakan `username` & `password`. |
| **Lihat Dashboard** | Ya (Tampilan Manajerial) | Ya (Tampilan Personal) | Pegawai hanya melihat data pinjaman miliknya. |
| **Lihat & Cari Inventaris** | Ya | Ya | Pencarian berdasarkan nama, kategori, merek, kode unit, dan lokasi. |
| **Kelola Master Barang & Unit** | Ya | Tidak | Tambah/Edit/Arsipkan barang dan kode unit fisik. |
| **Ajukan Peminjaman** | Ya | Ya | Menghasilkan transaksi berstatus *Menunggu Persetujuan*. |
| **Persetujuan & Penolakan Pinjaman** | Ya | Tidak | Admin mengalokasikan unit fisik saat menyetujui. |
| **Catat Pengembalian Barang** | Ya | Ya | Dapat mengembalikan seluruh atau sebagian unit. |
| **Catat Pemeliharaan / Perbaikan** | Ya | Tidak | Mengubah status unit *Rusak* -> *Dalam Perbaikan* -> *Tersedia*. |
| **Catat Perpindahan Lokasi** | Ya | Tidak | Mendukung mutasi satu atau banyak unit sekaligus. |
| **Batalkan Transaksi Menunggu** | Ya | Ya (Milik Sendiri) | Mengubah status transaksi menjadi *Dibatalkan*. |
| **Hapus Transaksi Selesai** | **DILARANG** | **DILARANG** | Demi integritas audit trail, data riwayat tidak boleh dihapus. |
| **Kelola Kategori, Lokasi, Staf, User**| Ya | Tidak | Akses halaman master data dibatasi middleware di server. |
| **Ekspor Laporan (CSV/Excel)** | Ya | Ya (Riwayat Sendiri) | Mengikuti filter pencarian aktif. |

---

## 4. Arsitektur Informasi & Navigasi

Navigasi desktop menggunakan sidebar responsif yang dapat diciutkan (*collapsible*). Pada layar ponsel (lebar minimum 360 px), navigasi bertransformasi menjadi *drawer menu* tanpa scroll horizontal halaman.

```
├── Dashboard (Tampilan berbeda sesuai peran)
├── Transaksi
│   ├── Semua Riwayat Transaksi
│   ├── Antrean Persetujuan (Khusus Admin — Badge Jumlah Menunggu)
│   ├── Form Pengajuan Peminjaman (Pegawai & Admin)
│   ├── Form Pengembalian Barang (Pegawai & Admin)
│   ├── Form Mutasi / Perpindahan Lokasi (Khusus Admin)
│   └── Form Pemeliharaan / Servis Barang (Khusus Admin)
├── Inventaris
│   ├── Katalog & Unit Barang (Tampilan hierarki Jenis Barang -> Daftar Unit Fisik)
│   ├── Kategori Barang (Khusus Admin)
│   └── Lokasi Ruangan / Rak (Khusus Admin)
├── Pengelolaan Master Data (Khusus Admin)
│   ├── Staf Perpustakaan
│   └── Akun Pengguna (Users)
└── Pengaturan Akun & Keluar (Logout)
```

---

## 5. Spesifikasi Alur Kerja Utama (User Workflows)

### 5.1 Alur Autentikasi & Masuk
1. Pengguna memasukkan `username` dan `password`.
2. Opsi "Ingat Saya" (*Remember Me*) tersedia.
3. Tombol submit memiliki *loading state* untuk mencegah pengiriman formulir ganda (*double submission*).
4. Jika gagal, tampilkan pesan kesalahan umum: *"Username atau password salah"* tanpa membocorkan eksistensi akun. Nilai input username tetap dipertahankan.
5. Pengguna diarahkan ke Dashboard sesuai perannya (Admin diarahkan ke ringkasan manajerial; Pegawai diarahkan ke ringkasan pinjaman aktifnya).

---

### 5.2 Alur Penjelajahan & Manajemen Inventaris
1. **Tampilan Dua Tingkat (Katalog & Unit):**
   - **Tingkat 1 (Katalog/Jenis Barang):** Menampilkan nama barang, merek, kategori, spesifikasi, foto, total unit fisik, dan jumlah unit yang sedang *Tersedia*.
   - **Tingkat 2 (Daftar Unit Fisik):** Saat baris katalog dibuka, tampilkan daftar unit berkode serial fisik (`kode_unit`), lokasi ruangan saat ini, kondisi fisik (Baik, Rusak, Perlu Perbaikan), dan status ketersediaan (Tersedia, Dipinjam, Dalam Perbaikan, Hilang).
2. **Pencarian & Filter:** Mendukung pencarian instan nama barang, kode unit, filter kategori, lokasi, kondisi, dan status ketersediaan. Filter dipertahankan saat paginasi dan dapat direset dalam satu klik.
3. **Pengarsipan Barang:** Tombol hapus pada barang yang sudah pernah memiliki transaksi berubah menjadi **Arsipkan / Nonaktifkan**. Riwayat transaksi masa lalu tetap utuh.

---

### 5.3 Alur Pengajuan Peminjaman (*Borrowing Request*)
*Alur ini telah disempurnakan agar ramah pengguna dan bebas dari konflik konkurensi data:*

1. **Langkah 1 (Pilih Barang & Kuantitas):**
   - Pegawai memilih jenis barang dari katalog (menampilkan nama, foto ringkas, dan jumlah stok tersedia saat ini).
   - Pegawai memasukkan jumlah unit yang dibutuhkan (validasi: `1 <= kuantitas <= stok_tersedia`).
   - *(Opsi Lanjutan):* Jika peminjam memerlukan unit dengan spesifikasi/kode khusus tertentu (misal: "Hanya Laptop unit No. 3"), disediakan opsi opsional untuk memilih kode serial. Jika tidak dicentang, sistem menyerahkan pemilihan serial kepada Admin saat persetujuan.
2. **Langkah 2 (Data Operasional Peminjaman):**
   - Pilih Staf Peminjam (dropdown staf aktif).
   - Pilih Lokasi Tujuan Penggunaan barang (dropdown lokasi aktif).
   - Tentukan **Tanggal Pinjam** dan **Estimasi Tanggal Pengembalian (*Expected Return Date*)**.
   - Masukkan Keperluan / Catatan peminjaman.
3. **Langkah 3 (Ringkasan & Pengiriman):**
   - Sistem menampilkan ringkasan pengajuan sebelum dikirim.
   - Setelah dikirim, sistem membuat transaksi berstatus **`Menunggu Persetujuan`**.
   - **Penting:** Permintaan yang menunggu persetujuan **belum memotong stok unit fisik**. Unit tetap berstatus *Tersedia*, namun antrean permintaan tercatat di sistem.

---

### 5.4 Alur Peninjauan & Persetujuan (*Approval & Unit Allocation*)
1. Admin membuka menu **Antrean Persetujuan** yang menampilkan daftar permohonan yang berstatus *Menunggu Persetujuan*.
2. Admin membuka detail permohonan. Sistem menampilkan ketersediaan stok fisik terkini.
3. **Alokasi Unit Fisik (*Fulfillment*):**
   - Jika pemohon tidak memilih serial khusus, sistem menampilkan pilihan/rekomendasi kode unit fisik yang berstatus *Tersedia* dan berkondisi *Baik*.
   - Admin memilih kode unit sejumlah kuantitas yang disetujui (atau menggunakan barcode scanner di masa depan).
4. **Keputusan Persetujuan:**
   - **Setujui (Approve):**
     - Dilakukan dalam transaksi database atomik dengan *database row-level locking* (`lockForUpdate`).
     - Sistem memvalidasi ulang bahwa seluruh unit yang dipilih masih berstatus *Tersedia*.
     - Status transaksi berubah menjadi **`Dipinjam`**.
     - Status setiap unit fisik yang dialokasikan berubah menjadi **`Dipinjam`**, dan lokasi unit diperbarui ke lokasi tujuan peminjaman.
     - Dibuat baris `detail_transaksi` untuk masing-masing unit fisik.
   - **Tolak (Reject):**
     - Admin wajib mengisi alasan penolakan (misal: "Stok dialokasikan untuk ujian", "Lokasi tidak memenuhi syarat").
     - Status transaksi berubah menjadi **`Ditolak`**. Tidak ada unit fisik yang berubah status.
   - **Batalkan (Cancel):**
     - Pegawai pemohon dapat membatalkan permintaannya sendiri selama statusnya masih *Menunggu Persetujuan*. Status berubah menjadi **`Dibatalkan`**.

---

### 5.5 Alur Pengembalian Barang (*Return Workflow & Partial Return*)
1. Pegawai atau Admin membuka formulir **Catat Pengembalian**.
2. Sistem menampilkan daftar peminjaman aktif yang berstatus **`Dipinjam`** atau **`Sebagian Dikembalikan`**.
3. **Dukungan Pengembalian Parsial (*Partial Return*):**
   - Setelah memilih transaksi peminjaman asal, sistem menampilkan daftar unit fisik yang masih dipinjam.
   - Pengguna dapat mencentang seluruh unit (pengembalian penuh) atau hanya sebagian unit (pengembalian bertahap).
4. **Pemeriksaan Kondisi Fisik per Unit:**
   - Untuk setiap unit yang dikembalikan, pengguna menentukan kondisi fisiknya:
     - **Baik:** Unit langsung kembali berstatus **`Tersedia`** dan siap dipinjam kembali.
     - **Rusak / Perlu Perbaikan:** Unit kembali ke gudang, tetapi berstatus **`Rusak`** atau **`Perlu Perbaikan`** (tidak dapat dipinjam kembali hingga diperbaiki).
5. **Pencatatan Transaksi:**
   - Pengguna memilih lokasi rak/ruangan pengembalian dan staf yang menyerahkan.
   - Sistem mencatat transaksi bertipe `return` yang mereferensikan transaksi peminjaman asal (`transaksi_asal_id`).
   - Jika seluruh unit telah kembali, status transaksi peminjaman asal berubah menjadi **`Dikembalikan`**.
   - Jika baru sebagian unit yang kembali, status transaksi peminjaman asal berubah menjadi **`Sebagian Dikembalikan`**, sehingga sisa unit tetap dapat ditagih/dikembalikan di kemudian hari.

---

### 5.6 Alur Pemeliharaan & Perbaikan Barang (*Maintenance Lifecycle*)
1. Unit barang yang berstatus *Rusak* atau *Perlu Perbaikan* masuk ke dalam daftar **Barang Butuh Perbaikan**.
2. Admin dapat mengubah status unit menjadi **`Dalam Perbaikan`** dengan mencatat vendor/teknisi dan tanggal mulai servis.
3. Setelah perbaikan selesai, Admin mencatat hasil perbaikan:
   - Jika kondisi berhasil diperbaiki -> kondisi menjadi *Baik*, status menjadi **`Tersedia`**.
   - Jika rusak permanen / tidak dapat diperbaiki -> status menjadi **`Dihapusbukukan / Rusak Total`** (diarsipkan).

---

### 5.7 Alur Perpindahan Lokasi / Mutasi (*Batch Transfer*)
1. Khusus Admin dapat mengakses formulir **Catat Perpindahan**.
2. **Dukungan Perpindahan Massal (*Batch Transfer*):**
   - Admin memilih lokasi asal.
   - Sistem menampilkan seluruh unit fisik yang berada di lokasi asal tersebut dan berstatus *Tersedia*.
   - Admin dapat memilih satu atau banyak unit sekaligus yang akan dipindahkan ke lokasi tujuan yang sama.
3. Admin memilih lokasi tujuan (wajib berbeda dari lokasi asal), staf pelaksana, dan alasan pemindahan.
4. Sistem mencatat 1 nomor transaksi perpindahan (`transfer`) yang berisi beberapa detail unit, lalu memperbarui `lokasi_id` pada setiap unit yang dipindahkan.
5. **Aturan Mutasi:** Status ketersediaan unit tetap **`Tersedia`**. Unit yang sedang berstatus *Dipinjam* tidak dapat dipindahkan melalui form ini.

---

### 5.8 Integritas Jejak Audit (*Audit Trail & Immutability*)
1. Seluruh transaksi yang telah berstatus akhir (**`Dipinjam`**, **`Dikembalikan`**, **`Ditolak`**, **`Dipindahkan`**) **TIDAK DAPAT DIHAPUS** dari sistem demi kepatuhan audit aset perpustakaan.
2. Tidak ada tombol hapus (*delete*) pada halaman riwayat transaksi.
3. Jika terjadi kesalahan administrasi pada transaksi yang telah berjalan, perbaikan dilakukan melalui transaksi penyesuaian (*adjustment transaction*), bukan menghapus riwayat masa lalu.

---

## 6. Dashboard & Metrik Visual

### 6.1 Dashboard Administrator
- **Kartu Metrik Utama:**
  - Total Unit Aset Fisik.
  - Unit Tersedia (Siap Dipinjam).
  - Unit Sedang Dipinjam.
  - Unit Rusak / Butuh Perbaikan.
  - **Peringatan Peminjaman Menunggak (*Overdue Alert*):** Jumlah transaksi yang melewati `tanggal_rencana_kembali`.
- **Panel Prioritas Tindakan:**
  - Banner mencolok: *"Ada X Permintaan Peminjaman Menunggu Persetujuan"* dengan tombol pintas menuju antrean verifikasi.
- **Peringatan Stok Menipis:** Menampilkan jenis barang yang sisa stok fisiknya `<= 5 unit`.
- **Aksi Cepat (Quick CTA):** Tambah Barang Baru, Tinjau Antrean, Catat Pengembalian, Catat Mutasi.

### 6.2 Dashboard Pegawai
- **Kartu Metrik Personal:**
  - Pinjaman Aktif Saya.
  - Permintaan Saya yang Menunggu Persetujuan.
  - Barang yang Harus Saya Kembalikan Minggu Ini.
- **Aksi Cepat:** Cari Barang di Katalog, Ajukan Pinjaman, Catat Pengembalian Saya.
- **Tabel Riwayat Terkini:** Daftar 5 transaksi terakhir milik akun pegawai tersebut lengkap dengan badge status terkini.

---

## 7. Evaluasi Database Saat Ini & Rekomendasi Migrasi

> **Pertanyaan Pengguna:**  
> *"Apakah database yang sekarang itu sudah sesuai agar aku tidak perlu membuat database baru, atau database ada perbaikan, jika ada maka tulis juga?"*

### 7.1 Jawaban & Evaluasi Kritis atas Database Saat Ini
**Jawaban Langsung:**  
Database MySQL Anda saat ini (`inventaris_barang`) **BELUM SEPENUHNYA SESUAI** dengan kebutuhan arsitektur baru, **TETAPI ANDA TIDAK PERLU MEMBUAT DATABASE BARU DARI NOL (JANGAN DIHAPUS / JANGAN DROP DATABASE)**.

Saat ini database Anda menyimpan **730 baris data di tabel `items`**, 27 staf, 11 lokasi, dan 5 kategori. Menghapus database akan membuang 730 data aset yang sudah diinput dengan susah payah.

Solusi profesional yang wajib diambil adalah **Melakukan Migrasi Skema Penyesuaian (*Schema Alteration & Data Migration*)** menggunakan fitur Migration Laravel.

### 7.2 Mengapa Database Saat Ini Belum Sesuai? (Kelemahan Skema Lama)

1. **Tabel `transactions` Tidak Memiliki Tabel Relasi Detail (`transaction_details`):**
   - Di database sekarang, tabel `transactions` menyimpan `item_id` secara langsung (1 transaksi = 1 item).
   - Jika pegawai meminjam 3 barang sekaligus, sistem lama membuat 3 transaksi terpisah yang tidak saling terhubung. Ini membuat fitur **Pengembalian Sebagian (*Partial Return*)** dan **Mutasi Massal (*Batch Transfer*)** mustahil diimplementasikan secara bersih.
2. **Duplikasi Data Katalog pada 730 Baris `items`:**
   - Akibat migrasi pemecahan unit tanggal 3 November kemarin (`split_inventory_into_individual_units`), tabel `items` sekarang berisi 730 baris di mana setiap baris dipaksa berkuantitas `quantity = 1`.
   - Nama barang, merek, kategori, spesifikasi, dan foto berulang ratusan kali untuk barang yang sama. Belum ada pemisahan antara tabel `barang` (katalog) dengan `unit_barang` (serial fisik).
3. **Kolom Penunjang Belum Tersedia di `transactions`:**
   - Belum ada kolom `expected_return_date` (estimasi tanggal kembali).
   - Belum ada kolom `rejection_reason` (alasan penolakan admin).
   - Kolom `from_location`, `to_location`, `from_person`, `to_person` saat ini bertipe teks biasa (`VARCHAR`), bukan relasi Foreign Key (`location_id`, `staff_id`), sehingga rawan inkonsistensi jika nama lokasi/staf diubah.

---

### 7.3 Skema Target Database Baru (Perbaikan Berjenjang)

Untuk menjaga agar 730 data unit barang tidak rusak dan transisi berjalan mulus, disarankan menggunakan skema tabel terstruktur berikut:

```
[categories] ──< [items_catalog] ──< [item_units] ──< [transaction_details] >── [transactions]
                      │                     │                                         │
                 (Data Induk)          (Unit Fisik)                             (Header Tx)
                                            │                                         │
                                       [locations]                                [staff] & [users]
```

#### Rincian Tabel dan Kolom Baru:

1. **Tabel `item_units` (Pengembangan dari tabel `items` saat ini):**
   - `id`: BigInt (Primary Key)
   - `catalog_id`: BigInt (Foreign Key ke katalog jenis barang)
   - `unit_code`: Varchar(50) **UNIQUE** (Kode inventaris unik per unit, misal: `LAP-001`)
   - `location_id`: BigInt (Foreign Key ke tabel `locations`)
   - `condition`: Enum ('Baik', 'Rusak', 'Perlu Perbaikan')
   - `status`: Enum ('Tersedia', 'Dipinjam', 'Dalam Perbaikan', 'Hilang', 'Nonaktif')
   - `purchase_date`: Date (Opsional)
   - `purchase_price`: Decimal(15,2) (Opsional)
   - `is_active`: Boolean (Default: true)

2. **Tabel `items_catalog` (Katalog Induk Jenis Barang):**
   - `id`: BigInt (Primary Key)
   - `category_id`: BigInt (Foreign Key ke tabel `categories`)
   - `name`: Varchar(255) (Nama barang, misal: "Laptop Lenovo ThinkPad E14")
   - `brand`: Varchar(100) (Nullable)
   - `specification`: Text (Nullable)
   - `unit_name`: Varchar(20) (Satuan: 'unit', 'pcs', 'buah', 'eksemplar')
   - `photo`: Varchar(255) (Nullable)
   - `description`: Text (Nullable)
   - `is_active`: Boolean (Default: true)

3. **Tabel `transactions` (Header Kejadian Transaksi):**
   - `id`: BigInt (Primary Key)
   - `transaction_code`: Varchar(50) **UNIQUE** (Kode transaksi, misal: `TRX-202611-0001`)
   - `type`: Enum ('borrow', 'return', 'transfer', 'maintenance')
   - `status`: Enum ('Menunggu Persetujuan', 'Dipinjam', 'Sebagian Dikembalikan', 'Dikembalikan', 'Ditolak', 'Dibatalkan', 'Selesai')
   - `staff_id`: BigInt (Foreign Key ke tabel `staff` — Peminjam / Petugas Penanggung Jawab)
   - `created_by_user_id`: BigInt (Foreign Key ke `users` — Akun yang login saat input)
   - `approved_by_user_id`: BigInt (Foreign Key ke `users` — Admin yang menyetujui, Nullable)
   - `parent_transaction_id`: BigInt (Foreign Key ke `transactions` — Untuk transaksi `return` yang merujuk `borrow`)
   - `transaction_date`: DateTime (Tanggal transaksi dilakukan)
   - `expected_return_date`: Date (Nullable — Wajib untuk transaksi peminjaman)
   - `notes`: Text (Keperluan pinjam / catatan transaksi)
   - `rejection_reason`: Text (Nullable — Diisi jika admin menolak)

4. **Tabel `transaction_details` (Detail Per Unit Fisik):**
   - `id`: BigInt (Primary Key)
   - `transaction_id`: BigInt (Foreign Key ke `transactions` — Cascade on Delete jika draft)
   - `item_unit_id`: BigInt (Foreign Key ke `item_units`)
   - `from_location_id`: BigInt (Foreign Key ke `locations`, Nullable)
   - `to_location_id`: BigInt (Foreign Key ke `locations`, Nullable)
   - `condition_before`: Varchar(50) (Kondisi saat diserahkan)
   - `condition_after`: Varchar(50) (Nullable — Kondisi saat dikembalikan)
   - `is_returned`: Boolean (Default: false — Penanda apakah unit spesifik ini sudah dikembalikan)
   - `returned_at`: DateTime (Nullable — Waktu pengembalian unit spesifik ini)

---

### 7.4 Rencana Tindakan Migrasi Data (Zero Data Loss)

Agar 730 data unit di database Anda sekarang **tetap aman 100%**, langkah migrasi yang akan dilakukan tim pengembang adalah:

1. **Langkah 1:** Jalankan migration untuk membuat tabel `items_catalog`, `transactions_new`, dan `transaction_details`.
2. **Langkah 2 (Script Migrasi Otomatis):**
   - Lakukan agregasi `GROUP BY name, brand, category_id` dari 730 baris tabel `items` lama, lalu masukkan hasilnya ke tabel `items_catalog`.
   - Hubungkan setiap baris di tabel `items` (yang saat ini mewakili unit individual) dengan ID katalog barunya di `items_catalog`.
3. **Langkah 3:** Menambahkan kolom `expected_return_date`, `rejection_reason`, dan foreign key ke tabel `transactions`.
4. **Hasil Akhir:** Tidak ada satu pun data aset atau transaksi lama yang terhapus, dan struktur database langsung kompatibel 100% dengan PRD V2.

---

## 8. Spesifikasi Desain Antarmuka untuk Figcraft

### 8.1 Prinsip Visual & Desain UI
- **Gaya Visual:** Bersih, korporat-modern (*calm enterprise*), fokus pada densitas data yang nyaman dibaca (*high scannability*).
- **Palet Warna:**
  - Netral: Abu-abu terang (background `#F8FAFC`, permukaan kartu `#FFFFFF`, garis batas `#E2E8F0`).
  - Warna Primer (Aksen Utama): Indigo / Deep Navy (`#3B82F6` atau `#1E40AF`) untuk tombol aksi utama.
  - Warna Semantik Status:
    - *Tersedia / Baik / Disetujui:* Hijau (`#10B981` / Emerald).
    - *Menunggu Persetujuan / Dalam Perbaikan:* Amber/Kuning (`#F59E0B`).
    - *Dipinjam / Pindah:* Biru/Indigo (`#6366F1`).
    - *Ditolak / Rusak / Overdue:* Merah (`#EF4444`).
- **Aksesibilitas:** Seluruh teks status wajib menampilkan label teks eksplisit (tidak hanya warna titik/badge). Kontras warna memenuhi standar minimal WCAG 2.1 AA.

### 8.2 Perilaku Responsif Layar Ponsel (Lebar Minimum 360 px)
- Sidebar navigasi berganti menjadi *Drawer Hamburger* yang dapat dibuka-tutup.
- Tabel data inventaris di desktop berubah menjadi format **Kartu Ringkas (*Stacked Cards*)** di ponsel, sehingga tidak ada *horizontal scroll* pada seluruh body halaman aplikasi.
- Tombol aksi utama (*Floating Action Button* atau Sticky Bottom Bar) mudah dijangkau dengan ibu jari (*thumb zone*).

---

## 9. Cakupan Rilis (Release Scope)

### Prioritas P0 (Wajib Ada pada Rilis Awal):
- Autentikasi dengan hak akses role (Admin & Pegawai).
- Dashboard Manajerial (Admin) & Dashboard Personal (Pegawai) dengan kartu *Overdue Alert*.
- Manajemen Katalog Barang & Unit Fisik (Pencarian, Filter Multikriteria, Tambah, Edit, Arsip).
- Alur Peminjaman Baru: Pengajuan Kuantitas oleh Pegawai -> Alokasi Unit Fisik & Persetujuan oleh Admin.
- Alur Pengembalian Barang: Mendukung pengembalian penuh maupun pengembalian sebagian (*Partial Return*).
- Form Mutasi Lokasi Massal (*Batch Transfer*).
- Siklus Pemeliharaan Barang Rusak.
- Riwayat Transaksi Permanen (*Immutable Audit Trail*).
- Migrasi data 730 item eksisting tanpa kehilangan data.

### Di Luar Cakupan (Out of Scope / Fase Selanjutnya):
- Integrasi WhatsApp Gateway / Notifikasi Email otomatis.
- Sistem denda uang keterlambatan.
- Pemindaian kamera barcode real-time dari browser ponsel (disiapkan manual input/scanner USB biasa terlebih dahulu).
- Integrasi akuntansi depresiasi aset tetap (*fixed asset depreciation*).

---

## 10. Persyaratan Non-Fungsional & Keamanan

1. **Integritas Konkurensi (*Atomic & Locking*):** Setiap aksi persetujuan pinjaman, pengembalian, dan mutasi unit wajib dibungkus dalam `DB::transaction()` dan menggunakan `lockForUpdate()` pada level unit fisik guna mencegah *race condition* saat dua admin membuka antrean bersamaan.
2. **Keamanan Otorisasi di Server:** Seluruh rute dan aksi tulis (*write operations*) divalidasi oleh Policy/Middleware Laravel. Kegagalan otorisasi menghasilkan respon HTTP 403 Forbidden.
3. **Penyimpanan Berkas Foto:** Unggah foto barang dibatasi maksimal 2 MB dengan validasi tipe berkas aman (`jpg`, `jpeg`, `png`, `webp`).
4. **Kecepatan Respons:** Paginasi default 15 data per halaman; kueri pencarian pada 730+ unit dioptimalkan menggunakan database indexing pada kolom `code`, `name`, `status`, dan foreign keys.

---

## 11. Kriteria Penerimaan Implementasi (Acceptance Criteria)

- [ ] 730 data unit barang lama berhasil termigrasi ke skema baru tanpa ada nomor seri atau barang yang hilang.
- [ ] Pegawai dapat mengajukan pinjaman dengan memilih jenis barang dan kuantitas tanpa dipaksa memilih kode serial unit fisik.
- [ ] Admin dapat melihat daftar antrean dan memilih kode unit fisik saat menyetujui peminjaman.
- [ ] Unit fisik yang disetujui langsung berubah status menjadi `Dipinjam` dan tidak dapat dipilih oleh transaksi lain.
- [ ] Transaksi peminjaman yang berisi 3 unit dapat dikembalikan 1 unit terlebih dahulu; status transaksi menjadi `Sebagian Dikembalikan` dan unit yang dikembalikan kembali `Tersedia`.
- [ ] Barang yang dikembalikan dengan kondisi *Rusak* berpindah ke antrean pemeliharaan dan tidak bisa dipinjam sebelum diperbaiki oleh admin.
- [ ] Admin dapat memindahkan 5 unit barang dari Lab Komputer ke Gudang dalam 1 kali pengisian form mutasi.
- [ ] Tidak ada tombol hapus (*delete*) pada transaksi yang sudah selesai.
- [ ] Tampilan antarmuka dapat dioperasikan secara penuh pada resolusi smartphone 360x640 px tanpa terjadi *layout overflow*.

---

## 12. Panduan Teknis Khusus untuk Junior Programmer & Model AI (Technical Guardrails & Implementation Guide)

> [!CAUTION]
> ### 🛑 DANGER ZONE — ATURAN MUTLAK DATABASE (BACA INI DULU!)
> 1. **DILARANG KERAS MENJALANKAN:**
>    - `php artisan migrate:fresh`
>    - `php artisan migrate:reset`
>    - `DROP DATABASE` atau menghapus tabel manual di phpMyAdmin.
> 2. **ALASAN:** Database saat ini menyimpan **730 baris unit barang fisik**, 27 staf, 11 lokasi, dan 5 kategori. Menjalankan perintah di atas akan menghapus seluruh data nyata perpustakaan secara permanen!
> 3. **WAJIB:** Sebelum menyentuh kode atau database, lakukan backup terlebih dahulu:
>    ```bash
>    mysqldump -u root -p inventaris_barang > backup_inventaris_barang_$(date +%F).sql
>    ```
> 4. Setiap penyesuaian struktur database **HARUS** dilakukan melalui file migrasi baru (`php artisan make:migration ...`) yang bersifat aman dan menjaga integritas data lama (*backward-compatible*).

---

### 12.1 Kamus Status Resmi (Official Enum & State Dictionary)
Untuk mencegah *bug* akibat *typo* string atau pencampuran bahasa (Inggris vs Indonesia) oleh programmer atau model AI, gunakan daftar nilai konstan berikut secara ketat:

| Kategori Data | Nama Kolom | Tipe / Format | Nilai yang Sah (Valid Values) | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **Kondisi Fisik** | `condition` | String / Enum | `'Baik'`, `'Rusak'`, `'Perlu Perbaikan'` | Kondisi fisik barang di dunia nyata. |
| **Status Unit** | `status` | String / Enum | `'Tersedia'`, `'Dipinjam'`, `'Dalam Perbaikan'`, `'Hilang'`, `'Nonaktif'` | Ketersediaan unit untuk dipinjam. |
| **Tipe Transaksi** | `type` | String / Enum | `'borrow'`, `'return'`, `'transfer'`, `'maintenance'` | Jenis aksi/kejadian pada sistem. |
| **Status Transaksi** | `status` | String / Enum | `'Menunggu Persetujuan'`, `'Dipinjam'`, `'Sebagian Dikembalikan'`, `'Dikembalikan'`, `'Ditolak'`, `'Dibatalkan'`, `'Selesai'` | *Lifecycle* dari dokumen transaksi. |
| **Peran Pengguna** | `role` | String / Enum | `'admin'`, `'pegawai'` | Hak akses akun pengguna aplikasi. |

> [!TIP]
> **Rekomendasi Laravel 10/11:** Buat file PHP Backed Enum di `app/Enums/`:
> - `app/Enums/ItemCondition.php`
> - `app/Enums/ItemUnitStatus.php`
> - `app/Enums/TransactionType.php`
> - `app/Enums/TransactionStatus.php`
> - `app/Enums/UserRole.php`

---

### 12.2 Blueprint Migrasi Skema & Transformasi Data Eksisting (Step-by-Step)

Junior developer atau model AI harus menjalankan langkah migrasi ini secara berurutan:

#### Langkah 1: Buat Migration Tabel `items_catalog`
```php
Schema::create('items_catalog', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
    $table->string('name');
    $table->string('brand')->nullable();
    $table->text('specification')->nullable();
    $table->string('unit_name')->default('unit');
    $table->string('photo')->nullable();
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    // Index untuk mempercepat pencarian katalog
    $table->index(['name', 'brand']);
});
```

#### Langkah 2: Tambah Kolom `catalog_id` pada Tabel `items` (Unit Fisik) & Migrasi Data Otomatis
Buat migration baru untuk mengekstrak 730 baris `items` menjadi katalog unik:
```php
public function up(): void
{
    Schema::table('items', function (Blueprint $table) {
        $table->foreignId('catalog_id')->nullable()->after('id')->constrained('items_catalog')->nullOnDelete();
    });

    // Skrip Migrasi Otomatis (Populate Catalog dari Data Lama)
    $legacyItems = DB::table('items')->orderBy('id')->get();
    $catalogCache = [];

    foreach ($legacyItems as $item) {
        $key = trim(strtolower($item->name)) . '|' . trim(strtolower($item->brand ?? '')) . '|' . ($item->category_id ?? 0);
        
        if (!isset($catalogCache[$key])) {
            $catalogId = DB::table('items_catalog')->insertGetId([
                'category_id'   => $item->category_id,
                'name'          => $item->name,
                'brand'         => $item->brand,
                'specification' => $item->specification,
                'unit_name'     => $item->unit ?? 'unit',
                'photo'         => $item->photo,
                'description'   => $item->description,
                'is_active'     => $item->is_active ?? true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $catalogCache[$key] = $catalogId;
        }

        // Tautkan unit fisik ini ke catalog_id yang baru dibuat
        DB::table('items')->where('id', $item->id)->update([
            'catalog_id' => $catalogCache[$key]
        ]);
    }
}
```

#### Langkah 3: Perbaiki Tabel `transactions` & Buat `transaction_details`
```php
Schema::table('transactions', function (Blueprint $table) {
    if (!Schema::hasColumn('transactions', 'transaction_code')) {
        $table->string('transaction_code')->nullable()->unique()->after('id');
    }
    if (!Schema::hasColumn('transactions', 'expected_return_date')) {
        $table->date('expected_return_date')->nullable()->after('transaction_date');
    }
    if (!Schema::hasColumn('transactions', 'rejection_reason')) {
        $table->text('rejection_reason')->nullable()->after('notes');
    }
    if (!Schema::hasColumn('transactions', 'staff_id')) {
        $table->foreignId('staff_id')->nullable()->after('user_id')->constrained('staff')->nullOnDelete();
    }
});

Schema::create('transaction_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
    $table->foreignId('item_id')->constrained('items')->cascadeOnDelete(); // unit fisik
    $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
    $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
    $table->string('condition_before')->default('Baik');
    $table->string('condition_after')->nullable();
    $table->boolean('is_returned')->default(false);
    $table->dateTime('returned_at')->nullable();
    $table->timestamps();
});
```

---

### 12.3 Blueprint Relasi Eloquent Model (Laravel Convention)

Gunakan penamaan relasi standar Laravel berikut pada folder `app/Models/`:

```
┌─────────────────┐       hasMany       ┌─────────────────┐
│   ItemCatalog   │ ──────────────────> │      Item       │ (Unit Fisik)
└─────────────────┘                     └─────────────────┘
         │                                       │
         │ belongsTo                             │ hasMany
         v                                       v
┌─────────────────┐                     ┌─────────────────────┐
│    Category     │                     │  TransactionDetail  │
└─────────────────┘                     └─────────────────────┘
                                                 │
                                                 │ belongsTo
                                                 v
┌─────────────────┐       hasMany       ┌─────────────────────┐
│      Staff      │ <────────────────── │     Transaction     │ (Header)
└─────────────────┘                     └─────────────────────┘
```

1. **`ItemCatalog` (`app/Models/ItemCatalog.php`):**
   - `category()`: `belongsTo(Category::class)`
   - `units()`: `hasMany(Item::class, 'catalog_id')`
   - *Accessor* `available_units_count`: Menghitung unit aktif yang berstatus `'Tersedia'` dan kondisi `'Baik'`.
2. **`Item` (`app/Models/Item.php` - Unit Fisik):**
   - `catalog()`: `belongsTo(ItemCatalog::class, 'catalog_id')`
   - `location()`: `belongsTo(Location::class)`
   - `transactionDetails()`: `hasMany(TransactionDetail::class)`
3. **`Transaction` (`app/Models/Transaction.php`):**
   - `details()`: `hasMany(TransactionDetail::class)`
   - `staff()`: `belongsTo(Staff::class)`
   - `user()`: `belongsTo(User::class, 'user_id')` (akun pencatat)
   - `parentTransaction()`: `belongsTo(Transaction::class, 'borrow_transaction_id')`
   - `returnTransactions()`: `hasMany(Transaction::class, 'borrow_transaction_id')`
4. **`TransactionDetail` (`app/Models/TransactionDetail.php`):**
   - `transaction()`: `belongsTo(Transaction::class)`
   - `item()`: `belongsTo(Item::class)` (unit fisik)
   - `fromLocation()`: `belongsTo(Location::class, 'from_location_id')`
   - `toLocation()`: `belongsTo(Location::class, 'to_location_id')`

---

### 12.4 Contoh Logika Kritis: Transaksi Persetujuan (Approval) & Locking

Junior developer atau model AI wajib mengimplementasikan transaksi database atomik dan *locking* untuk mencegah permohonan ganda:

```php
public function approve(Transaction $transaction, Request $request)
{
    // Validasi input: admin memilih serial unit fisik yang akan diserahkan
    $request->validate([
        'unit_ids' => ['required', 'array', 'min:1'],
        'unit_ids.*' => ['required', 'exists:items,id'],
    ]);

    DB::transaction(function () use ($transaction, $request) {
        // 1. Kunci baris transaksi peminjaman
        $lockedTransaction = Transaction::whereKey($transaction->id)
            ->where('type', 'borrow')
            ->where('status', 'Menunggu Persetujuan')
            ->lockForUpdate()
            ->firstOrFail();

        // 2. Kunci seluruh unit fisik yang dipilih dan pastikan masih 'Tersedia'
        $units = Item::whereIn('id', $request->unit_ids)
            ->where('status', 'Tersedia')
            ->where('is_active', true)
            ->lockForUpdate()
            ->get();

        if ($units->count() !== count($request->unit_ids)) {
            throw ValidationException::withMessages([
                'unit_ids' => 'Salah satu atau lebih unit yang dipilih sudah tidak tersedia.'
            ]);
        }

        // 3. Buat detail transaksi dan update status unit menjadi 'Dipinjam'
        foreach ($units as $unit) {
            TransactionDetail::create([
                'transaction_id'    => $lockedTransaction->id,
                'item_id'           => $unit->id,
                'from_location_id'  => $unit->location_id,
                'to_location_id'    => $lockedTransaction->to_location_id ?? $unit->location_id,
                'condition_before'  => $unit->condition,
                'is_returned'       => false,
            ]);

            $unit->update([
                'status' => 'Dipinjam',
                // lokasi berpindah ke lokasi peminjaman jika ditentukan
            ]);
        }

        // 4. Update status transaksi utama menjadi 'Dipinjam'
        $lockedTransaction->update([
            'status' => 'Dipinjam',
            'approved_by_user_id' => Auth::id(),
        ]);
    });

    return redirect()->route('transactions.index')->with('success', 'Peminjaman berhasil disetujui.');
}
```

---

### 12.5 Urutan Pengerjaan Proyek (Step-by-Step Execution Roadmap)

Jangan melompat-lompat fitur. Kerjakan dengan urutan berikut:

```
[Tahap 1: Persiapan & DB] ──> [Tahap 2: Master & Auth] ──> [Tahap 3: Katalog & Unit]
                                                                    │
[Tahap 6: UI & Polish]    <── [Tahap 5: Dashboard/Export] <── [Tahap 4: Transaksi Core]
```

1. **Tahap 1 — Database & Integritas:**
   - Lakukan backup database `inventaris_barang`.
   - Jalankan migration penyesuaian (tabel `items_catalog`, `transaction_details`, penambahan kolom baru).
   - Verifikasi data 730 unit tetap utuh dan terhubung ke katalog.
2. **Tahap 2 — Autentikasi & Master Data:**
   - Pastikan login multi-role (`admin` dan `pegawai`) aktif menggunakan middleware `role`.
   - Modul CRUD Kategori, Lokasi, dan Staf (lengkap dengan *soft deactivation*, bukan hard delete).
3. **Tahap 3 — Manajemen Katalog & Unit Barang:**
   - Tampilan antarmuka katalog (daftar barang, stok tersedia, filter kategori/kondisi).
   - Detail katalog yang menampilkan daftar unit fisik berkode serial di bawahnya.
4. **Tahap 4 — Alur Transaksi Utama (Jantung Aplikasi):**
   - Form Pengajuan Pinjam Pegawai (Pilih barang, input jumlah, tanggal rencana kembali).
   - Halaman Antrean Admin (Pilih serial unit fisik $\rightarrow$ Setujui/Tolak).
   - Form Pengembalian (Mendukung pengembalian penuh atau bertahap, cek kondisi Baik/Rusak).
   - Form Mutasi Lokasi Massal (*Batch Transfer*).
   - Form Pemeliharaan Barang Rusak (*Maintenance*).
5. **Tahap 5 — Dashboard & Laporan:**
   - Dashboard Admin: Kartu total aset, barang dipinjam, barang rusak, kartu **Overdue Alert**, dan antrean pending.
   - Dashboard Pegawai: Daftar pinjaman aktif saya dan riwayat pribadi.
   - Ekspor data CSV yang mengikuti filter aktif.
6. **Tahap 6 — Desain Responsif & Uji Konkurensi:**
   - Penyesuaian tampilan ponsel (lebar 360 px), memastikan drawer menu dan kartu ringkas berjalan rapi.
   - Uji coba skenario *double submission* (klik tombol berulang kali) dan pastikan transaksi atomik bekerja.

---

### 12.6 Checklist Larangan untuk Model AI (Prompting Anti-Hallucination)

Jika menggunakan model AI (Claude Haiku, GPT-4o-mini, dsb.) untuk menghasilkan kode:
- ❌ **JANGAN** membuat tabel baru yang tidak tertera di dokumen ini (seperti tabel `fines`, `reviews`, `chats`, `notifications`, `orders`).
- ❌ **JANGAN** menggunakan query manual tanpa Eloquent relationship (hindari masalah *N+1 query* dengan selalu memakai `with(...)`).
- ❌ **JANGAN** menempatkan logika bisnis pemotongan status di dalam Blade View atau Route Closure; tempatkan di `Controller` atau `Service Action`.
- ❌ **JANGAN** mengubah status transaksi menjadi `'borrowed'`, `'returned'`, atau string bahasa Inggris lainnya. Wajib gunakan kamus status resmi di Bagian 12.1.
- ❌ **JANGAN** menampilkan tombol hapus pada transaksi yang sudah selesai.
- ✔️ **SELALU** gunakan `FormRequest` terpisah untuk validasi formulir (misal `StoreBorrowRequest`, `ReturnTransactionRequest`).
- ✔️ **SELALU** bungkus operasi tulis jamak (*multiple writes*) dalam `DB::transaction()`.


