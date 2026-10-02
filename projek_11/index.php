<?php
/**
 * Main Entry Point Sistem Keuangan
 * Jika pengguna belum login, langsung alihkan ke form login pertama kali
 */
require_once __DIR__ . '/php/session.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/awal.html';
?>
