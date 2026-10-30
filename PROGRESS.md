# Market Basket Analysis (Apriori) — Progress & Checkpoint

> **Status Saat Ini:** Selesai (Completed & Production-Ready)  
> **Terakhir Diperbarui:** 2026-10-30  
> **Repository Root:** `/var/www/sistem_asosiasi_rule`

---

## 1. Ringkasan Eksekutif & Pencapaian

Aplikasi web **Market Basket Analysis** dengan algoritma **Apriori** telah berhasil dibangun secara utuh dan teruji 100% menggunakan arsitektur Laravel 13, PHP 8.4, MySQL 8.0, Blade, Tailwind CSS v4, dan Alpine.js.

Seluruh 15 poin kriteria penerimaan (acceptance criteria) dan 6 fase implementasi telah diselesaikan:
1. **Ketepatan Algoritma:** Algoritma Apriori mandiri (Pure PHP Engine) bekerja dengan akurat melalui tahapan Join, Pruning ($C_k \to L_k$), Support Count, pembagian partisi aturan non-empty, perhitungan Confidence, dan Lift Ratio.
2. **Automated Testing:** 17 automated tests (79 assertions) lulus 100%, termasuk pengujian dataset benchmark 5 transaksi standar (Roti, Susu, Mentega; Min Support 40%, Min Confidence 60%).
3. **Data Management:** Mendukung upload dataset CSV & XLSX, pratinjau data, pemetaan kolom otomatis & manual, validasi kesalahan baris dengan pesan informatif, penanganan duplikasi per transaksi (sum quantity), dan download template CSV.
4. **Desain Antarmuka:** Tampilan profesional, elegan, dan minimalis bergaya SaaS modern (Linear / Notion / Vercel dashboard) tanpa elemen dekoratif berlebihan.
5. **Pelaporan & Ekspor:** Mendukung ekspor laporan resmi dalam format PDF (DomPDF), Excel multi-sheet (Maatwebsite Excel), serta tampilan ramah cetak (Print Preview).

---

## 2. Kredensial & Konfigurasi Lingkungan

- **Web Server / Framework:** Laravel 13.x (PHP 8.4.23 CLI)
- **Database Engine:** MySQL 8.0.46
- **Database Name:** `market_basket_analysis` (Testing DB: `db_testing`)
- **Database User:** `root`
- **Database Password:** `"Dimasmysql#1"`
- **Host & Port:** `127.0.0.1:3306`
- **Server Dev:** `http://127.0.0.1:8000` (atau via web server Nginx / Apache jika dikonfigurasi)
- **Queue Connection:** `sync`
- **Session & Cache Driver:** `file`

---

## 3. Fitur yang Berhasil Dibuat

### A. Dashboard (`/dashboard`)
- Menampilkan 6 metrik riil dari database: Total Dataset, Total Transaksi, Total Item Unik, Total Analisis, Total Frequent Itemset, dan Total Association Rule.
- Tabel riwayat analisis terbaru dan dataset terkini dengan tombol aksi cepat.

### B. Kelola Dataset (`/datasets`)
- Unggah file CSV dan Excel (XLSX, XLS) hingga 10MB.
- Pratinjau data 10 baris pertama sebelum proses penyimpanan.
- Pemetaan kolom interaktif (ID Transaksi, Nama Produk, Kuantitas, Tanggal).
- Normalisasi nama item (trim whitespace, lowercasing untuk pemadanan, display name rapi).
- Penggabungan item identik pada transaksi yang sama dengan penjumlahan kuantitas.
- Validasi data ketat dengan pesan nomor baris yang spesifik.
- Transaksi database rollback jika terjadi kegagalan impor.
- Hapus dataset beserta konfirmasi modal.
- Download template format CSV.

### C. Data Transaksi (`/transactions`)
- Filter transaksi berdasarkan dataset terpilih.
- Pencarian transaksi berdasarkan kode faktur / ID transaksi.
- Menampilkan seluruh item dalam keranjang transaksi beserta kuantitas belanja dan tanggal.

### D. Analisis Apriori (`/analyses/create`)
- Form konfigurasi: Nama analisis, pemilihan dataset, persentase Minimum Support, persentase Minimum Confidence, dan opsional Minimum Lift.
- Ringkasan profil dataset sebelum analisis dimulai.
- Validasi rentang parameter (Support & Confidence antara 0% s.d. 100%, Lift positif).

### E. Hasil Analisis (`/analyses/{id}`)
- **Tab 1 — Overview:** Ringkasan parameter analisis, total transaksi, total frequent itemset, total association rule, durasi eksekusi (ms), dan status.
- **Tab 2 — Frequent Itemset:** Tabel kombinasi item, filter berdasarkan ukuran ($1$-itemset, $2$-itemset, $3$-itemset, dst), pencarian produk, support count, dan persentase support.
- **Tab 3 — Association Rule:** Tabel aturan asosiasi (Antecedent $\to$ Consequent), support, confidence, nilai lift, badge interpretasi (Positif / Negatif / Independen), kalimat penjelasan non-kausalitas, filter rentang nilai, dan pengurutan (sorting) dinamis.
- **Tab 4 — Detail Iterasi:** Menampilkan tahapan $C_1/L_1, C_2/L_2, \dots, C_k/L_k$, jumlah kandidat dibentuk, jumlah frequent itemset lolos, durasi langkah, serta penjelasan prinsip eliminasi pruning Apriori.

