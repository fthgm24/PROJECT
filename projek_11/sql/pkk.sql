-- Database: pkk
-- Sesuai dengan phpMyAdmin (5 tabel: categories, events, settings, transactions, users)

CREATE DATABASE IF NOT EXISTS pkk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pkk;

-- 1. Tabel users (2 Rows)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('Administrator','Manager Keuangan','Staff Booth') DEFAULT 'Administrator',
  business_name VARCHAR(150) DEFAULT 'Keuangan Saya',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel settings (1 Row)
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  business_name VARCHAR(150) DEFAULT 'Keuangan Saya',
  admin_name VARCHAR(100) DEFAULT '',
  email VARCHAR(100) DEFAULT '',
  theme ENUM('light','dark') DEFAULT 'light',
  currency VARCHAR(10) DEFAULT 'IDR',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Tabel categories (6 Rows)
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  type ENUM('Pemasukan','Pengeluaran') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabel transactions (5 Rows)
CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATE NOT NULL,
  description VARCHAR(255) NOT NULL,
  type ENUM('Pemasukan','Pengeluaran') NOT NULL,
  category VARCHAR(100) NOT NULL,
  amount BIGINT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabel events (3 Rows)
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  event_name VARCHAR(200) NOT NULL,
  event_date DATE NOT NULL,
  location VARCHAR(255) NOT NULL,
  package VARCHAR(100) NOT NULL,
  status ENUM('Selesai','Mendatang','Dibatalkan') DEFAULT 'Mendatang',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed Data Users (2 rows)
INSERT INTO users (name, email, password, role, business_name) VALUES
('Astor', 'admin@sistemkeuangan.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'Keuangan Saya'),
('Staff Keuangan', 'staff@sistemkeuangan.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff Booth', 'Keuangan Saya');

-- Seed Data Settings (1 row)
INSERT INTO settings (user_id, business_name, admin_name, email) VALUES
(1, 'Keuangan Saya', 'Astor', 'admin@sistemkeuangan.id');

-- Seed Data Categories (6 rows)
INSERT INTO categories (user_id, name, type) VALUES
(1, 'Sewa Booth', 'Pemasukan'),
(1, 'Cetak Foto Tambahan', 'Pemasukan'),
(1, 'Merchandise', 'Pemasukan'),
(1, 'Operasional', 'Pengeluaran'),
(1, 'Transportasi', 'Pengeluaran'),
(1, 'Maintenance Alat', 'Pengeluaran');

-- Seed Data Transactions (5 rows)
INSERT INTO transactions (user_id, date, description, type, category, amount) VALUES
(1, '2026-07-22', 'Wedding A', 'Pemasukan', 'Sewa Booth', 800000),
(1, '2026-07-22', 'Beli Kertas Foto', 'Pengeluaran', 'Operasional', 120000),
(1, '2026-07-21', 'Wisuda B', 'Pemasukan', 'Sewa Booth', 600000),
(1, '2026-07-21', 'Transportasi Event', 'Pengeluaran', 'Transportasi', 80000),
(1, '2026-07-20', 'Birthday Party', 'Pemasukan', 'Sewa Booth', 500000);

-- Seed Data Events (3 rows)
INSERT INTO events (user_id, event_name, event_date, location, package, status) VALUES
(1, 'Wedding A (Budi & Annisa)', '2026-07-22', 'Hotel Mulia, Jakarta', 'Paket Unlimited 4 Jam', 'Selesai'),
(1, 'Wisuda B (Universitas X)', '2026-07-21', 'Balai Kartini', 'Paket 3 Jam', 'Selesai'),
(1, 'Birthday Party C (Rina 17th)', '2026-07-28', 'Cafe Skyline', 'Paket 2 Jam', 'Mendatang');
