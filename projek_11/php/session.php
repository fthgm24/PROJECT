<?php
/**
 * Helper Session Management Akun
 * Memastikan setiap akun melihat & menyimpan data ke database 'pkk' sesuai ID akun masing-masing
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mendapatkan User ID dari akun yang sedang aktif/login.
 * Jika belum ada session login, mengembalikan ID akun default (1).
 */
function get_active_user_id() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }
    return 1;
}

/**
 * Mendapatkan Data User lengkap yang sedang aktif/login.
 */
function get_active_user($conn) {
    $user_id = get_active_user_id();
    $stmt = mysqli_prepare($conn, "SELECT id, name, email, role, business_name FROM users WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if ($user) return $user;
    }
    return [
        'id' => 1,
        'name' => 'Astor',
        'email' => 'admin@sistemkeuangan.id',
        'role' => 'Administrator',
        'business_name' => 'Keuangan Saya'
    ];
}
?>
