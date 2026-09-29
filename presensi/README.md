# Aplikasi Presensi Kelas (QR Code) — PHP + MySQL

Aplikasi sederhana untuk presensi kelas berbasis QR Code.
Dosen menampilkan QR Code, mahasiswa memindainya lewat kamera HP, lalu memasukkan NIM untuk mencatat kehadiran ke database MySQL.

## Struktur File

```
presensi-app/
├── config/
│   └── db.php                  -> pengaturan koneksi database
├── assets/
│   └── style.css                -> tampilan (tema gelap)
├── database.sql                 -> struktur tabel + data contoh mahasiswa
├── index.php                    -> dashboard dosen (tampilkan & refresh QR)
├── presensi.php                 -> halaman yang dibuka mahasiswa setelah scan QR
├── refresh_qr.php                -> membuat sesi/kode QR baru
├── generate_qr_mahasiswa.php     -> generate QR khusus per NIM (AJAX)
├── rekap.php                     -> rekap seluruh riwayat presensi
└── README.md
```

## Cara Instalasi (Localhost - XAMPP/Laragon)

1. Salin folder `presensi-app` ke dalam folder `htdocs` (XAMPP) atau `www` (Laragon).
2. Aktifkan Apache dan MySQL.
3. Buka **phpMyAdmin**, buat/import database dengan cara:
   - Klik menu **Import**, lalu pilih file `database.sql`, atau
   - Copy-paste isi `database.sql` ke tab **SQL** lalu jalankan.
4. Buka `config/db.php`, sesuaikan bila username/password MySQL Anda berbeda dari default (`root` tanpa password).
5. Akses dashboard dosen melalui browser:
   ```
   http://localhost/presensi-app/index.php
   ```

## Cara Instalasi (Hosting / cPanel)

1. Upload seluruh isi folder `presensi-app` ke `public_html` (atau subfolder) melalui File Manager/FTP.
2. Buat database MySQL baru beserta user & password lewat cPanel.
3. Import `database.sql` ke database tersebut lewat phpMyAdmin.
4. Edit `config/db.php`, isi `$host`, `$user`, `$pass`, `$dbname` sesuai data hosting Anda.
5. Akses `https://domainanda.com/presensi-app/index.php`.

## Cara Pakai

**Untuk Dosen:**
- Buka `index.php` di laptop/PC, lalu tampilkan ke layar/proyektor.
- Tombol **Refresh Data** membuat kode sesi/QR baru (berguna agar QR lama tidak bisa dipakai ulang di pertemuan berikutnya).
- Tombol **Download QR** menyimpan gambar QR sebagai file PNG.
- Tombol **Lihat Rekap** membuka riwayat semua presensi.
- Fitur **Generate QR Code Mahasiswa**: masukkan NIM tertentu untuk membuat QR pribadi mahasiswa tersebut (misalnya untuk dicetak di kartu ID).

**Untuk Mahasiswa:**
- Scan QR Code di layar menggunakan kamera HP.
- Akan terbuka halaman `presensi.php`, masukkan NIM, lalu tekan **Konfirmasi Hadir**.
- Sistem otomatis menolak jika NIM tidak terdaftar atau sudah presensi 2 kali di sesi yang sama.

## Menambah Data Mahasiswa

Tambahkan lewat phpMyAdmin pada tabel `mahasiswa`, atau jalankan query:

```sql
INSERT INTO mahasiswa (nim, nama, kelas) VALUES ('2210006', 'Nama Mahasiswa', 'TI-A');
```

## Catatan Penting

- Pembuatan gambar QR menggunakan layanan online **api.qrserver.com**, jadi **server perlu koneksi internet** saat menampilkan/generate QR. Jika ingin 100% offline, ganti dengan library PHP QR Code lokal (misalnya `phpqrcode`).
- Agar mahasiswa bisa scan dari HP, pastikan `index.php` diakses lewat alamat yang **bisa dijangkau oleh HP mahasiswa** (bukan `localhost`) — misalnya IP jaringan lokal (`http://192.168.x.x/presensi-app/`) atau domain hosting publik.
- Setiap hari sistem otomatis membuat sesi presensi baru (berdasarkan tanggal), jadi kode QR kemarin tidak berlaku lagi hari ini.
