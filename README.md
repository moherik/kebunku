# KebunKu 🌿

KebunKu adalah Sistem Informasi Pertanian dan Manajemen Panen yang dirancang untuk membantu petani mengelola penanaman mereka serta memberikan transparansi jadwal panen kepada publik dan calon pembeli.

## ✨ Fitur Utama

### 👨‍🌾 Untuk Petani
- **Manajemen Penanaman**: Kelola data tanaman, varietas, luas lahan, jumlah pohon, hingga estimasi hasil panen (kg).
- **Dashboard Kebun**: Pantau status tanaman (Masa Tanam, Pre-Order, Siap Panen, Selesai).
- **Progress Log**: Catat perkembangan tanaman disertai foto secara *real-time*.
- **Profil Kebun Kustom**: Atur nama kebun, alamat, deskripsi, serta unggah logo dan cover kebun dengan fitur *cropping* terintegrasi.
- **Kalender Panen Pribadi**: Lihat jadwal panen semua tanaman dalam satu tampilan kalender yang intuitif.

### 🌎 Untuk Publik & Pembeli
- **Profil Kebun Publik**: Lihat detail kebun, lokasi, kontak media sosial (WhatsApp, Instagram, Facebook), dan daftar tanaman aktif.
- **Kalender Panen Publik**: Temukan jadwal panen dari berbagai kebun untuk merencanakan pembelian hasil tani segar.
- **Detail Tanaman**: Lihat histori perkembangan tanaman dari awal tanam hingga siap panen melalui *thread* log.

## 🛠️ Teknologi yang Digunakan

- **Backend**: [Laravel 11](https://laravel.com)
- **Frontend**: [Vue 3](https://vuejs.org) dengan Script Setup
- **Bridge**: [InertiaJS](https://inertiajs.com)
- **Styling**: [Tailwind CSS](https://tailwindcss.com)
- **Icons**: [Lucide Vue Next](https://lucide.dev)
- **Komponen Tambahan**:
  - `vue-advanced-cropper` untuk pengelolaan gambar profil.
  - DatePicker kustom untuk input tanggal yang konsisten.
  - FullCalendar untuk visualisasi jadwal panen.

## 🚀 Cara Menjalankan Projek

1. **Clone repositori**:
   ```bash
   git clone https://github.com/moherik/kebunku.git
   cd kebunku
   ```

2. **Instal dependensi**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Pastikan untuk mengatur konfigurasi database di file `.env`.*

4. **Migrasi dan Seed Database**:
   ```bash
   php artisan migrate --seed
   ```

5. **Hubungkan Storage**:
   ```bash
   php artisan storage:link
   ```

6. **Jalankan Aplikasi**:
   ```bash
   # Jalankan Vite server
   npm run dev

   # Jalankan Laravel server
   php artisan serve
   ```

---
Dibuat dengan ❤️ untuk kemajuan petani lokal.
