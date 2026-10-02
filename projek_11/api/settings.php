<?php
/**
 * API Settings untuk Database 'pkk' (Sesuai Akun Terkonfirmasi)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/koneksi.php';
require_once __DIR__ . '/../php/session.php';

$user_id = get_active_user_id();
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM settings WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data) {
        $data = [
            'business_name' => 'Keuangan Saya',
            'admin_name'    => 'Admin',
            'email'         => 'admin@sistemkeuangan.id',
            'theme'         => 'light',
            'currency'      => 'IDR'
        ];
    }
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if ($method === 'POST') {
    $business_name = isset($_POST['business_name']) ? trim($_POST['business_name']) : 'Keuangan Saya';
    $admin_name    = isset($_POST['admin_name']) ? trim($_POST['admin_name']) : 'Admin';
    $email         = isset($_POST['email']) ? trim($_POST['email']) : 'admin@sistemkeuangan.id';
    $theme         = isset($_POST['theme']) ? trim($_POST['theme']) : 'light';
    $currency      = isset($_POST['currency']) ? trim($_POST['currency']) : 'IDR';

    $checkStmt = mysqli_prepare($conn, "SELECT id FROM settings WHERE user_id = ?");
    mysqli_stmt_bind_param($checkStmt, "i", $user_id);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $hasSettings = mysqli_num_rows($checkResult) > 0;
    mysqli_stmt_close($checkStmt);

    if ($hasSettings) {
        $stmt = mysqli_prepare($conn, "UPDATE settings SET business_name = ?, admin_name = ?, email = ?, theme = ?, currency = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, "sssssi", $business_name, $admin_name, $email, $theme, $currency, $user_id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO settings (user_id, business_name, admin_name, email, theme, currency) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isssss", $user_id, $business_name, $admin_name, $email, $theme, $currency);
    }

    if ($stmt && mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil diperbarui!']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
    exit;
}
?>
