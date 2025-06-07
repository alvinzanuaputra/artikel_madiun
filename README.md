# Madiun Blog: Jelajahi Pesona Kota Pendekar

Sebuah platform blog dinamis yang didedikasikan untuk mengeksplorasi keindahan, budaya, dan kelezatan kuliner Kota Madiun, Jawa Timur.

## Deskripsi Proyek

Madiun Blog adalah aplikasi web berbasis PHP yang memungkinkan pengguna untuk menjelajahi dan berbagi artikel tentang Kota Madiun - yang dikenal sebagai "Kota Pendekar". Platform ini berfokus pada:
- Pengalaman kuliner lokal
- Atraksi wisata
- Warisan budaya
- Cerita menarik dari kota kecil yang memiliki spirit besar

## Fitur Utama

### Manajemen Artikel
- Tambah, edit, dan hapus artikel dengan mudah
- Dukungan unggah gambar
- Kategorisasi artikel
- Pelacakan penulis artikel

### Desain & Pengalaman Pengguna
- Desain responsif (mobile-friendly)
- Pratinjau artikel dengan fitur "Baca Selengkapnya"
- Tampilan artikel fullscreen dengan modal
- Antarmuka yang bersih dan modern

### Keamanan & Manajemen
- Sistem login penulis
- Validasi kepemilikan artikel
- Logging aktivitas
- Penanganan error komprehensif

## Tangkapan Layar

*[Tambahkan tangkapan layar aplikasi di sini]*

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB 5.7+
- Web server (Apache/Nginx)
- Dukungan PDO

## Instalasi

1. Kloning repositori
   ```bash
   git clone https://github.com/SalsabilaAlya26/artikel_madiun.git
   cd madiun-blog
   ```

2. Impor struktur database
   ```bash
   mysql -u username -p nama_database < assets/database/kota_madiun.sql
   ```

3. Konfigurasi koneksi database
   - Buka `koneksi.php`
   - Sesuaikan pengaturan database Anda

4. Pastikan izin tulis untuk direktori
   ```bash
   chmod -R 755 assets/img/
   chmod -R 755 logs/
   ```

5. Jalankan melalui server lokal
   ```
   http://localhost/artikel_madiun
   ```

## Struktur Proyek

```
madiun-blog/
├── index.php          # Halaman utama blog
├── dashboard.php      # Dasbor penulis
├── tambah_artikel.php # Halaman tambah artikel
├── edit_artikel.php   # Halaman edit artikel
├── hapus_artikel.php  # Fungsionalitas hapus artikel
├── koneksi.php        # Konfigurasi koneksi database
├── style.css          # Stylesheet utama
├── assets/
│   ├── img/           # Direktori gambar artikel
│   └── database/      # File database
└── logs/              # Direktori log aktivitas
```

## Penggunaan

### Membaca Artikel
- Jelajahi artikel di halaman utama
- Klik "Baca Selengkapnya" untuk membaca artikel lengkap
- Tutup modal dengan tombol X, tekan ESC, atau klik di luar area artikel

### Manajemen Artikel (CRUD - CREATE READ UPDATE DELETE)
- Gunakan "+ Tambah Artikel" untuk membuat artikel baru
- Edit artikel dengan tombol "Edit"
- Hapus artikel dengan tombol "Hapus" (dengan konfirmasi)
- Read Informasi di setiap page.

## Teknologi yang Digunakan

- PHP Native
- MySQL
- PDO
- HTML5
- CSS3
- JavaScript
- Font Awesome (ikon)

## Lisensi

*[Tambahkan informasi lisensi]*

## Pengakuan

- Terinspirasi oleh keindahan dan budaya Kota Madiun
- Terima kasih kepada semua kontributor dan pendukung proyek

## Pembuat

- **Nama:** Salsabila Alya Putri Waluyo
- **NIM:** 230605110015
- **Kelas:** Pemrograman Web (C)
- **Kontak:** 
  - GitHub: SalsabilaAlya26
  - Email: salsabillaalyaputri26@gmail.com

## Catatan Pengembangan

- Versi: 1.0.0
- Status: Dalam pengembangan
- Terakhir diperbarui: Juni 2024

*Dibuat dengan ❤️ untuk Kota Madiun*