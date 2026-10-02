<?php
/**
 * API User Info - Mengembalikan data pengguna aktif dari session
 * Digunakan oleh Dashboard untuk menampilkan nama & role pengguna
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/koneksi.php';
require_once __DIR__ . '/../php/session.php';

$user_id = get_active_user_id();

// Ambil data user langsung dari tabel users
$stmt = mysqli_prepare($conn, "SELECT id, name, email, role, business_name FROM users WHERE id = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }
}

// Fallback: ambil dari session jika DB tidak ada
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'User tidak ditemukan']);
?>
