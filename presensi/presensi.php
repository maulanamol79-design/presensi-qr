<?php
require_once 'config/db.php';

$kode        = isset($_GET['kode']) ? trim($_GET['kode']) : '';
$nim_prefill = isset($_GET['nim']) ? trim($_GET['nim']) : '';

$pesan      = '';
$tipe_pesan = '';
$sesi       = null;

// Validasi kode sesi (harus aktif & untuk hari ini)
if ($kode !== '') {
    $tanggal = date('Y-m-d');
    $stmt = $conn->prepare("SELECT * FROM sesi_presensi WHERE kode_sesi = ? AND tanggal = ? AND status = 'aktif' LIMIT 1");
    $stmt->bind_param('ss', $kode, $tanggal);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $sesi = $res->fetch_assoc();
    }
    $stmt->close();
}

if (!$sesi) {
    $pesan = 'QR Code tidak valid atau sudah kedaluwarsa. Silakan minta QR terbaru ke dosen.';
    $tipe_pesan = 'error';
}

// Proses submit NIM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sesi) {
    $nim = trim($_POST['nim'] ?? '');

    if ($nim === '') {
        $pesan = 'NIM tidak boleh kosong.';
        $tipe_pesan = 'error';
    } else {
        $stmt = $conn->prepare("SELECT nama FROM mahasiswa WHERE nim = ?");
        $stmt->bind_param('s', $nim);
        $stmt->execute();
        $r = $stmt->get_result();

        if ($r->num_rows === 0) {
            $pesan = 'NIM tidak ditemukan di data mahasiswa.';
            $tipe_pesan = 'error';
        } else {
            $mhs = $r->fetch_assoc();

            $cek = $conn->prepare("SELECT id FROM presensi WHERE sesi_id = ? AND nim = ?");
            $cek->bind_param('is', $sesi['id'], $nim);
            $cek->execute();
            $cekRes = $cek->get_result();

            if ($cekRes->num_rows > 0) {
                $pesan = 'Anda (' . $mhs['nama'] . ') sudah tercatat hadir untuk sesi ini.';
                $tipe_pesan = 'info';
            } else {
                $waktu = date('Y-m-d H:i:s');
                $ins = $conn->prepare("INSERT INTO presensi (sesi_id, nim, nama, waktu_scan) VALUES (?, ?, ?, ?)");
                $ins->bind_param('isss', $sesi['id'], $nim, $mhs['nama'], $waktu);
                $ins->execute();
                $ins->close();

                $pesan = 'Presensi berhasil! Terima kasih, ' . $mhs['nama'] . '.';
                $tipe_pesan = 'sukses';
            }
            $cek->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Presensi Mahasiswa</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card">
    <div class="icon-badge">✅</div>
    <h1>Presensi Kehadiran</h1>
    <div class="subtitle">
        <?php echo $sesi ? 'Sesi tanggal ' . htmlspecialchars($sesi['tanggal']) : 'Halaman Presensi'; ?>
    </div>

    <?php if ($pesan): ?>
        <div class="pesan <?php echo htmlspecialchars($tipe_pesan); ?>"><?php echo htmlspecialchars($pesan); ?></div>
    <?php endif; ?>

    <?php if ($sesi && $tipe_pesan !== 'sukses'): ?>
        <form method="POST">
            <div class="input-row">
                <input type="text" name="nim" placeholder="Masukkan NIM Anda"
                       value="<?php echo htmlspecialchars($nim_prefill); ?>" required>
            </div>
            <button type="submit" class="btn btn-green" style="width:100%;">Konfirmasi Hadir</button>
        </form>
    <?php elseif (!$sesi): ?>
        <a href="index.php" class="link-muted">&larr; Kembali ke halaman dosen</a>
    <?php endif; ?>
</div>
</body>
</html>