### F. Riwayat Analisis (`/analyses`)
- Seluruh riwayat percobaan analisis terdahulu.
- Filter berdasarkan dataset, status pengerjaan, dan pencarian nama.
- Tombol aksi lihat hasil atau hapus dengan konfirmasi.

### G. Laporan & Ekspor (`/reports`)
- **Ekspor PDF:** Dokumen A4 terstruktur rapi memuat ringkasan parameter, frequent itemsets, association rules, dan kesimpulan temuan.
- **Ekspor Excel:** Spreadsheet XLSX dengan 4 lembar kerja (Ringkasan, Frequent Itemsets, Association Rules, Detail Iterasi).
- **Cetak Laporan:** Tampilan halaman bersih dengan dukungan `@media print`.

### H. Pengaturan (`/settings`)
- Informasi lingkungan aplikasi (versi Laravel, PHP runtime, database driver, cache/queue driver).
- Pengaturan profil pengguna (ubah nama, email, dan kata sandi).

---

## 4. Struktur Database

1. **`datasets`**: `id`, `user_id`, `name`, `description`, `original_filename`, `storage_path`, `file_type`, `total_rows`, `total_transactions`, `total_items`, `status`, `imported_at`, `timestamps`.
2. **`transactions`**: `id`, `dataset_id`, `transaction_code`, `transaction_at`, `timestamps`. (Unique: `dataset_id` + `transaction_code`).
3. **`items`**: `id`, `dataset_id`, `name`, `normalized_name`, `timestamps`. (Unique: `dataset_id` + `normalized_name`).
4. **`transaction_items`**: `id`, `transaction_id`, `item_id`, `quantity`, `timestamps`. (Unique: `transaction_id` + `item_id`).
5. **`analyses`**: `id`, `dataset_id`, `user_id`, `name`, `algorithm`, `min_support`, `min_confidence`, `min_lift`, `status`, `total_transactions`, `total_frequent_itemsets`, `total_rules`, `started_at`, `completed_at`, `duration_ms`, `error_message`, `timestamps`.
6. **`itemsets`**: `id`, `analysis_id`, `itemset_size`, `itemset_hash`, `support_count`, `support`, `timestamps`. (Unique: `analysis_id` + `itemset_hash`).
7. **`itemset_items`**: `id`, `itemset_id`, `item_id`. (Unique: `itemset_id` + `item_id`).
8. **`association_rules`**: `id`, `analysis_id`, `antecedent_itemset_id`, `consequent_itemset_id`, `union_itemset_id`, `support_count`, `support`, `confidence`, `lift`, `timestamps`. (Unique: `analysis_id` + `antecedent_itemset_id` + `consequent_itemset_id`).
9. **`apriori_iterations`**: `id`, `analysis_id`, `k`, `candidate_count`, `frequent_count`, `duration_ms`, `created_at`. (Unique: `analysis_id` + `k`).

---

## 5. Service & Arsitektur Kelas Algoritma

- `app/Services/AprioriService.php`: Koordinator utama eksekusi algoritma Apriori, pencatatan iterasi, penyimpanan itemset dan aturan dalam transaksi database atomik.
- `app/Services/FrequentItemsetService.php`: Pembentukan $C_1/L_1$, operasi Join $L_{k-1} \bowtie L_{k-1}$, operasi Pruning subset $k-1$, serta pencocokan transaksi berindeks.
- `app/Services/AssociationRuleService.php`: Pembentukan partisi non-empty antecedent & consequent untuk itemset $\ge 2$, penghitungan Support, Confidence, Lift, dan eliminasi ambang batas.
- `app/Services/DatasetImportService.php`: Parsing CSV/XLSX, deduplikasi item transaksi, penggabungan kuantitas, dan impor bulk database.
- `app/Services/DatasetValidationService.php`: Validasi spesifik setiap baris data.

---

## 6. Hasil Automated Testing

```bash
php artisan test
```

- **Tests:** 17 Passed (0 Failed)
- **Assertions:** 79 Passed
- **Cakupan:**
  - Benchmark pengujian Apriori (5 transaksi: T1–T5, Min Support 40%, Min Confidence 60%).
  - Verifikasi eksklusi aturan di bawah ambang batas (Confidence 50% < 60%).
  - Verifikasi pruning superset ketika support tidak terpenuhi.
  - Verifikasi import CSV dengan duplicate item quantity aggregation.
  - Verifikasi deteksi baris invalid dengan pesan error nomor baris.
  - Verifikasi rendering seluruh view dan ekspor (PDF, Excel, Print).

---

## 7. Cara Menjalankan Aplikasi

1. **Jalankan Migrasi & Seeder:**
   ```bash
   php artisan migrate --force
   php artisan db:seed
   ```
2. **Kompilasi Frontend (Jika Ada Perubahan):**
   ```bash
   npm run build
   ```
3. **Jalankan Server Aplikasi:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```
   Buka browser di `http://localhost:8000` (atau IP server Anda).
