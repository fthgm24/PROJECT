<?php
/**
 * Script Pengujian Koneksi dan Tabel Database PKK
 * (Sesuai struktur pada phpMyAdmin)
 */

require_once __DIR__ . '/koneksi.php';

echo "<br><hr><h3>📊 Cek Tabel Database '$db':</h3>";

$tables = ['categories', 'events', 'settings', 'transactions', 'users'];

echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; font-family: sans-serif;'>";
echo "<tr style='background-color: #f2f2f2;'><th>Nama Tabel</th><th>Jumlah Data (Rows)</th><th>Status</th></tr>";

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$table`");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<tr>";
        echo "<td><b>$table</b></td>";
        echo "<td align='center'>" . $row['total'] . "</td>";
        echo "<td style='color: green;'>✅ Ada & Terhubung</td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td><b>$table</b></td>";
        echo "<td align='center'>-</td>";
        echo "<td style='color: red;'>❌ Tabel belum dibuat / " . mysqli_error($conn) . "</td>";
        echo "</tr>";
    }
}

echo "</table>";
?>
