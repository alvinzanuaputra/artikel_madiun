# Blog Artikel Kota Madiun

## Deskripsi Proyek
Website blog artikel tentang Kota Madiun yang memungkinkan pengunjung untuk membaca artikel dan penulis untuk mengelola konten melalui sistem manajemen konten (CMS) yang terintegrasi.

## 🌐 Link Demo
- **URL Blog Online**: [http://salsabila-artikel-madiun.kesug.com](http://salsabila-artikel-madiun.kesug.com)
- **Repository GitHub**: [https://github.com/SalsabilaAlya26/project-uas-artikel-pemweb-kota-madiun](https://github.com/SalsabilaAlya26/project-uas-artikel-pemweb-kota-madiun)

## 🚀 Fitur Utama

### 1. Halaman Pengunjung
Pengunjung dapat mengakses website dalam mode publik untuk membaca artikel.

#### a. Homepage
- **Header**: Menampilkan tema/judul blog

    ![alt text](/assets/document/image.png)

- **Navigasi**: Menu navigasi utama untuk berpindah halaman

    ![alt text](/assets/document/image-1.png)

    ![alt text](/assets/document/image-2.png)

- **Konten Utama** (Bagian Kiri): Menampilkan 7 artikel terbaru yang sudah tersorting berdasarkan `article_id` secara otomatis

    ![alt text](/assets/document/image-3.png)

- **Sidebar** (Bagian Kanan): 
  - Fitur pencarian artikel
  - Menu kategori

    ![alt text](/assets/document/image-4.png)

  - Informasi "Tentang ITU"

    ![alt text](/assets/document/image-5.png)
    ![alt text](/assets/document/image-6.png)

- **Footer**: Informasi copyright

    ![alt text](/assets/document/image-7.png)

#### b. Halaman Detail Artikel
- **Navigasi**: Menu navigasi konsisten
- **Konten Utama** (Bagian Kiri): Detail lengkap artikel yang dipilih
- **Sidebar** (Bagian Kanan):
  - Fitur pencarian
  - Daftar judul artikel terkait
- **Footer**: Informasi copyright

    ![alt text](/assets/document/image-8.png)

#### c. Halaman Artikel per Kategori
- **Header**: Tema/judul blog
- **Navigasi**: Menu navigasi utama
- **Konten Utama** (Bagian Kiri): Menampilkan artikel berdasarkan kategori yang dipilih
- **Sidebar** (Bagian Kanan):
  - Fitur pencarian
  - Menu kategori
  - Informasi "Tentang"
- **Footer**: Informasi copyright

    ![alt text](/assets/document/image-9.png)

### 2. Sistem Pengelolaan Konten

#### a. Autentikasi
- **Login System**: Username dan password untuk penulis/author
- **Mode Pengunjung**: Akses tanpa login (mode logout)

    ![alt text](/assets/document/image-10.png)

- **Mode Admin**: Akses setelah login sebagai penulis/author

    ![alt text](/assets/document/image-11.png)

#### b. CRUD Operations

Jadi dalam sistem ini saat sudah login session akan disimpan dalam browser dan cookies akan menyimpan session selamanya sampai user/penulis logout. Saat melakukan operasi CRUD adalah user/penulis yang login sekarang bisa dilihat pada database berikut : 

[Database Kota Madiun Local](/assets/database/kota_madiun.sql)

Sistem mendukung operasi lengkap untuk:

**Penulis (Authors)**
- Create : Register penulis
- Read: Melihat penulis yaitu kita yang login saat ini

**Kategori**
- Create: Menambah kategori baru
- Read: Melihat daftar kategori
- Update: Mengedit kategori
- Delete: Menghapus kategori

**Artikel**
- **CREATE**: Halaman tambah artikel dengan form lengkap
- **READ**: Menampilkan artikel dari database MySQL
- **UPDATE**: Halaman edit artikel untuk modifikasi konten
- **DELETE**: Fitur hapus artikel dengan konfirmasi

**Capture :**
- CREATE

    ![alt text](/assets/document/image-13.png)

- READ

`Sudah sesuai fetching data dari database server php dengan koneksi MySQL`

-UPDATE

  ![alt text](/assets/document/image-14.png)

- DELETE

    ![alt text](/assets/document/image-15.png)

#### c. Logout
- Fitur logout untuk mengakhiri sesi penulis/author
- Kembali ke mode pengunjung setelah logout

    ![alt text](/assets/document/image-16.png)

### 3. Fitur Tambahan
- **Interactive Toaster**: Notifikasi inter12345678 untuk feedback user actions

    ![alt text](/assets/document/image-12.png)

- **Responsive Design**: Tampilan yang optimal di berbagai perangkat
- **Search Functionality**: Pencarian artikel berdasarkan kata kunci

    ![alt text](/assets/document/image-4.png)

## 👥 Data Penulis (Authors)

Website ini memiliki 7 penulis terdaftar:

| ID | Nama | Email | Password |
|---|---|---|---|
| 1 | SALSABILA ALYA PUTRI WALUYO | salsabila@gmail.com | 12345678 |
| 2 | ALVIN ZANUA PUTRA | alvinzanuaputra@gmail.com | 12345678 |
| 3 | CANTIKA MELATI | cantikamelati@gmail.com | 12345678 |
| 4 | ABDI RAMADHAN | abdiramadhan@gmail.com | 12345678 |
| 5 | FERDIANSYAH | ferdiansyah@gmail.com | 12345678 |
| 6 | TIMOTHY ANTONIO | timothy@gmail.com | 12345678 |
| 8 | SABIL | sabil@gmail.com | 12345678 |

## 🔐 Kredensial Login

### Username & Password
Semua penulis menggunakan password yang sama:
- **Password**: `12345678`
- **Username**: Gunakan email yang sesuai dari tabel penulis di atas

### Contoh Login:
- **Email**: `salsabila@gmail.com` atau 
- **Username**: `SALSABILA ALYA PUTRI WALUYO`
- **Password**: `12345678`

## 🛠️ Teknologi yang Digunakan

### Frontend
- HTML5
- CSS3
- JavaScript
- Bootstrap (untuk responsive design)

### Backend
- PHP
- MySQL Database
- Session Management








```

Penjelasan halaman index.php :
Toaster muncul ketika ada $_SESSION['toast_message'], lalu dihapus setelah ditampilkan.

Warna pink agak gelap #db7093, teks hitam.

Tombol keluar pakai PHP display: <?= isLoggedIn() ? 'block' : 'none' ?>

Semua tombol punya logika POST tersendiri di atas.

bagian tombol 1, Lihat Semua Artikel, 
munculkan echo toaster ketika user klik tombol ini, jika table author belum masuk sebagai penulis atau pengunjung arahkan ke page login.php


bagian tombol 2, Portal penulis
jika sudah login sebagai penulis = Portal Penulis, mengarah ke page dashboard.php
jika sudah login sebagai pengunjung = Portal Pengunjung, mengarah ke main.php
jika belum login sebagai apapun = Masuk

bagian tombol 3, Keluar
jika sudah login munculkan tombol keluar
jika belum login hilangkan tombol Keluar



poin tambahan : warna toaster pink agak gelap, tulisan hitam
echo dalam toaster 
jika user atau author sudah login sebagai penulis, munculkan toaster "Anda sudah masuk sebagai penulis"
jika user atau author sudah login sebagai pengunjung, munculkan toaster "Anda sudah masuk sebagai pengunjung"
jika user belum login munculkan pesan "Masuk untuk melihat artikel atau menulis artikel"
```