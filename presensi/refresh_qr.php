<?php
require_once 'config/db.php';

function buatKodeSesi() {
    return strtoupper(bin2hex(random_bytes(4)));
}

$tanggal = date('Y-m-d');
$waktu   = date('Y-m-d H:i:s');
$kode    = buatKodeSesi();

// Nonaktifkan sesi lama hari ini, lalu buat sesi baru
$conn->query("UPDATE sesi_presensi SET status='nonaktif' WHERE tanggal='$tanggal' AND status='aktif'");

$stmt = $conn->prepare("INSERT INTO sesi_presensi (kode_sesi, tanggal, waktu_dibuat, status) VALUES (?, ?, ?, 'aktif')");
$stmt->bind_param('sss', $kode, $tanggal, $waktu);
$stmt->execute();
$stmt->close();

header('Location: index.php');
exit;
