<?php
/**
 * Entry Point Dashboard Awal
 */
require_once __DIR__ . '/php/session.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/awal.html';
?>
