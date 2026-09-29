<?php
require_once 'config/db.php';
header('Content-Type: application/json');

$nim = trim($_GET['nim'] ?? '');

if ($nim === '') {
    echo json_encode(['status' => 'error', 'pesan' => 'NIM wajib diisi']);
    exit;
}

$stmt = $conn->prepare("SELECT nama FROM mahasiswa WHERE nim = ?");
$stmt->bind_param('s', $nim);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'pesan' => 'NIM tidak ditemukan di data mahasiswa']);
    exit;
}
$mhs = $res->fetch_assoc();
$stmt->close();

$tanggal = date('Y-m-d');
$sesiRes = $conn->query("SELECT kode_sesi FROM sesi_presensi WHERE tanggal = '$tanggal' AND status = 'aktif' ORDER BY id DESC LIMIT 1");

if (!$sesiRes || $sesiRes->num_rows === 0) {
    echo json_encode(['status' => 'error', 'pesan' => 'Belum ada sesi presensi aktif hari ini']);
    exit;
}
$sesi = $sesiRes->fetch_assoc();

$protokol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$base_url  = $protokol . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$target    = $base_url . '/presensi.php?kode=' . urlencode($sesi['kode_sesi']) . '&nim=' . urlencode($nim);
$qr_url    = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($target);

echo json_encode(['status' => 'ok', 'nama' => $mhs['nama'], 'qr_url' => $qr_url]);
