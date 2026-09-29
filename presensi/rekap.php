<?php
require_once 'config/db.php';

$data = $conn->query(
    "SELECT p.nim, p.nama, p.waktu_scan, s.kode_sesi, s.tanggal
     FROM presensi p
     JOIN sesi_presensi s ON p.sesi_id = s.id
     ORDER BY p.waktu_scan DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekap Presensi</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card" style="max-width:640px; text-align:left;">
    <h1 style="text-align:center;">📄 Rekap Presensi</h1>
    <div class="subtitle" style="text-align:center;">Riwayat seluruh sesi kehadiran</div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Waktu</th>
                <th>Kode Sesi</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($data && $data->num_rows > 0): ?>
            <?php while ($row = $data->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                    <td><?php echo htmlspecialchars($row['nim']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td><?php echo htmlspecialchars(date('H:i:s', strtotime($row['waktu_scan']))); ?></td>
                    <td><span class="badge-hijau"><?php echo htmlspecialchars($row['kode_sesi']); ?></span></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center; color:#8a84a8;">Belum ada data presensi.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div style="text-align:center; margin-top:20px;">
        <a href="index.php" class="btn btn-blue" style="display:inline-flex;">&larr; Kembali ke Dashboard</a>
    </div>
</div>
</body>
</html>
