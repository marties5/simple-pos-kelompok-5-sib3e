# Laporan Query Listing Transaksi & EXPLAIN QUERY PLAN (Tinker)

Dokumentasi ini membuktikan pengujian query listing transaksi menggunakan `php artisan tinker` dan pemeriksaan penggunaan indeks melalui perintah `EXPLAIN QUERY PLAN` pada SQLite.

---

## 1. Query Listing Transaksi dalam Rentang Tanggal

Dijalankan melalui `php artisan tinker`:

```php
// Listing transaksi dalam rentang tanggal tertentu (misal: September 2026)
$transactions = DB::table('transactions')
    ->whereBetween('created_at', ['2026-09-01 00:00:00', '2026-09-30 23:59:59'])
    ->get();
```

### Pemeriksaan dengan `EXPLAIN QUERY PLAN`:
```php
DB::select("EXPLAIN QUERY PLAN SELECT * FROM transactions WHERE created_at BETWEEN '2026-09-01 00:00:00' AND '2026-09-30 23:59:59'");
```

**Hasil Output:**
```text
array:1 [
  0 => {#7071
    +"id": 2
    +"parent": 0
    +"notused": 216
    +"detail": "SCAN transactions"
  }
]
```

**Analisis:**
- Status query adalah **`SCAN transactions`** *(Full Table Scan)*.
- Hal ini terjadi karena kolom `created_at` pada tabel `transactions` belum memiliki indeks khusus, sehingga database harus membaca seluruh baris tabel dari awal sampai akhir.

---

## 2. Query Listing Transaksi dengan Filter Kasir (`user_id`) & Rentang Tanggal

Dijalankan melalui `php artisan tinker`:

```php
// Listing transaksi milik user_id = 1 dalam rentang tanggal
$transactionsByUser = DB::table('transactions')
    ->where('user_id', 1)
    ->whereBetween('created_at', ['2026-09-01 00:00:00', '2026-09-30 23:59:59'])
    ->get();
```

### Pemeriksaan dengan `EXPLAIN QUERY PLAN`:
```php
DB::select("EXPLAIN QUERY PLAN SELECT * FROM transactions WHERE user_id = 1 AND created_at BETWEEN '2026-09-01 00:00:00' AND '2026-09-30 23:59:59'");
```

**Hasil Output:**
```text
array:1 [
  0 => {#7055
    +"id": 3
    +"parent": 0
    +"notused": 62
    +"detail": "SEARCH transactions USING INDEX transactions_user_id_index (user_id=?)"
  }
]
```

**Analisis:**
- Status query adalah **`SEARCH transactions USING INDEX transactions_user_id_index (user_id=?)`**.
- Query ini **SUDAH memakai index yang ada** (`transactions_user_id_index`), yang ditambahkan pada migrasi `2026_09_17_134826_add_index_to_products_and_transactions_table`.
- Pencarian data langsung menuju index B-Tree tanpa memindai seluruh tabel, sehingga jauh lebih cepat dan efisien.

---

## 3. Kesimpulan & Rekomendasi

1. Index `transactions_user_id_index` terbukti aktif dan dimanfaatkan secara optimal oleh SQLite engine saat query melibatkan kondisi `user_id`.
2. Jika aplikasi sering melakukan pencarian/laporan transaksi berdasarkan rentang tanggal (`created_at`), disarankan untuk menambahkan index pada kolom `created_at` atau komposit index `(user_id, created_at)`.
