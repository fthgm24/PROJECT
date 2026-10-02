# 📸 Sistem Keuangan - Sistem Manajemen Keuangan Dashboard

Sistem manajemen keuangan berbasis web yang intuitif, modern, dan interaktif untuk mengelola pendapatan, pengeluaran, serta jadwal event usaha Anda.

**Tanpa database!** Semua data disimpan dalam file JSON di folder `data/`, sehingga seluruh fitur langsung bisa dibuka tanpa perlu MySQL/phpMyAdmin. Cukup jalankan di server PHP (XAMPP, Laragon, PHP built-in server).

---

## 🌟 Fitur Utama

- 📊 **Dashboard Real-Time**:
  - **3 Kartu Statistik**: *Total Pemasukan*, *Total Pengeluaran*, dan *Keuntungan Bersih* yang otomatis terhitung ulang saat transaksi bertambah atau dihapus.
  - **Grafik Batang Interaktif (Chart.js)**: Visualisasi perbandingan pemasukan dan pengeluaran per bulan.
  - **Filter Periode Grafik**: Pilihan tampilan 6 Bulan Terakhir, 3 Bulan Terakhir, atau Tahun Ini.

- 💰 **Manajemen Transaksi (CRUD Real-Time)**:
  - Form modal **Tambah Pemasukan** & **Tambah Pengeluaran**.
  - Fitur **Pencarian Transaksi** berdasarkan keterangan atau kategori.
  - **Filter Jenis Transaksi** (Semua / Pemasukan / Pengeluaran).
  - Penghapusan transaksi dengan konfirmasi aman.

- 📥 **Export Laporan Keuangan**:
  - Unduh seluruh data riwayat transaksi keuangan langsung ke file **Excel / CSV** dengan sekali klik.
  - Visualisasi persentase pengeluaran & pemasukan berdasarkan kategori (*Progress Bar*).

- 📅 **Jadwal Event**:
  - Pencatatan jadwal pemesanan / booking event mendatang (*Wedding, Wisuda, Birthday Party*).
  - Penambahan event baru via form modal.

- 🏷️ **Manajemen Kategori**:
  - Pengelolaan kategori pemasukan (*Sewa Booth, Merchandise, Print*) dan pengeluaran (*Operasional, Transportasi, Maintenance*).

- ⚙️ **Pengaturan & Tampilan**:
  - **Mode Gelap (Dark Mode)**: Pengalihan tema terang / gelap secara instan.
  - Pengaturan profil usaha dan nama admin.
  - **Notifikasi Toast**: Pop-up konfirmasi aksi interaktif di pojok layar.

- 📱 **Desain Responsif**:
  - Optimal di layar Desktop, Tablet, hingga Smartphone dengan dukungan menu *Hamburger Drawer*.

---

## 📁 Struktur Berkas Proyek

```text
projek_11/
├── login.php         # Halaman landing & portal login sistem
├── index.php         # Berkas utama aplikasi web dashboard
├── awal.php          # Berkas alternatif aplikasi web dashboard
├── config/
│   ├── storage.php   # Penyimpanan JSON (pengganti database) & seed data demo
│   └── session.php   # Session & auth helpers
├── api/
│   ├── login.php     # Endpoint login
│   ├── register.php  # Endpoint registrasi
│   ├── logout.php    # Endpoint logout
│   ├── transactions.php # CRUD transaksi
│   ├── categories.php   # CRUD kategori
│   ├── events.php       # CRUD event
│   ├── stats.php        # Statistik dashboard
│   └── settings.php     # Pengaturan profil
├── data/             # Data JSON dibuat otomatis saat pertama kali dijalankan
├── css/              # Stylesheets
├── js/               # JavaScript (AJAX + Chart.js)
└── README.md         # Dokumentasi proyek
```

---

## 🚀 Cara Menggunakan

Tidak perlu install MySQL, import SQL, atau mengatur koneksi database apa pun.

### 1. Jalankan di Server PHP

Jalankan project melalui server PHP (XAMPP, Laragon, atau PHP built-in server):
```bash
php -S localhost:8000
```

Buka browser ke: `http://localhost:8000/login.php`

### 2. Data Otomatis

Folder `data/` dan file JSON dibuat otomatis saat aplikasi pertama kali diakses, lengkap dengan **data demo**.

### 3. Login Demo

- **Email:** admin@sistemkeuangan.id
- **Password:** admin123

Atau gunakan tombol **⚡ Demo Admin** di halaman login, atau daftarkan usaha baru lewat tab **📝 Daftar Usaha**.

---

## 🛠️ Teknologi yang Digunakan

- **PHP 8+**: Backend API & session-based authentication
- **JSON File Storage**: Pengganti database (folder `data/`)
- **HTML5**: Struktur halaman semantik.
- **CSS3 (Vanilla)**: Desain kustom modern, CSS Grid & Flexbox, Animasi Smooth, CSS CSS Variables, Mode Gelap.
- **JavaScript (ES6+)**: AJAX Fetch API, Manipulasi DOM, Toast System.
- **Chart.js**: Library visualisasi grafik batang ganda.
- **Google Fonts**: Tipografi modern *Plus Jakarta Sans*.

---

## 📝 Lisensi & Kredit

Dibuat untuk pengelolaan keuangan bisnis. Bebas disesuaikan dan dikembangkan lebih lanjut.
