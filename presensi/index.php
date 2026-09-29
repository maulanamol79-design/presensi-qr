<?php
require_once 'config/db.php';

function buatKodeSesi() {
    return strtoupper(bin2hex(random_bytes(4)));
}

function ambilSesiAktif($conn) {
    $tanggal = date('Y-m-d');
    $res = $conn->query("SELECT * FROM sesi_presensi WHERE tanggal = '$tanggal' AND status = 'aktif' ORDER BY id DESC LIMIT 1");
    if ($res && $res->num_rows > 0) {
        return $res->fetch_assoc();
    }
    return null;
}

function buatSesiBaru($conn) {
    $tanggal = date('Y-m-d');
    $waktu   = date('Y-m-d H:i:s');
    $kode    = buatKodeSesi();

    $conn->query("UPDATE sesi_presensi SET status='nonaktif' WHERE tanggal='$tanggal' AND status='aktif'");

    $stmt = $conn->prepare("INSERT INTO sesi_presensi (kode_sesi, tanggal, waktu_dibuat, status) VALUES (?, ?, ?, 'aktif')");
    $stmt->bind_param('sss', $kode, $tanggal, $waktu);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    return ['id' => $id, 'kode_sesi' => $kode, 'tanggal' => $tanggal, 'waktu_dibuat' => $waktu, 'status' => 'aktif'];
}

// Ambil / buat sesi aktif hari ini
$sesi = ambilSesiAktif($conn);
if (!$sesi) {
    $sesi = buatSesiBaru($conn);
}

// Bangun URL tujuan QR (halaman presensi mahasiswa)
$protokol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$base_url  = $protokol . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$target_url = $base_url . '/presensi.php?kode=' . urlencode($sesi['kode_sesi']);
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($target_url);

// Daftar hadir untuk sesi aktif saat ini
$sesi_id = (int) $sesi['id'];
$hadir = $conn->query("SELECT nim, nama, waktu_scan FROM presensi WHERE sesi_id = $sesi_id ORDER BY waktu_scan ASC");
$jumlah_hadir = $hadir ? $hadir->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Presensi Kelas - QR Code</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card">
    <div class="icon-badge">🗓️</div>
    <h1>Presensi Kelas</h1>
    <div class="subtitle">Scan QR Code untuk Absensi</div>

    <div class="qr-box">
        <img id="qrImage" src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR Presensi">
        <div class="qr-status">✅ Scan untuk hadir</div>
    </div>

    <div class="kode-sesi">
        Kode Sesi: <b><?php echo htmlspecialchars($sesi['kode_sesi']); ?></b>
        &nbsp;•&nbsp; Tanggal: <?php echo htmlspecialchars($sesi['tanggal']); ?>
        &nbsp;•&nbsp; Hadir: <b><?php echo $jumlah_hadir; ?></b> mahasiswa
    </div>

    <div class="btn-row">
        <a href="refresh_qr.php" class="btn btn-green">🔄 Refresh Data</a>
        <button type="button" class="btn btn-dark" onclick="downloadQR()">⬇️ Download QR</button>
        <a href="rekap.php" class="btn btn-blue">📄 Lihat Rekap</a>
    </div>

    <div class="section-title">🔲 Generate QR Code Mahasiswa</div>
    <div class="input-row">
        <input type="text" id="inputNim" placeholder="Masukkan NIM">
        <button type="button" class="btn btn-green" style="flex:0 0 110px;" onclick="generateQrMhs()">Generate</button>
    </div>

    <div class="hasil-qr-mhs" id="hasilQrMhs"></div>

    <div class="info-box">
        🟢 Online - Data dari MySQL Database
    </div>

    <div class="info-box" style="background:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.15);">
        ℹ️ Tunjukkan QR Code di atas kepada mahasiswa, atau mahasiswa dapat memindai lewat kamera HP masing-masing.
    </div>
</div>

<script>
function downloadQR() {
    fetch("<?php echo htmlspecialchars($qr_url); ?>")
        .then(res => res.blob())
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'qr_presensi_<?php echo htmlspecialchars($sesi['kode_sesi']); ?>.png';
            document.body.appendChild(a);
            a.click();
            a.remove();
        })
        .catch(() => {
            window.open("<?php echo htmlspecialchars($qr_url); ?>", '_blank');
        });
}

function generateQrMhs() {
    const nim = document.getElementById('inputNim').value.trim();
    const hasil = document.getElementById('hasilQrMhs');
    if (!nim) {
        hasil.innerHTML = '<div class="pesan error">Masukkan NIM terlebih dahulu.</div>';
        return;
    }
    hasil.innerHTML = '<div class="pesan info">Memuat...</div>';
    fetch('generate_qr_mahasiswa.php?nim=' + encodeURIComponent(nim))
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                hasil.innerHTML =
                    '<div style="margin-bottom:8px;font-size:13px;color:#b7b2cf;">' + data.nama + ' (' + nim + ')</div>' +
                    '<img src="' + data.qr_url + '" alt="QR Mahasiswa">';
            } else {
                hasil.innerHTML = '<div class="pesan error">' + data.pesan + '</div>';
            }
        })
        .catch(() => {
            hasil.innerHTML = '<div class="pesan error">Terjadi kesalahan, coba lagi.</div>';
        });
}
</script>
</body>
</html>
