<?php
/**
 * File Koneksi Database MySQL
 * Database Name: pkk
 */

$host = "localhost";
$user = "root";
$pass = ""; // Default Laragon/XAMPP password kosong
$db   = "pkk";

// Membuat koneksi ke MySQL
$conn = mysqli_connect($host, $user, $pass, $db);

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi ke database '$db' gagal: " . mysqli_connect_error());
}

// Set karakter encoding ke utf8mb4
mysqli_set_charset($conn, "utf8mb4");

// Notifikasi tes koneksi (bisa dikomentari jika digunakan dalam aplikasi)
// echo "Koneksi berhasil ke database: " . $db;
?>