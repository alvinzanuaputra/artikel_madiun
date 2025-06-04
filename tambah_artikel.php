<?php
session_start();
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $content = trim($_POST['content']);
    
    // Handle file upload
    $picture = '';
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = time() . '_' . $filename;
            $upload_path = 'img/' . $new_filename;
            
            if (move_uploaded_file($_FILES['picture']['tmp_name'], $upload_path)) {
                $picture = $new_filename;
            } else {
                $error = "Gagal mengupload gambar.";
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    }
    
    if (empty($error) && !empty($title) && !empty($author) && !empty($category) && !empty($content)) {
        if (tambahArtikel($title, $author, $category, $content, $picture)) {
            $message = "Artikel berhasil ditambahkan!";
            // Reset form
            $title = $author = $category = $content = '';
        } else {
            $error = "Gagal menambahkan artikel.";
        }
    } elseif (empty($error)) {
        $error = "Semua field harus diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel - Madiun Blog</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 200px;
            resize: vertical;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <header>
        <h1>Tambah Artikel Baru</h1>
        <p>Bagikan cerita menarik tentang Kota Madiun</p>
    </header>

    <div class="form-container">
        

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Judul Artikel *</label>
                <input type="text" id="title" name="title" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="author">Penulis *</label>
                <input type="text" id="author" name="author" value="<?= isset($author) ? htmlspecialchars($author) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Kategori *</label>
                <select id="category" name="category" required>
                    <option value="">Pilih Kategori</option>
                    <option value="Kuliner" <?= (isset($category) && $category == 'Kuliner') ? 'selected' : '' ?>>Kuliner</option>
                    <option value="Wisata" <?= (isset($category) && $category == 'Wisata') ? 'selected' : '' ?>>Wisata</option>
                    <option value="Budaya" <?= (isset($category) && $category == 'Budaya') ? 'selected' : '' ?>>Budaya</option>
                    <option value="Sejarah" <?= (isset($category) && $category == 'Sejarah') ? 'selected' : '' ?>>Sejarah</option>
                    <option value="Tips" <?= (isset($category) && $category == 'Tips') ? 'selected' : '' ?>>Tips</option>
                </select>
            </div>

            <div class="form-group">
                <label for="picture">Gambar Artikel</label>
                <input type="file" id="picture" name="picture" accept="image/*">
                <small>Format yang didukung: JPG, JPEG, PNG, GIF (Maksimal 5MB)</small>
            </div>

            <div class="form-group">
                <label for="content">Isi Artikel *</label>
                <textarea id="content" name="content" placeholder="Tulis artikel Anda di sini..." required><?= isset($content) ? htmlspecialchars($content) : '' ?></textarea>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn">Publikasikan Artikel</button>
                <a href="dashboard.php" class="btn-secondary">Kembali ke Dashboard</a>
            </div>
        </form>
    </div>

    <footer>
        <p>© <?= date('Y') ?> Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni.</p>
    </footer>
</body>
</html>