<?php
session_start();
require 'functions.php';

/* jika query() mengembalikan null, ubah ke array kosong supaya aman */
if (!is_array($articles)) {
    $articles = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Madiun Blog</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .welcome-info h2 {
            margin: 0;
            color: #333;
        }
        
        .welcome-info p {
            margin: 5px 0 0 0;
            color: #666;
        }
        
        .dashboard-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-logout {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-logout:hover {
            background-color: #c82333;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
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
        
        .articles-table {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .table-header h3 {
            margin: 0;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: bold;
            color: #333;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .article-title {
            font-weight: bold;
            max-width: 300px;
            word-wrap: break-word;
        }
        
        .article-meta {
            font-size: 0.9em;
            color: #666;
        }
        
        .article-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.8em;
            border: none;
            cursor: pointer;
        }
        
        .btn-edit {
            background-color: #28a745;
            color: white;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-view {
            background-color: #007bff;
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.8;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                text-align: center;
            }
            
            table, thead, tbody, th, td, tr {
                display: block;
            }
            
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            
            tr {
                border: 1px solid #ccc;
                margin-bottom: 10px;
                padding: 10px;
            }
            
            td {
                border: none;
                position: relative;
                padding-left: 50%;
            }
            
            td:before {
                content: attr(data-label) ": ";
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="welcome-info">
            <h2>Dashboard</h2>
            <p>Selamat datang</p>
        </div>
        <div class="dashboard-actions">
            <a href="tambah_artikel.php" class="btn">+ Tambah Artikel</a>
            <a href="index.php" class="btn" style="background-color: #28a745;">Lihat Blog</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>


    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-number"><?= is_countable($articles) ? count($articles) : 0 ?></div>

            <div class="stat-label">Total Artikel</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($articles, function($a) { return $a['category'] == 'Kuliner'; })) ?></div>
            <div class="stat-label">Artikel Kuliner</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($articles, function($a) { return $a['category'] == 'Wisata'; })) ?></div>
            <div class="stat-label">Artikel Wisata</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($articles, function($a) { return $a['category'] == 'Budaya'; })) ?></div>
            <div class="stat-label">Artikel Budaya</div>
        </div>
    </div>

    <div class="articles-table">
        <div class="table-header">
            <h3>Kelola Artikel</h3>
        </div>
        
        <?php if (!empty($articles)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td data-label="Judul">
                                <div class="article-title"><?= htmlspecialchars($article['title']) ?></div>
                                <div class="article-meta"><?= strlen($article['content']) ?> karakter</div>
                            </td>
                            <td data-label="Penulis"><?= htmlspecialchars($article['author']) ?></td>
                            <td data-label="Kategori">
                                <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.8em;">
                                    <?= htmlspecialchars($article['category']) ?>
                                </span>
                            </td>
                            <td data-label="Tanggal"><?= date("d/m/Y", strtotime($article['date'])) ?></td>
                            <td data-label="Aksi">
                                <div class="article-actions">
                                    <button onclick="showArticle(<?= $article['id'] ?>)" class="action-btn btn-view">Lihat</button>
                                    <form action="edit_artikel.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                        <button type="submit" class="action-btn btn-edit">Edit</button>
                                    </form>
                                    <form action="hapus_artikel.php" method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Yakin ingin menghapus artikel \'<?= htmlspecialchars(addslashes($article['title'])) ?>\'?')">
                                        <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                        <button type="submit" class="action-btn btn-delete">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Modal untuk preview artikel -->
                        <div id="fullscreen-<?= $article['id'] ?>" class="fullscreen-article" style="display: none;">
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
                                <div class="full-content"><?= nl2br(htmlspecialchars($article['content'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h3>Belum Ada Artikel</h3>
                <p>Mulai berbagi cerita tentang Kota Madiun dengan menambahkan artikel pertama Anda.</p>
                <a href="tambah_artikel.php" class="btn">+ Tambah Artikel Pertama</a>
            </div>
        <?php endif; ?>
    </div>

    <footer style="margin-top: 50px;">
        <p>© <?= date('Y') ?> Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni.</p>
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
                    const visibleModal = document.querySelector('.fullscreen-article[style*="display: block"]');
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