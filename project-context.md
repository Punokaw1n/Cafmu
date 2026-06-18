# Master Project Context & AI Instructions

## 1. Project Overview

- **Nama Project:** Sistem Menu QR & Self-Ordering (SaaS Ready)
- **Tujuan:** Membuat aplikasi pemesanan mandiri untuk kafe berbasis QR Code di mana pelanggan bisa memesan langsung dari meja tanpa perlu ke kasir. Pesanan masuk ke dashboard kasir secara real-time.
- **Model Bisnis:** SaaS — aplikasi ini akan disewakan ke banyak kafe (multi-tenant).
- **Metode Pengembangan:** Incremental Model — kerjakan fitur per inkremen sesuai instruksi, **jangan membangun seluruh sistem sekaligus**.
- **Identifikasi Tenant:** Berbasis **subdomain** — setiap kafe mendapat subdomain unik (contoh: `kopisusu.appkamu.com`). Di tahap development lokal, gunakan file `hosts` untuk simulasi subdomain.

---

## 2. Tech Stack & Environment

- **PHP Version:** Minimum PHP 8.2
- **Backend:** Laravel 12
- **Authentication:** Laravel Breeze (session-based, untuk admin & kasir — tidak ada mobile app)
- **Role & Permission:** Spatie Laravel Permission (`admin`, `kasir`)
- **Frontend / UI:** Blade Templates + Tailwind CSS (utility-first, hindari custom CSS manual)
- **JS & UI Components:** Alpine.js (untuk interaktivitas client-side: cart drawer, modal, dropdown, counter)
- **Real-time Engine:** Laravel Reverb (WebSocket — untuk push pesanan baru ke dashboard kasir tanpa refresh)
- **Database:** MySQL / MariaDB
- **QR Code Generator:** `simplesoftwareio/simple-qrcode`
- **PDF / Struk:** `barryvdh/laravel-dompdf` (opsional, untuk export struk PDF)
- **Image Handling:** Laravel Storage (disk: public) dengan helper `Storage::url()`
- **Payment Gateway:** Midtrans (Sandbox untuk development, Production untuk live)
- **Notifikasi:** WhatsApp API (untuk e-receipt otomatis setelah pembayaran lunas)
- **Server & Deployment (Target):** VPS dengan OpenLiteSpeed atau Docker container
- **Wildcard DNS (Production):** `*.appkamu.com` → IP server (untuk multi-tenant subdomain)

---

## 3. Database Schema

> Dirancang multi-tenant. Semua tabel utama memiliki `tenant_id` sebagai isolasi data antar kafe.

### `tenants`
| Field | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | Nama kafe |
| subdomain | string unique | Slug subdomain (contoh: `kopisusu`) |
| is_active | boolean | Status aktif/nonaktif tenant |
| created_at / updated_at | timestamp | |

### `tenant_settings`
| Field | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants | |
| key | string | Contoh: `logo_url`, `primary_color`, `wa_number`, `midtrans_server_key` |
| value | text nullable | Nilai konfigurasi |
| created_at / updated_at | timestamp | |

### `users`
| Field | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants | |
| name | string | |
| email | string unique | |
| password | string | |
| created_at / updated_at | timestamp | |
> Role dikelola via Spatie Laravel Permission (bukan kolom di tabel ini)

### `tables`
| Field | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants | |
| table_number | string | Nomor/nama meja |
| qr_code_string | string unique | Token unik untuk URL QR |
| is_active | boolean | Status meja aktif/nonaktif |
| created_at / updated_at | timestamp | |

### `categories`
| Field | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants | |
| name | string | |
| sort_order | integer default 0 | Urutan tampil di menu |
| created_at / updated_at | timestamp | |

### `products`
| Field | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants | |
| category_id | FK → categories | |
| name | string | |
| description | text nullable | |
| price | decimal(10,2) | |
| image | string nullable | Path file gambar |
| is_available | boolean default true | Toggle ketersediaan produk |
| sort_order | integer default 0 | Urutan tampil di menu |
| created_at / updated_at | timestamp | |

### `orders`
| Field | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants | |
| table_id | FK → tables | |
| order_number | string unique | Kode order (contoh: `ORD-20250617-001`) |
| total_price | decimal(10,2) | |
| status | enum | `new`, `processing`, `ready`, `completed` — status dapur/kasir |
| payment_status | enum | `pending`, `paid`, `cancelled` — status pembayaran |
| payment_url | string nullable | URL Snap Midtrans |
| midtrans_transaction_id | string nullable | ID transaksi dari Midtrans |
| customer_name | string nullable | Nama pelanggan (opsional saat checkout) |
| customer_phone | string nullable | Nomor WA untuk e-receipt |
| notes | text nullable | Catatan umum dari pelanggan |
| created_at / updated_at | timestamp | |

### `order_items`
| Field | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| order_id | FK → orders | |
| product_id | FK → products | |
| quantity | integer | |
| price | decimal(10,2) | **Snapshot harga saat order dibuat** (bukan relasi ke products.price) |
| subtotal | decimal(10,2) | `price × quantity` |
| notes | string nullable | Catatan per item (contoh: "jangan pakai bawang") |
| created_at / updated_at | timestamp | |

---

## 4. Alur Aplikasi

