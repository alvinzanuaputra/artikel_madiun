<?php
session_start();
require 'koneksi.php';

// Fungsi untuk memeriksa status login
function isLoggedIn() {
    return isset($_SESSION['author_id']);
}

// Fungsi untuk mendapatkan semua artikel (jika diperlukan untuk menampilkan di halaman utama)
// Mengambil data artikel dari database
function getAllArticles() {
    global $pdo;
    $stmt = $pdo->query("SELECT a.id, a.date, a.title, a.content, a.picture, 
                         GROUP_CONCAT(DISTINCT au.nickname) as author_nickname, 
                         GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names
                         FROM article a
                         JOIN article_author aa ON a.id = aa.article_id
                         JOIN author au ON aa.author_id = au.id
                         LEFT JOIN article_category ac ON a.id = ac.article_id
                         LEFT JOIN category c ON ac.category_id = c.id
                         GROUP BY a.id, a.date, a.title, a.content, a.picture
                         ORDER BY a.date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$articles = getAllArticles();

// Jika $articles tidak array (misal karena belum ada data), inisialisasi sebagai array kosong
if (!is_array($articles)) {
    $articles = [];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="html; charset=UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Blog tentang keindahan dan pesona Kota Madiun - kuliner, tempat wisata, dan budaya">
    <title> Madiun : Kota Pendekar, Surga Kuliner dan Pesona yang Tak Terlupakan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="/assets/img/head_logo.jpg" type="image/jpg">
    <!-- Tambahkan Font Awesome untuk icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Tambahkan gaya untuk toaster */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .toast {
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            animation: slideIn 0.5s ease, fadeOut 0.5s ease 3s forwards;
        }
        
        .toast.error {
            background-color: #dc3545;
        }
        
        .toast-icon {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    </style>
</head>

<body data-new-gr-c-s-check-loaded="14.1101.0" data-gr-ext-installed="" style="overflow: auto;">
    <?php 
    // Tampilkan toaster jika ada pesan
    if (isset($_SESSION['toast_message'])): ?>
        <div class="toast-container">
            <div class="toast <?= isset($_SESSION['toast_type']) ? $_SESSION['toast_type'] : '' ?>">
                <i class="toast-icon <?= 
                    isset($_SESSION['toast_type']) && $_SESSION['toast_type'] == 'error' 
                    ? 'fas fa-exclamation-circle' 
                    : 'fas fa-check-circle' 
                ?>"></i>
                <?= htmlspecialchars($_SESSION['toast_message']) ?>
            </div>
        </div>
        <?php 
        // Hapus pesan setelah ditampilkan
        unset($_SESSION['toast_message']);
        unset($_SESSION['toast_type']);
        endif; 
    ?>
    <header>
        <h1> Madiun : Kota Pendekar, Surga Kuliner dan Pesona yang Tak Terlupakan</h1>
        <p>Temukan pesona tersembunyi Kota Madiun—dari kehangatan kulinernya, keramahan warganya, hingga tempat-tempat
            yang bikin betah berlama-lama.</p>
        <div class="header-buttons">
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="logout.php" class="btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn"><i class="fas fa-sign-in-alt"></i> Masuk</a>
                <a href="register.php" class="btn"><i class="fas fa-user-plus"></i> Daftar</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="blog-container">
        <?php foreach ($articles as $article): ?>
        <div class="article-card">
            <h2 class="title"><?= htmlspecialchars($article['title']) ?></h2>
            <p class="meta">
                Dipublikasikan: <?= date("d F Y", strtotime($article['date'])) ?> |
                Penulis: <?= htmlspecialchars($article['author_nickname']) ?> |
                Kategori: <?= htmlspecialchars($article['category_names']) ?> </p>

            <img src="assets/img/<?= htmlspecialchars($article['picture']) ?>" class="article-image" alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy">

            <div class="content">
                <p>
                    <?= htmlspecialchars(substr($article['content'], 0, 200)) ?>...
                </p>

                <button onclick="showArticle(<?= $article['id'] ?>)" class="more-btn"><i class="fas fa-book-open"></i> Selengkapnya</button>

            </div>
        </div>

        <div id="fullscreen-<?= $article['id'] ?>" class="fullscreen-article" style="display: none;">
            <div class="fullscreen-content">
                <button class="close-btn" onclick="hideArticle(<?= $article['id'] ?>)" aria-label="Tutup artikel">×</button>
                <h2><?= htmlspecialchars($article['title']) ?></h2>
                <p class="meta">
                    Dipublikasikan: <?= date("d F Y", strtotime($article['date'])) ?> |
                    Penulis: <?= htmlspecialchars($article['author_nickname']) ?> |
                    Kategori: <?= htmlspecialchars($article['category_names']) ?> </p>
                <img src="assets/img/<?= htmlspecialchars($article['picture']) ?>" style="max-width: 100%; height: auto; margin-bottom: 15px;" alt="<?= htmlspecialchars($article['title']) ?>">
                <div class="full-content">
                    <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <footer>
        <p>© 2025 Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni—di kota yang menari dalam diam dan
            menggetarkan lewat cerita.</p>
    </footer>

    <script>
        function showArticle(id) {
            document.getElementById('fullscreen-' + id).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hideArticle(id) {
            document.getElementById('fullscreen-' + id).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside content
        document.addEventListener('DOMContentLoaded', function() {
            const fullscreenArticles = document.querySelectorAll('.fullscreen-article');
            fullscreenArticles.forEach(function(article) {
                article.addEventListener('click', function(e) {
                    if (e.target === article) {
                        const id = article.id.split('-')[1];
                        hideArticle(id);
                    }
                });
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const visibleModal = document.querySelector('.fullscreen-article[style="display: block;"]');
                    if (visibleModal) {
                        const id = visibleModal.id.split('-')[1];
                        hideArticle(id);
                    }
                }
            });
        });
    </script>

</body>

</html>