# Cafmu - Sistem Pemesanan Kafe Multi-Tenant

Aplikasi sistem pemesanan kafe (SaaS) berbasis QR Code dengan fitur **Multi-Tenant**, pembayaran menggunakan **Midtrans**, dan notifikasi kasir *real-time* menggunakan **Laravel Reverb (WebSockets)**.

## 🚀 Panduan Menjalankan Aplikasi (Development Lokal)

Karena aplikasi ini menggunakan berbagai fitur *real-time* dan pemrosesan antrean di latar belakang, Anda perlu membuka **5 Tab Terminal** secara bersamaan saat tahap *development*.

Buka terminal Anda di dalam folder proyek (`c:\Cafmu`), lalu jalankan perintah-perintah berikut di masing-masing tab yang berbeda:

### Terminal 1: Web Server (Laravel)
Menjalankan server utama PHP.
```bash
php artisan serve
```

### Terminal 2: Frontend Asset Bundler (Vite)
Mengkompilasi TailwindCSS dan Alpine.js secara *real-time*.
```bash
npm run dev
```

### Terminal 3: WebSocket Server (Laravel Reverb)
Menjalankan server WebSocket untuk fitur *real-time* pembaruan pesanan di layar kasir.
```bash
php artisan reverb:start
```

### Terminal 4: Queue Worker (Pekerja Antrean)
Memproses tugas latar belakang seperti mengirimkan *broadcast* WebSocket (`ShouldBroadcast`) dan pekerjaan tertunda lainnya.
```bash
php artisan queue:work
```

### Terminal 5: Ngrok Tunneling (Wajib untuk Midtrans)
Mengekspos `localhost:8000` ke internet publik agar server Midtrans bisa mengirimkan Webhook notifikasi pembayaran yang sukses ke laptop Anda.
```bash
ngrok http 8000
```
*(Catatan: Salin URL HTTPS yang diberikan oleh Ngrok, dan masukkan ke Dashboard Midtrans Sandbox -> Settings -> Configuration -> Notification URL: `https://[url-ngrok-anda].ngrok-free.app/webhook/midtrans`)*

---

## 🔗 URL Akses Penting

### 1. Dashboard Admin Kasir
Karena ini adalah sistem multi-tenant, Anda harus memberitahu *middleware* untuk memuat data tenant tertentu di percobaan pertama.

**URL:** `http://localhost:8000/admin?tenant=demo`
*(Ganti `demo` dengan nama subdomain tenant yang ada di database)*

### 2. Menu Pelanggan (QR Code)
Biasanya URL ini didapatkan otomatis dari hasil *scan* QR Code di halaman Meja. Namun jika ingin mengaksesnya langsung secara manual di lokal:

**URL:** `http://localhost:8000/menu/{qr_code_string}?tenant=demo`
*(Ganti `{qr_code_string}` dengan string acak yang ada di tabel `tables` database Anda)*

---

## 🛠️ Fitur Utama
1. **Multi-Tenant Architecture:** Middleware `ResolveTenant` menangani isolasi data (Kategori, Produk, Pesanan, Meja) berdasarkan query parameter atau subdomain.
2. **Midtrans Snap Integration:** Pembuatan link pembayaran otomatis (*Snap Token*) saat proses *checkout*.
3. **Midtrans Webhook Handler:** Menerima notifikasi otomatis saat pembayaran lunas/gagal dan memperbarui status pesanan.
4. **Real-Time Dashboard (Reverb):** Menggunakan *Event Broadcasting* agar layar pesanan admin otomatis ter-*refresh* tanpa perlu dimuat ulang saat ada pesanan baru atau pembayaran masuk.