### Alur Pelanggan
1. Pelanggan duduk di meja → scan QR Code
2. Diarahkan ke halaman menu publik: `kopisusu.appkamu.com/menu/{qr_code_string}`
3. Browse menu, tambah item ke cart (Alpine.js)
4. Checkout → isi nama & nomor WA (opsional)
5. Order tersimpan dengan `status: new`, `payment_status: pending`
6. Halaman payment muncul (Midtrans Snap pop-up)
7. Setelah bayar → webhook Midtrans update `payment_status: paid`
8. Pelanggan terima e-receipt via WhatsApp

### Alur Kasir / Admin
1. Login ke `kopisusu.appkamu.com/admin`
2. Dashboard real-time menampilkan pesanan baru via Laravel Reverb (WebSocket)
3. Kasir update status pesanan: `new` → `processing` → `ready` → `completed`
4. Admin bisa kelola menu, kategori, meja, dan lihat laporan

---

## 5. Development Increments

### ✅ Increment 1: Core CRUD & Order Flow (MVP)
- Setup Laravel 12 + Breeze + Spatie Permission
- Migrasi semua tabel & model Eloquent dengan relasi lengkap
- Middleware `ResolveTenant` untuk identifikasi tenant via subdomain
- Dashboard Admin: CRUD Kategori, Produk, Meja
- Generator QR Code dinamis per meja (`simplesoftwareio/simple-qrcode`)
- Halaman Publik: Katalog Menu (Mobile-First, Tailwind)
- Fitur Cart menggunakan Alpine.js + Laravel Session
- Halaman Checkout sederhana → order tersimpan status `new` / `pending`

### ⏳ Increment 2: Payment Gateway & Real-time Dashboard
- Integrasi Midtrans Snap (Sandbox) → generate `payment_url` saat checkout
- Route Webhook `/webhook/midtrans` → update `payment_status` jadi `paid` otomatis
- Setup & instalasi Laravel Reverb
- Event Broadcasting: `OrderPlaced` & `OrderStatusUpdated`
- Dashboard kasir real-time: pesanan baru muncul tanpa refresh (Alpine.js + Reverb)
- Notifikasi audio/visual saat pesanan baru masuk ke kasir

### ⏳ Increment 3: Operational Management & Laporan
- Tombol update status pesanan di dashboard kasir (`new → processing → ready → completed`)
- Halaman Riwayat Transaksi dengan filter tanggal & status
- Laporan penjualan: ringkasan per hari/minggu/bulan
- Export laporan ke PDF (`barryvdh/laravel-dompdf`)

### ⏳ Increment 4: SaaS Architecture & Notifikasi
- Terapkan Eloquent Global Scope untuk auto-filter `tenant_id` di semua model utama
- Integrasi WhatsApp API → kirim e-receipt otomatis setelah `payment_status: paid`
- Halaman pengaturan tenant (upload logo, warna tema, nomor WA)
- Pastikan isolasi data antar tenant berjalan sempurna (testing multi-tenant)

### ⏳ Increment 5: Tenant Onboarding & SaaS Polish
- Halaman registrasi tenant baru (self-service onboarding)
- Kustomisasi white-label per tenant (logo, warna primer)
- Landing page publik untuk marketing SaaS
- Paket/plan subscription opsional (Free Trial, Basic, Pro)

---

## 6. Konvensi & Aturan Kode

### Naming Convention
- **Model:** PascalCase singular → `Order`, `OrderItem`, `Tenant`
- **Controller:** PascalCase + `Controller` → `OrderController`, `ProductController`
- **Migration:** snake_case → `create_orders_table`
- **Blade View:** snake_case, folder sesuai fitur → `admin/products/index.blade.php`
- **Route name:** dot notation → `admin.products.index`, `menu.show`

### Database
- Selalu gunakan `foreignId('tenant_id')->constrained()->cascadeOnDelete()` untuk foreign key ke tenants
- Gunakan `foreignId('xxx_id')->constrained()->cascadeOnDelete()` untuk semua FK
- Semua tabel wajib punya `timestamps()`

### Keamanan & Konfigurasi
- Semua API Key, Secret, credential **WAJIB** disimpan di `.env`, tidak boleh di-hardcode
- Midtrans keys: `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION=false`
- Gunakan `config/services.php` sebagai jembatan antara `.env` dan kode

---

## 7. Strict AI Rules (Aturan untuk AI Assistant)

1. Selalu tulis kode yang efisien, bersih, dan mematuhi best practices Laravel 12.
2. Gunakan Tailwind CSS utility classes secara maksimal. **Hindari custom CSS manual**.
3. Manfaatkan Alpine.js untuk interaktivitas: cart drawer, modal, dropdown, counter quantity.
4. Saat memberikan respons kode, **berikan secara utuh** untuk mempermudah copy-paste.
5. Selalu sebutkan **path lengkap file** dari root project (contoh: `app/Models/Order.php`).
6. **FOKUS INCREMENT:** Jangan menulis kode untuk Increment selanjutnya jika Increment yang sedang dikerjakan belum selesai dan berjalan baik.
7. Setiap membuat migration, gunakan `foreignId()->constrained()->cascadeOnDelete()` untuk foreign key.
8. Gunakan Laravel Resource (`php artisan make:resource`) jika ada endpoint yang mengembalikan JSON.
9. Semua konfigurasi sensitif (API Key, Secret) **WAJIB** di `.env`, bukan di-hardcode.
10. UI halaman publik (menu pelanggan) harus **Mobile-First** — mayoritas user akses via smartphone setelah scan QR.
11. Untuk setiap fitur baru, ikuti urutan: **Migration → Model → Controller → Route → View**.
12. Tidak ada mobile app — tidak perlu API endpoint berbasis token/Sanctum. Semua berbasis session web.