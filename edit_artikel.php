<?php
session_start();
require 'koneksi.php';

// Pastikan hanya author yang login yang bisa mengakses halaman ini
if (!isset($_SESSION['author_id'])) {
    header("Location: login.php");
    exit();
}

function getArticleById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, 
                         GROUP_CONCAT(c.name SEPARATOR ', ') as category_names,
                         GROUP_CONCAT(c.id SEPARATOR ',') as category_ids
                         FROM article a
                         LEFT JOIN article_category ac ON a.id = ac.article_id
                         LEFT JOIN category c ON ac.category_id = c.id
                         WHERE a.id = ?
                         GROUP BY a.id");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateArticle($id, $title, $content, $picture, $date)
{
    global $pdo, $current_author_id;

    try {
        $pdo->beginTransaction();

        // Pastikan artikel milik penulis yang sedang login
        $checkStmt = $pdo->prepare("SELECT 1 FROM article_author WHERE article_id = ? AND author_id = ?");
        $checkStmt->execute([$id, $current_author_id]);

        if ($checkStmt->rowCount() == 0) {
            throw new Exception("Anda tidak memiliki izin untuk mengedit artikel ini.");
        }

        // Update artikel dengan semua field
        $stmt = $pdo->prepare("UPDATE article SET 
                                title = ?, 
                                content = ?, 
                                picture = ?, 
                                date = ? 
                                WHERE id = ?");
        $result = $stmt->execute([$title, $content, $picture, $date, $id]);

        if (!$result) {
            throw new Exception("Gagal memperbarui artikel.");
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return $e->getMessage();
    }
}

function updateArticleCategories($article_id, $category_ids)
{
    global $pdo;

    try {
        // Hapus kategori lama
        $deleteStmt = $pdo->prepare("DELETE FROM article_category WHERE article_id = ?");
        $deleteStmt->execute([$article_id]);

        // Tambahkan kategori baru
        $insertStmt = $pdo->prepare("INSERT INTO article_category (article_id, category_id) VALUES (?, ?)");

        // Pecah string category_ids menjadi array
        $categories = explode(',', $category_ids);

        foreach ($categories as $category_id) {
            if (!empty(trim($category_id))) {
                $insertStmt->execute([$article_id, $category_id]);
            }
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}

$current_author_id = $_SESSION['author_id'];
$message = '';
$error = '';
$article = null;

// Inisialisasi variabel untuk menghindari warning
$title = '';
$content = '';
$picture = '';
$category_ids = '';
$current_picture = '';

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

// Perbarui variabel setelah mendapatkan data artikel
if ($article) {
    $title = $article['title'];
    $content = $article['content'];
    $picture = $article['picture'];
    $current_picture = $article['picture'];
    $category_ids = $article['category_ids'] ?? '';
}

// Tambahkan debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Logging untuk debugging
function logDebug($message)
{
    $logFile = 'artikel_debug.log';
    $timestamp = date('[Y-m-d H:i:s]');
    $logMessage = "{$timestamp} {$message}\n";

    // Batasi ukuran log file (misalnya 100 KB)
    $maxLogSize = 100 * 1024; // 100 KB
    if (file_exists($logFile) && filesize($logFile) > $maxLogSize) {
        // Rotasi log: simpan log lama dan buat log baru
        rename($logFile, $logFile . '.old');
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Logging detail artikel sebelum update
logDebug("Artikel ID: $article_id");
logDebug("Judul Baru: $title");
logDebug("Gambar Baru: $picture");
logDebug("Kategori Baru: $category_ids");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $current_picture = $_POST['current_picture'];
    $category_ids = isset($_POST['categories']) ? implode(',', $_POST['categories']) : '';

    // Handle file upload
    $picture = $current_picture; // Keep current picture by default

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = time() . '_' . $filename;
            $upload_path = 'assets/img/' . $new_filename;

            if (move_uploaded_file($_FILES['picture']['tmp_name'], $upload_path)) {
                // Delete old picture if it exists
                if ($current_picture && file_exists('assets/img/' . $current_picture)) {
                    unlink('assets/img/' . $current_picture);
                }
                $picture = $new_filename;
            } else {
                $error = "Gagal mengupload gambar.";
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
        }
    }

    if (empty($error) && !empty($title) && !empty($content)) {
        // Gunakan tanggal artikel asli
        $date = $article['date'];

        $update_result = updateArticle($article_id, $title, $content, $picture, $date);
        if ($update_result === true) {
            // Update kategori
            if (updateArticleCategories($article_id, $category_ids)) {
                $message = "Artikel berhasil diperbarui!";

                // Logging yang lebih ringkas
                logDebug("Update Artikel: ID={$article_id}, Judul=" . substr($title, 0, 50) . "...");

                // Refresh article data
                $article = getArticleById($article_id);
            } else {
                $error = "Gagal memperbarui kategori artikel.";
                logDebug("Gagal Update Kategori: ID={$article_id}");
            }
        } else {
            $error = $update_result; // Pesan kesalahan spesifik
            logDebug("Gagal Update Artikel: ID={$article_id}, Pesan=" . substr($error, 0, 100));
        }
    } elseif (empty($error)) {
        $error = "Judul dan isi artikel harus diisi.";
    }
}

// Ambil daftar kategori untuk dropdown
$categories_query = $pdo->query("SELECT * FROM category");
$all_categories = $categories_query->fetchAll(PDO::FETCH_ASSOC);

// Ambil kategori artikel saat ini
$current_categories = $article ? explode(',', $article['category_ids'] ?? '') : [];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel - Madiun Blog</title>
    <link rel="icon" href="./assets/img/head_logo.jpg" type="image/jpg">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .form-group select[multiple] {
            height: auto;
            min-height: 100px;
        }

        .form-group select[multiple] option {
            padding: 8px;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border-radius: 3px;
        }

        .form-group select[multiple] option:checked {
            background-color: #e74c3c;
            color: white;
        }

        /* Tambahan untuk Toast Notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .toast {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .toast.show {
            opacity: 1;
        }

        .toast.error {
            background-color: #dc3545;
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
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $article_id ?>">
                <input type="hidden" name="current_picture" value="<?= htmlspecialchars($current_picture) ?>">

                <div class="form-group">
                    <label for="title">Judul Artikel *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
                </div>

                <div class="form-group">
                    <label for="content">Isi Artikel *</label>
                    <textarea id="content" name="content" required><?= htmlspecialchars($content) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="picture">Gambar Artikel</label>
                    <?php if ($current_picture): ?>
                        <div>
                            <p>Gambar saat ini:</p>
                            <img src="assets/img/<?= htmlspecialchars($current_picture) ?>" class="current-image" alt="Current image">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="picture" name="picture" accept="image/*">
                    <small>Biarkan kosong jika tidak ingin mengubah gambar. Format yang didukung: JPG, JPEG, PNG, GIF</small>
                </div>

                <div class="form-group">
                    <label for="categories">Kategori *</label>
                    <select id="categories" name="categories[]" multiple required>
                        <?php
                        $current_category_ids = $category_ids ? explode(',', $category_ids) : [];
                        foreach ($all_categories as $category):
                        ?>
                            <option value="<?= $category['id'] ?>" <?= in_array($category['id'], $current_category_ids) ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="btn-container">
                    <button type="submit" name="update" class="btn">Perbarui Artikel</button>
                    <a href="dashboard.php" class="btn-secondary">Kembali ke Dashboard</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Fungsi untuk menampilkan toast notification
        function showToast(message, type = 'success') {
            // Buat elemen toast
            const toast = document.createElement('div');
            toast.classList.add('toast', type);
            toast.textContent = message;

            // Tambahkan ke container
            const container = document.getElementById('toastContainer');
            container.appendChild(toast);

            // Tampilkan toast
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Sembunyikan toast setelah 3 detik
            setTimeout(() => {
                toast.classList.remove('show');

                // Hapus dari DOM setelah animasi
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Cek apakah ada pesan sukses dari PHP
        <?php if (!empty($message)): ?>
            // Tunggu DOM siap
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= htmlspecialchars($message) ?>');
            });
        <?php endif; ?>

        // Cek apakah ada pesan error dari PHP
        <?php if (!empty($error)): ?>
            // Tunggu DOM siap
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= htmlspecialchars($error) ?>', 'error');
            });
        <?php endif; ?>
    </script>

    <footer style="margin-top: 50px;">
        <p>© <?= date('Y') ?> Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni.</p>
        <p>Ditulis oleh Sasabila Alya – Universitas Negeri Malang | Artikel Madiun Blog</p>
    </footer>

</body>

</html>