<?php
session_start();
require 'koneksi.php';

// Pastikan hanya author yang login yang bisa mengakses halaman ini
if (!isset($_SESSION['author_id'])) {
    header("Location: login.php");
    exit();
}

$current_author_id = $_SESSION['author_id'];
$current_author_nickname = $_SESSION['nickname'];

function tambahArtikel($title, $author_id, $category_id, $content, $picture) {
    global $pdo;
    
    try {
        // Mulai transaksi
        $pdo->beginTransaction();
        
        // Insert artikel
        $stmt = $pdo->prepare("INSERT INTO article (title, content, picture, date) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$title, $content, $picture]);
        $article_id = $pdo->lastInsertId();
        
        // Insert penulis artikel
        $stmt_author = $pdo->prepare("INSERT INTO article_author (article_id, author_id) VALUES (?, ?)");
        $stmt_author->execute([$article_id, $author_id]);
        
        // Insert kategori artikel
        $stmt_category = $pdo->prepare("INSERT INTO article_category (article_id, category_id) VALUES (?, ?)");
        $stmt_category->execute([$article_id, $category_id]);
        
        // Commit transaksi
        $pdo->commit();
        
        return true;
    } catch (PDOException $e) {
        // Rollback transaksi jika terjadi kesalahan
        $pdo->rollBack();
        error_log("Gagal menambahkan artikel: " . $e->getMessage());
        return false;
    }
}

function uploadGambar($file) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Gagal mengupload gambar.'];
    }
    
    $filename = $file['name'];
    $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $filesize = $file['size'];
    
    // Validasi tipe file
    if (!in_array($filetype, $allowed)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.'];
    }
    
    // Validasi ukuran file
    if ($filesize > $max_size) {
        return ['success' => false, 'message' => 'Ukuran gambar terlalu besar. Maksimal 5MB.'];
    }
    
    // Generate nama file unik
    $new_filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $filetype;
    $upload_path = 'assets/img/' . $new_filename;
    
    // Pindahkan file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'message' => 'Gagal memindahkan file.'];
    }
}

function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, name FROM category");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? '';
    
    $errors = [];
    
    // Validasi input
    if (empty($title)) {
        $errors[] = "Judul artikel harus diisi.";
    }
    
    if (empty($content)) {
        $errors[] = "Isi artikel harus diisi.";
    }
    
    if (empty($category)) {
        $errors[] = "Pilih kategori artikel.";
    }
    
    // Validasi gambar
    $picture = '';
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadGambar($_FILES['picture']);
        
        if ($upload_result['success']) {
            $picture = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    if (empty($errors)) {
        if (tambahArtikel($title, $current_author_id, $category, $content, $picture)) {
            $_SESSION['message'] = "Artikel berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Gagal menambahkan artikel. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel - Madiun Blog</title>
    <link rel="icon" href="./assets/img/head_logo.jpg" type="image/jpg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
        
        .category-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .category-checkboxes label {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .category-checkboxes input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .category-checkboxes label:hover {
            background-color: #e9ecef;
        }
        
        .category-checkboxes input[type="checkbox"]:checked + span {
            font-weight: bold;
            color: #007bff;
        }
        
        .error-list {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error-list ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Tambah Artikel Baru</h1>
        <p>Bagikan cerita menarik tentang Kota Madiun</p>
    </header>

    <div class="form-container">
        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Judul Artikel *</label>
                <input type="text" id="title" name="title" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required maxlength="255">
            </div>

            <div class="form-group">
                <label for="category">Kategori *</label>
                <select id="category" name="category" required>
                    <option value="">Pilih kategori artikel</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= (isset($category) && $category == $category['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
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