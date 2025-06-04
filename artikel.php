<?php
require 'functions.php';
$articles = getAllArticles();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Blog tentang keindahan dan pesona Kota Madiun - kuliner, tempat wisata, dan budaya">
    <title> Madiun : Kota Pendekar, Surga Kuliner dan Pesona yang Tak Terlupakan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body>
    <header>
        <h1> Madiun : Kota Pendekar, Surga Kuliner dan Pesona yang Tak Terlupakan</h1>
        <p>Temukan pesona tersembunyi Kota Madiun—dari kehangatan kulinernya, keramahan warganya, hingga tempat-tempat
            yang bikin betah berlama-lama.</p>
        <a href="tambah_artikel.php" class="btn">+ Tambah Artikel</a>
    </header>

    <div class="blog-container">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $article): ?>
                <div class="article-card">
                    <h2 class="title"><?= htmlspecialchars($article['title']) ?></h2>
                    <p class="meta">
                        Dipublikasikan: <?= date("d F Y", strtotime($article['date'])) ?> |
                        Penulis: <?= htmlspecialchars($article['author']) ?> |
                        Kategori: <?= htmlspecialchars($article['category']) ?>
                    </p>

                    <?php if (!empty($article['picture'])): ?>
                        <img src="img/<?= htmlspecialchars($article['picture']) ?>" class="article-image"
                            alt="<?= htmlspecialchars($article['title']) ?>" loading="lazy">
                    <?php endif; ?>

                    <div class="content">
                        <p>
                            <?= nl2br(htmlspecialchars(substr(strip_tags($article['content']), 0, 200))) ?>...
                        </p>

                        <button onclick="showArticle(<?= $article['id'] ?>)" class="more-btn">Selengkapnya</button>

                        <div class="article-actions">
                            <form action="edit_artikel.php" method="POST" class="action-form">
                                <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                <button type="submit" class="action-btn">Edit</button>
                            </form>

                            <form action="hapus_artikel.php" method="POST" class="action-form"
                                onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                <button type="submit" class="action-btn">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="fullscreen-<?= $article['id'] ?>" class="fullscreen-article">
                    <div class="fullscreen-content">
                        <button class="close-btn" onclick="hideArticle(<?= $article['id'] ?>)" aria-label="Tutup artikel">×</button>
                        <h2><?= htmlspecialchars($article['title']) ?></h2>
                        <p class="meta">
                            Dipublikasikan: <?= date("d F Y", strtotime($article['date'])) ?> |
                            Penulis: <?= htmlspecialchars($article['author']) ?> |
                            Kategori: <?= htmlspecialchars($article['category']) ?>
                        </p>
                        <?php if (!empty($article['picture'])): ?>
                            <img src="img/<?= htmlspecialchars($article['picture']) ?>"
                                style="max-width: 100%; height: auto; margin-bottom: 15px;"
                                alt="<?= htmlspecialchars($article['title']) ?>">
                        <?php endif; ?>
                        <div class="full-content"><?= nl2br($article['content']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>Maaf</h2>
                <p>Saat ini belum ada artikel yang tersedia.</p>
                <p><a href="index.php" class="btn">Kembali ke Halaman Utama</a></p>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>© <?= date('Y') ?> Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni—di kota yang menari dalam diam dan
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