<?php
/**
 * Entry Point Halaman Login
 * Menampilkan Form Login di Awal jika belum terautentikasi
 */
require_once __DIR__ . '/php/session.php';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include __DIR__ . '/login.html';
?>
