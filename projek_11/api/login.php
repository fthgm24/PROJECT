<?php
/**
 * API Login Akun ke Database 'pkk'
 * Mendukung login menggunakan Nama (Username) atau Email
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/koneksi.php';
require_once __DIR__ . '/../php/session.php';

$login_input = isset($_POST['email']) ? trim($_POST['email']) : '';
$password    = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($login_input) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Username/Email dan password harus diisi!']);
    exit;
}

// Cari user berdasarkan nama ATAU email di database pkk
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? OR name = ? LIMIT 1");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user) {
        // Verifikasi password: coba password_verify dulu, fallback ke perbandingan langsung
        $is_valid = password_verify($password, $user['password']) || $password === $user['password'];
        
        if ($is_valid) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user']    = [
                'id'            => $user['id'],
                'name'          => $user['name'],
                'email'         => $user['email'],
                'role'          => $user['role'],
                'business_name' => $user['business_name'] ?? 'Keuangan Saya'
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil!',
                'user'    => $_SESSION['user']
            ]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'error' => 'Username/Email atau password yang Anda masukkan salah!']);
?>

