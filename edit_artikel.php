<?php
session_start();
require 'functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';
$article = null;

// Get article ID
if (isset($_POST['id'])) {
    $article_id = $_POST['id'];
} elseif (isset($_GET['id'])) {
    $article_id = $_GET['id'];
} else {
    header("Location: dashboard.php");
    exit();
}

// Get article data
$article = getArticleById($article_id);
if (!$article) {
    $error = "Artikel tidak ditemukan.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $content = trim($_POST['content']);
    $current_picture = $_POST['current_picture'];
    
    // Handle file upload
    $picture = $current_picture; // Keep current picture by default
    
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = time() . '_' . $filename;
            $upload_path = 'img/' . $new_filename;
            
            if (move_uploaded_file($_FILES['picture']['tmp_name'], $upload_path)) {
                // Delete old picture if it exists
                if ($current_picture && file_exists('img/' . $current_picture)) {
                    unlink('img/' . $current_picture);
                }
                $picture = $new_filename;
            } else {
                $error = "Gagal mengupload gambar.";
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    }
    
    if (empty($error) && !empty($title) && !empty($author) && !empty($category) && !empty($content)) {
        if (editArtikel($article_id, $title, $author, $category, $content, $picture)) {
            $message = "Artikel berhasil diperbarui!";
            // Refresh article data
            $article = getArticleById($article_id);
        } else {
            $error = "Gagal memperbarui artikel.";
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
    <title>Edit Artikel - Madiun Blog</title>
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
        
        .current-image {
            max-width: 200px;
            max-height: 150px;
            margin: 10px 0;
            border-radius: 5px;
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
        <h1>Edit Artikel</h1>
        <p>Perbarui artikel tentang Kota Madiun</p>
    </header>

    <div class="form-container">
        <?php if ($error && !$article): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
        <?php elseif ($article): ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_picture" value="<?= htmlspecialchars($article['picture']) ?>">
                
                <div class="form-group">
                    <label for="title">Judul Artikel *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($article['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="author">Penulis *</label>
                    <input type="text" id="author" name="author" value="<?= htmlspecialchars($article['author']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">Kategori *</label>
                    <select id="category" name="category" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Kuliner" <?= $article['category'] == 'Kuliner' ? 'selected' : '' ?>>Kuliner</option>
                        <option value="Wisata" <?= $article['category'] == 'Wisata' ? 'selected' : '' ?>>Wisata</option>
                        <option value="Budaya" <?= $article['category'] == 'Budaya' ? 'selected' : '' ?>>Budaya</option>
                        <option value="Sejarah" <?= $article['category'] == 'Sejarah' ? 'selected' : '' ?>>Sejarah</option>
                        <option value="Tips" <?= $article['category'] == 'Tips' ? 'selected' : '' ?>>Tips</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="picture">Gambar Artikel</label>
                    <?php if ($article['picture']): ?>
                        <div>
                            <p>Gambar saat ini:</p>
                            <img src="img/<?= htmlspecialchars($article['picture']) ?>" class="current-image" alt="Current image">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="picture" name="picture" accept="image/*">
                    <small>Biarkan kosong jika tidak ingin mengubah gambar. Format yang didukung: JPG, JPEG, PNG, GIF</small>
                </div>

                <div class="form-group">
                    <label for="content">Isi Artikel *</label>
                    <textarea id="content" name="content" required><?= htmlspecialchars($article['content']) ?></textarea>
                </div>

                <div class="btn-container">
                    <button type="submit" name="update" class="btn">Perbarui Artikel</button>
                    <a href="dashboard.php" class="btn-secondary">Kembali ke Dashboard</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <footer>
        <p>© <?= date('Y') ?> Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni.</p>
    </footer>
</body>
</html>