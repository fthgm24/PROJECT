<?php
/**
 * API Events untuk Database 'pkk' (Sesuai Akun Terkonfirmasi)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/koneksi.php';
require_once __DIR__ . '/../php/session.php';

$user_id = get_active_user_id();
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE user_id = ? ORDER BY event_date ASC");
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
    $event_name = isset($_POST['event_name']) ? trim($_POST['event_name']) : '';
    $event_date = isset($_POST['event_date']) ? $_POST['event_date'] : date('Y-m-d');
    $location   = isset($_POST['location']) ? trim($_POST['location']) : '';
    $package    = isset($_POST['package']) ? trim($_POST['package']) : '';

    if (empty($event_name)) {
        echo json_encode(['success' => false, 'error' => 'Nama event wajib diisi!']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO events (user_id, event_name, event_date, location, package, status) VALUES (?, ?, ?, ?, ?, 'Mendatang')");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issss", $user_id, $event_name, $event_date, $location, $package);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Event berhasil disimpan!']);
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
