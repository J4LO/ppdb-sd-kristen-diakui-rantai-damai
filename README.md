# 🏫 PPDB SD Kristen Diakui Rantai Damai

Sistem **Penerimaan Peserta Didik Baru (PPDB)** berbasis web untuk **SD Kristen Diakui Rantai Damai**. Dibangun menggunakan **Laravel 12** dengan antarmuka modern menggunakan **Tailwind CSS v4**.

---

## 📋 Deskripsi Proyek

Sistem ini menyediakan platform digital terpadu untuk mengelola seluruh proses penerimaan peserta didik baru, mulai dari pendaftaran mandiri oleh calon siswa, pengelolaan data oleh panitia, hingga pengumuman hasil seleksi oleh administrator.

---

## ✨ Fitur Utama

### 🌐 Publik (Tanpa Login)
- Halaman landing page informatif (profil sekolah, alur pendaftaran, kontak)
- Pendaftaran akun calon siswa baru
- Login multi-role

### 👨‍🎓 Calon Siswa (`/beranda/casis`)
- Dashboard informasi tahun ajaran & status pendaftaran
- Pengisian & pengeditan data diri secara mandiri (per kolom, *inline editing*)
- Unggah berkas persyaratan (akta lahir, KK, dsb.)
- Pantau pengumuman hasil seleksi & daftar ulang

### 🗂️ Panitia (`/beranda/panitia`)
- Dashboard statistik pendaftar real-time
- Manajemen data pendaftaran calon siswa
- Input hasil seleksi & pengelolaan daftar ulang

### 🔑 Admin (`/admin/dashboard`)
- Dashboard admin dengan stat card (Tahun Ajaran, Kuota, Total Pendaftar, dll.)
- **Data Master**: Kelola data Calon Siswa, Panitia, Tahun Ajaran, Kuota
- **Laporan**: Rekap hasil seleksi & daftar ulang
- **Pengaturan**: Konfigurasi landing page

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend Framework | Laravel 12 (PHP 8.3) |
| Frontend CSS | Tailwind CSS v4 |
| Build Tool | Vite 5 |
| Database | MySQL |
| JS Library | Alpine.js v3 |
| Icon | Font Awesome 6 |
| Font | Inter, Outfit (Google Fonts) |

---

## 🚀 Cara Menjalankan (Development)

### 1. Clone & Install Dependencies

```bash
# Clone repository (Ganti <URL_REPOSITORY> dengan URL repo yang sebenarnya)
git clone <URL_REPOSITORY>

# Masuk ke direktori proyek
cd ppdb-sd-kristen-diakui-rantai-damai

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Konfigurasi Environment

```bash
# Salin file .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ppdb-sd-kristen-diakui-rantai-damai
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Jalankan Migrasi Database

```bash
php artisan migrate
```

### 4. Build Assets & Jalankan Server

```bash
# Build CSS/JS untuk production
npm run build

# Atau untuk development (hot reload)
npm run dev

# Jalankan server Laravel
php artisan serve
```

Aplikasi dapat diakses di: **http://localhost:8000**

---

## 📁 Struktur URL Utama

```
/                       → Landing page publik
/admin/dashboard        → Dashboard Admin
/admin/data/casis       → Data Calon Siswa (Admin)
/admin/data/panitia     → Data Panitia (Admin)
/admin/data/tahun-ajar  → Data Tahun Ajaran (Admin)
/admin/laporan          → Laporan (Admin)
/beranda/panitia        → Dashboard Panitia
/beranda/casis          → Dashboard Calon Siswa
/beranda/form           → Form Data Diri Calon Siswa
```

---

## 📄 Lisensi

Proyek ini dikembangkan khusus untuk kebutuhan internal **SD Kristen Diakui Rantai Damai**. Seluruh hak cipta dilindungi.
