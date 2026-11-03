# ATURAN PROYEK INVENTARIS PERPUSTAKAAN

## 1. DOKUMEN UTAMA
- Selalu patuhi spesifikasi di file `prd.md`.

## 2. ATURAN KRITIS DATABASE
- DILARANG KERAS menjalankan `php artisan migrate:fresh` atau `migrate:reset`.
- Database `inventaris_barang_perpus` berisi data nyata (730 item barang).
- Perubahan skema HANYA boleh lewat migration baru tanpa menghapus data lama.

## 3. KONSISTENSI STATUS & BAHASA
- Seluruh teks UI, label, dan pesan validasi/notifikasi wajib dalam Bahasa Indonesia.
- Nilai status transaksi HANYA: 'Menunggu Persetujuan', 'Dipinjam', 'Sebagian Dikembalikan', 'Dikembalikan', 'Ditolak', 'Dibatalkan', 'Selesai'.
- Status unit HANYA: 'Tersedia', 'Dipinjam', 'Dalam Perbaikan', 'Hilang', 'Nonaktif'.
- Kondisi fisik HANYA: 'Baik', 'Rusak', 'Perlu Perbaikan'.
- Tipe transaksi HANYA: 'borrow', 'return', 'transfer', 'maintenance'.

## 4. STANDAR KODE LARAVEL
- Gunakan FormRequest terpisah untuk validasi formulir.
- Selalu gunakan Eloquent Eager Loading (`with(...)`) untuk mencegah masalah N+1 query.
- Operasi transaksi jamak wajib dibungkus dalam `DB::transaction()` dan gunakan `lockForUpdate()`.

<!-- CODEGRAPH_START -->
## CodeGraph

In repositories indexed by CodeGraph (a `.codegraph/` directory exists at the repo root), reach for it BEFORE grep/find or reading files when you need to understand or locate code:

- **MCP tool** (when available): `codegraph_explore` answers most code questions in one call — the relevant symbols' verbatim source plus the call paths between them, including dynamic-dispatch hops grep can't follow. Name a file or symbol in the query to read its current line-numbered source. If it's listed but deferred, load it by name via tool search.
- **Shell** (always works): `codegraph explore "<symbol names or question>"` prints the same output.

If there is no `.codegraph/` directory, skip CodeGraph entirely — indexing is the user's decision.
<!-- CODEGRAPH_END -->
