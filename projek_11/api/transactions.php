<?php
/**
 * API CRUD Transaksi Keuangan ke Database 'pkk' (Sesuai Akun Terkonfirmasi)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/koneksi.php';
require_once __DIR__ . '/../php/session.php';

$user_id = get_active_user_id();
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Ambil transaksi hanya milik akun ini
    $stmt = mysqli_prepare($conn, "SELECT * FROM transactions WHERE user_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
    
    echo json_encode(['success' => true, 'user_id' => $user_id, 'data' => $data]);
    exit;
}

if ($method === 'POST') {
    // Tambah transaksi baru untuk akun ini
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $category    = isset($_POST['category']) ? trim($_POST['category']) : 'Sewa Booth';
    $amount      = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
    $type        = isset($_POST['type']) ? trim($_POST['type']) : 'Pemasukan';
    $date        = isset($_POST['date']) && !empty($_POST['date']) ? $_POST['date'] : date('Y-m-d');

    if (empty($description) || $amount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Keterangan dan Nominal transaksi wajib diisi!']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO transactions (user_id, date, description, type, category, amount) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issssd", $user_id, $date, $description, $type, $category, $amount);
        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($conn);
            echo json_encode(['success' => true, 'message' => 'Transaksi berhasil disimpan ke database pkk!', 'id' => $new_id]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
    exit;
}

if ($method === 'DELETE') {
    // Hapus transaksi hanya milik akun ini
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM transactions WHERE id = ? AND user_id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dihapus.']);
            } else {
                echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID Transaksi tidak valid']);
    }
    exit;
}
?>
