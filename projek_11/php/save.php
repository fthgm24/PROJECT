<?php
/**
 * Handler Simpan Transaksi Ke Database 'pkk' (Sesuai Akun Terkonfirmasi)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/session.php';

// Dapatkan user_id dari session akun aktif
$user_id = get_active_user_id();

$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category    = isset($_POST['category']) ? trim($_POST['category']) : 'Sewa Booth';
$amount      = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$type        = isset($_POST['type']) ? trim($_POST['type']) : 'Pemasukan';
$date        = isset($_POST['date']) && !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');

if (empty($description) || $amount <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Keterangan dan Nominal transaksi wajib diisi!'
    ]);
    exit;
}

if ($type !== 'Pemasukan' && $type !== 'Pengeluaran') {
    $type = 'Pemasukan';
}

$query = "INSERT INTO transactions (user_id, date, description, type, category, amount) VALUES (?, ?, ?, ?, ?, ?)";
$stmt  = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "issssd", $user_id, $date, $description, $type, $category, $amount);
    
    if (mysqli_stmt_execute($stmt)) {
        $insert_id = mysqli_insert_id($conn);
        echo json_encode([
            'success' => true,
            'message' => 'Transaksi berhasil disimpan ke akun Anda di database pkk!',
            'id' => $insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Gagal menyimpan transaksi: ' . mysqli_stmt_error($stmt)
        ]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Query database error: ' . mysqli_error($conn)
    ]);
}
?>
