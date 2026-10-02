<?php
/**
 * API Statistik Keuangan dari Database 'pkk' (Sesuai Akun Terkonfirmasi)
 * Menghitung Total Pemasukan, Pengeluaran, Keuntungan Bersih, Ringkasan Bulan Ini & Data Grafik Realtime
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../php/koneksi.php';
require_once __DIR__ . '/../php/session.php';

$user_id = get_active_user_id();
$currentMonthKey = date('Y-m');

// 1. Total Pemasukan Keseluruhan
$stmtPem = mysqli_prepare($conn, "SELECT SUM(amount) AS total FROM transactions WHERE type = 'Pemasukan' AND user_id = ?");
mysqli_stmt_bind_param($stmtPem, "i", $user_id);
mysqli_stmt_execute($stmtPem);
$resPem = mysqli_stmt_get_result($stmtPem);
$rowPem = mysqli_fetch_assoc($resPem);
$total_pemasukan = (float)($rowPem['total'] ?? 0);
mysqli_stmt_close($stmtPem);

// 2. Total Pengeluaran Keseluruhan
$stmtPeng = mysqli_prepare($conn, "SELECT SUM(amount) AS total FROM transactions WHERE type = 'Pengeluaran' AND user_id = ?");
mysqli_stmt_bind_param($stmtPeng, "i", $user_id);
mysqli_stmt_execute($stmtPeng);
$resPeng = mysqli_stmt_get_result($stmtPeng);
$rowPeng = mysqli_fetch_assoc($resPeng);
$total_pengeluaran = (float)($rowPeng['total'] ?? 0);
mysqli_stmt_close($stmtPeng);

// 3. Keuntungan Bersih Keseluruhan
$keuntungan_bersih = $total_pemasukan - $total_pengeluaran;

// 4. Total Pemasukan Bulan Ini
$stmtPemBulan = mysqli_prepare($conn, "SELECT SUM(amount) AS total FROM transactions WHERE type = 'Pemasukan' AND user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
mysqli_stmt_bind_param($stmtPemBulan, "is", $user_id, $currentMonthKey);
mysqli_stmt_execute($stmtPemBulan);
$resPemBulan = mysqli_stmt_get_result($stmtPemBulan);
$rowPemBulan = mysqli_fetch_assoc($resPemBulan);
$pemasukan_bulan_ini = (float)($rowPemBulan['total'] ?? 0);
mysqli_stmt_close($stmtPemBulan);

// 5. Total Pengeluaran Bulan Ini
$stmtPengBulan = mysqli_prepare($conn, "SELECT SUM(amount) AS total FROM transactions WHERE type = 'Pengeluaran' AND user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
mysqli_stmt_bind_param($stmtPengBulan, "is", $user_id, $currentMonthKey);
mysqli_stmt_execute($stmtPengBulan);
$resPengBulan = mysqli_stmt_get_result($stmtPengBulan);
$rowPengBulan = mysqli_fetch_assoc($resPengBulan);
$pengeluaran_bulan_ini = (float)($rowPengBulan['total'] ?? 0);
mysqli_stmt_close($stmtPengBulan);

// 6. Keuntungan Bulan Ini
$keuntungan_bulan_ini = $pemasukan_bulan_ini - $pengeluaran_bulan_ini;

// 7. 5 Transaksi Terbaru Akun Ini
$stmtRecent = mysqli_prepare($conn, "SELECT * FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 5");
mysqli_stmt_bind_param($stmtRecent, "i", $user_id);
mysqli_stmt_execute($stmtRecent);
$resRecent = mysqli_stmt_get_result($stmtRecent);
$recent_transactions = [];
if ($resRecent) {
    while ($row = mysqli_fetch_assoc($resRecent)) {
        $recent_transactions[] = $row;
    }
}
mysqli_stmt_close($stmtRecent);

// 8. Data Grafik Per Bulan (6 Bulan Terakhir Akun Ini)
$mIndo = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$months = [];

for ($i = 5; $i >= 0; $i--) {
    $timestamp = strtotime("first day of -$i month");
    $mKey  = date('Y-m', $timestamp);
    $mIdx  = (int)date('n', $timestamp) - 1;
    $mName = $mIndo[$mIdx];
    
    $months[$mKey] = [
        'label' => $mName,
        'pemasukan' => 0,
        'pengeluaran' => 0
    ];
}

$sqlMonthly = "SELECT 
    DATE_FORMAT(date, '%Y-%m') AS month_key,
    SUM(CASE WHEN type = 'Pemasukan' THEN amount ELSE 0 END) AS pem,
    SUM(CASE WHEN type = 'Pengeluaran' THEN amount ELSE 0 END) AS peng
FROM transactions
WHERE user_id = ?
GROUP BY month_key";

$stmtMonthly = mysqli_prepare($conn, $sqlMonthly);
mysqli_stmt_bind_param($stmtMonthly, "i", $user_id);
mysqli_stmt_execute($stmtMonthly);
$resMonthly = mysqli_stmt_get_result($stmtMonthly);

if ($resMonthly) {
    while ($row = mysqli_fetch_assoc($resMonthly)) {
        $mKey = $row['month_key'];
        if (isset($months[$mKey])) {
            $months[$mKey]['pemasukan'] = (float)$row['pem'];
            $months[$mKey]['pengeluaran'] = (float)$row['peng'];
        } else if (!empty($mKey)) {
            $mIdx = (int)substr($mKey, 5, 2) - 1;
            $months[$mKey] = [
                'label' => $mIndo[$mIdx] ?? $mKey,
                'pemasukan' => (float)$row['pem'],
                'pengeluaran' => (float)$row['peng']
            ];
        }
    }
}
mysqli_stmt_close($stmtMonthly);

$chart_labels = [];
$chart_pemasukan = [];
$chart_pengeluaran = [];

foreach ($months as $m) {
    $chart_labels[]      = $m['label'];
    $chart_pemasukan[]   = $m['pemasukan'];
    $chart_pengeluaran[] = $m['pengeluaran'];
}

echo json_encode([
    'success' => true,
    'user_id' => $user_id,
    'total_pemasukan' => $total_pemasukan,
    'total_pengeluaran' => $total_pengeluaran,
    'keuntungan_bersih' => $keuntungan_bersih,
    'pemasukan_bulan_ini' => $pemasukan_bulan_ini,
    'pengeluaran_bulan_ini' => $pengeluaran_bulan_ini,
    'keuntungan_bulan_ini' => $keuntungan_bulan_ini,
    'recent_transactions' => $recent_transactions,
    'chart' => [
        'labels' => $chart_labels,
        'pemasukan' => $chart_pemasukan,
        'pengeluaran' => $chart_pengeluaran
    ]
]);
?>
