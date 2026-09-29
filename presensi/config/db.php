<?php
// =========================================================
// KONFIGURASI KONEKSI DATABASE
// Sesuaikan dengan pengaturan MySQL di server/laptop Anda
// =========================================================
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'presensi_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error .
        '<br>Pastikan MySQL sudah aktif dan database "presensi_db" sudah diimport dari file database.sql');
}

$conn->set_charset('utf8mb4');

date_default_timezone_set('Asia/Jakarta');
