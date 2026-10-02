<?php
/**
 * API Registrasi Akun Baru ke Database 'pkk'
 * Menyimpan Akun Baru, Settings, & Kategori Default ke Database
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/koneksi.php';
require_once __DIR__ . '/../php/session.php';

$name          = isset($_POST['name']) ? trim($_POST['name']) : '';
$business_name = isset($_POST['business_name']) ? trim($_POST['business_name']) : 'Usaha Saya';
$email         = isset($_POST['email']) ? trim($_POST['email']) : '';
$password      = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Harap lengkapi nama, email, dan password!']);
    exit;
}

// Cek apakah email atau nama sudah terdaftar di database pkk
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? OR name = ? LIMIT 1");
if ($checkStmt) {
    mysqli_stmt_bind_param($checkStmt, "ss", $email, $name);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);
    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        echo json_encode(['success' => false, 'error' => 'Email atau nama pengguna sudah terdaftar! Silakan login atau gunakan nama lain.']);
        mysqli_stmt_close($checkStmt);
        exit;
    }
    mysqli_stmt_close($checkStmt);
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
// Role default untuk pendaftar baru (sesuai ENUM: 'Administrator','Manager Keuangan','Staff Booth')
$role = 'Staff Booth';

// Insert akun baru ke tabel users di database pkk
$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, business_name) VALUES (?, ?, ?, ?, ?)");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $role, $business_name);
    if (mysqli_stmt_execute($stmt)) {
        $new_user_id = mysqli_insert_id($conn);
        
        // Buat settings default untuk akun baru ini di database pkk
        $setStmt = mysqli_prepare($conn, "INSERT INTO settings (user_id, business_name, admin_name, email) VALUES (?, ?, ?, ?)");
        if ($setStmt) {
            mysqli_stmt_bind_param($setStmt, "isss", $new_user_id, $business_name, $name, $email);
            mysqli_stmt_execute($setStmt);
            mysqli_stmt_close($setStmt);
        }

        // Inisialisasi Kategori Default Akun Baru
        $default_categories = [
            ['name' => 'Sewa Booth', 'type' => 'Pemasukan'],
            ['name' => 'Cetak Foto Tambahan', 'type' => 'Pemasukan'],
            ['name' => 'Merchandise', 'type' => 'Pemasukan'],
            ['name' => 'Operasional', 'type' => 'Pengeluaran'],
            ['name' => 'Transportasi', 'type' => 'Pengeluaran'],
            ['name' => 'Maintenance Alat', 'type' => 'Pengeluaran']
        ];
        
        $catStmt = mysqli_prepare($conn, "INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
        if ($catStmt) {
            foreach ($default_categories as $cat) {
                mysqli_stmt_bind_param($catStmt, "iss", $new_user_id, $cat['name'], $cat['type']);
                mysqli_stmt_execute($catStmt);
            }
            mysqli_stmt_close($catStmt);
        }

        // Set session ke akun baru
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['user'] = [
            'id' => $new_user_id,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'business_name' => $business_name
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Pendaftaran akun baru berhasil disembung ke database!',
            'user' => $_SESSION['user']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal mendaftar: ' . mysqli_stmt_error($stmt) . ' | DB: ' . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'error' => 'Database prepare error: ' . mysqli_error($conn)]);
}
?>
