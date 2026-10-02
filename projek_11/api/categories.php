<?php
/**
 * API Categories untuk Database 'pkk' (Sesuai Akun Terkonfirmasi)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/koneksi.php';
require_once __DIR__ . '/../php/session.php';

$user_id = get_active_user_id();
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = mysqli_prepare($conn, "SELECT c.*, COUNT(t.id) AS transaction_count 
              FROM categories c 
              LEFT JOIN transactions t ON (t.category = c.name AND t.user_id = c.user_id) 
              WHERE c.user_id = ?
              GROUP BY c.id ORDER BY c.id ASC");
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
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if ($method === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : 'Pemasukan';

    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Nama kategori tidak boleh kosong!']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO categories (user_id, name, type) VALUES (?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $name, $type);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Kategori berhasil ditambahkan!']);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
    exit;
}
?>
