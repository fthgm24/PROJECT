<?php
/**
 * File Koneksi Database MySQL menggunakan PDO (PHP Data Objects)
 * Database Name: pkk
 */

$host = "localhost";
$users = "root";
$pass = "";
$db   = "pkk";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $users, $pass, $options);
    // echo "Koneksi PDO berhasil ke database: " . $db;
} catch (PDOException $e) {
    die("Koneksi PDO Gagal: " . $e->getMessage());
}
?>
