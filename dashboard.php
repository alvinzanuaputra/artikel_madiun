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

function getAllArticles($search = '', $category = '')
{
    global $pdo, $current_author_id;

    // Debugging
    if (!isset($current_author_id)) {
        die("Error: Author ID not set");
    }

    $whereConditions = ["aa.author_id = ?"];
    $params = [$current_author_id];

    // Tambahkan kondisi pencarian
    if (!empty($search)) {
        $whereConditions[] = "(a.title LIKE ? OR a.content LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Tambahkan kondisi kategori
    if (!empty($category) && $category !== 'semua') {
        $whereConditions[] = "c.name = ?";
        $params[] = $category;
    }

    $sql = "SELECT a.id, a.date, a.title, a.content, a.picture, 
            (SELECT GROUP_CONCAT(DISTINCT au.nickname) 
             FROM article_author aa2 
             JOIN author au ON aa2.author_id = au.id 
             WHERE aa2.article_id = a.id) as author_nickname, 
            GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names
            FROM article a
            JOIN article_author aa ON a.id = aa.article_id
            LEFT JOIN article_category ac ON a.id = ac.article_id
            LEFT JOIN category c ON ac.category_id = c.id
            WHERE " . implode(" AND ", $whereConditions) . "
            GROUP BY a.id, a.date, a.title, a.content, a.picture
            ORDER BY a.date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticleById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, au.nickname as author_nickname, 
                         GROUP_CONCAT(c.name SEPARATOR ', ') as category_names
                         FROM article a
                         JOIN article_author aa ON a.id = aa.article_id
                         JOIN author au ON aa.author_id = au.id
                         LEFT JOIN article_category ac ON a.id = ac.article_id
                         LEFT JOIN category c ON ac.category_id = c.id
                         WHERE a.id = ?
                         GROUP BY a.id");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function isLoggedIn()
{
    return isset($_SESSION['author_id']);
}

function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date)
{
    return date("d F Y", strtotime($date));
}

function truncateText($text, $length = 200)
{
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Fungsi untuk mendapatkan semua kategori
function getAllCategories()
{
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT name FROM category ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mendapatkan parameter pencarian dan kategori
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

// Mendapatkan artikel berdasarkan filter
$articles = getAllArticles($search, $selectedCategory);
$categories = getAllCategories();

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
    <link rel="icon" href="/assets/img/head_logo.jpg" type="image/jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

            table,
            thead,
            tbody,
            th,
            td,
            tr {
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

        .article-image-cell {
            text-align: center;
            vertical-align: middle !important;
        }

        .article-image-cell .no-image {
            color: #666;
            font-style: italic;
        }

        .article-image-cell img {
            transition: transform 0.3s ease;
        }

        .article-image-cell img:hover {
            transform: scale(1.1);
            cursor: pointer;
        }

        .search-filter-section {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .search-filter-container {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            border-color: #e74c3c;
        }

        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
        }

        .search-btn:hover {
            background: #c0392b;
        }

        .category-filter {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .category-select {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            outline: none;
            min-width: 120px;
        }

        .category-select:focus {
            border-color: #e74c3c;
        }

        .clear-filters {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease;
        }

        .clear-filters:hover {
            background: #5a6268;
        }

        .results-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #e74c3c;
        }

        .results-info h3 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .results-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="dashboard-header" style="background-color: #f5c6cb;">
        <div class="welcome-info">
            <h2>Dashboard</h2>
            <p>Selamat datang, <?= htmlspecialchars($current_author_nickname) ?>!</p>
        </div>
        <div class="dashboard-actions">
            <a href="tambah_artikel.php" class="btn">+ Tambah Artikel</a>
            <a href="index.php" class="btn" style="background-color: #28a745; color: white;"><i class="fas fa-eye"></i> Lihat Blog</a>
            <a href="logout.php" class="btn" style="background-color: #dc3545; color: white;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="search-filter-section">
        <form method="GET" action="" class="search-filter-container">
            <div class="search-box">
                <input type="text"
                    name="search"
                    class="search-input"
                    placeholder="Cari artikel, judul, konten..."
                    value="<?= htmlspecialchars($search) ?>"
                    autocomplete="off">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="category-filter">
                <select name="category" class="category-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['name']) ?>"
                            <?= $selectedCategory === $category['name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if (!empty($search) || !empty($selectedCategory)): ?>
                    <a href="dashboard.php" class="clear-filters">
                        <i class="fas fa-times"></i> Hapus Filter
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <?php if (!empty($search) || !empty($selectedCategory)): ?>
        <div class="results-info">
            <h3>
                <?php if (!empty($search) && !empty($selectedCategory)): ?>
                    Hasil pencarian "<?= htmlspecialchars($search) ?>" dalam kategori "<?= htmlspecialchars($selectedCategory) ?>"
                <?php elseif (!empty($search)): ?>
                    Hasil pencarian "<?= htmlspecialchars($search) ?>"
                <?php elseif (!empty($selectedCategory)): ?>
                    Artikel kategori "<?= htmlspecialchars($selectedCategory) ?>"
                <?php endif; ?>
            </h3>
            <p>Ditemukan <?= count($articles) ?> artikel</p>
        </div>
    <?php endif; ?>

    <div id="toastContainer" style="
        position: fixed; 
        top: 20px; 
        right: 20px; 
        z-index: 9999;
    "></div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-number"><?= is_countable($articles) ? count($articles) : 0 ?></div>
            <div class="stat-label">Total Artikel</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($articles, function ($a) {
                                            return strpos($a['category_names'], 'KULINER') !== false;
                                        })) ?></div>
            <div class="stat-label">Artikel Kuliner</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($articles, function ($a) {
                                            return strpos($a['category_names'], 'BUDAYA') !== false;
                                        })) ?></div>
            <div class="stat-label">Artikel Budaya</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($articles, function ($a) {
                                            return strpos($a['category_names'], 'MODERNISASI') !== false;
                                        })) ?></div>
            <div class="stat-label">Artikel Modernisasi</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= count(array_filter($articles, function ($a) {
                                            return strpos($a['category_names'], 'TOKOH INSPIRATIF') !== false;
                                        })) ?></div>
            <div class="stat-label">Artikel Tokoh Inspiratif</div>
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
                        <th>Gambar</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Penulis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td data-label="Gambar" class="article-image-cell">
                                <?php if (!empty($article['picture'])): ?>
                                    <img src="./assets/img/<?= htmlspecialchars($article['picture']) ?>"
                                        alt="Gambar <?= htmlspecialchars($article['title']) ?>"
                                        style="max-width: 300px; max-height: 200px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <span class="no-image">Tidak ada gambar</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Judul">
                                <div class="article-title"><?= htmlspecialchars($article['title']) ?></div>
                                <div class="article-meta"><?= strlen($article['content']) ?> karakter</div>
                            </td>
                            <td data-label="Kategori">
                                <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.8em;">
                                    <?= htmlspecialchars($article['category_names']) ?>
                                </span>
                            </td>
                            <td data-label="Tanggal"><?= date("d/m/Y", strtotime($article['date'])) ?></td>
                            <td data-label="Penulis">
                                <?= !empty($article['author_nickname'])
                                    ? htmlspecialchars($article['author_nickname'])
                                    : 'Penulis Tidak Diketahui'
                                ?>
                            </td>
                            <td data-label="Aksi">
                                <div class="article-actions">
                                    <button onclick="showArticle(<?= $article['id'] ?>)" class="action-btn btn-view">Lihat</button>
                                    <form action="edit_artikel.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                        <button type="submit" class="action-btn btn-edit">Edit</button>
                                    </form>
                                    <form action="hapus_artikel.php" method="POST" style="display: inline;"
                                        onsubmit="return confirmDelete('<?= htmlspecialchars(addslashes($article['title'])) ?>')">
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
                                    Penulis: <?= !empty($article['author_nickname'])
                                                    ? htmlspecialchars($article['author_nickname'])
                                                    : 'Penulis Tidak Diketahui'
                                                ?> |
                                    Kategori: <?= htmlspecialchars($article['category_names']) ?>
                                </p>
                                <?php if (!empty($article['picture'])): ?>
                                    <img src="/assets/img/<?= htmlspecialchars($article['picture']) ?>"
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

        function showToast(message, type = 'success') {
            // Buat elemen toast
            const toast = document.createElement('div');
            toast.classList.add('toast', type);
            toast.style.cssText = `
                background-color: ${type === 'success' ? '#28a745' : '#dc3545'};
                color: white;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                opacity: 0;
                transition: opacity 0.3s ease-in-out;
            `;
            toast.textContent = message;

            // Tambahkan ke container
            const container = document.getElementById('toastContainer');
            container.appendChild(toast);

            // Tampilkan toast
            setTimeout(() => {
                toast.style.opacity = '1';
            }, 10);

            // Sembunyikan toast setelah 3 detik
            setTimeout(() => {
                toast.style.opacity = '0';

                // Hapus dari DOM setelah animasi
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Cek apakah ada pesan dari session
        <?php
        if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
            $message = htmlspecialchars($_SESSION['message']);
            $type = htmlspecialchars($_SESSION['message_type']);

            // Hapus pesan dari session setelah dibaca
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        ?>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= $message ?>', '<?= $type ?>');
            });
        <?php } ?>

        // Fungsi konfirmasi hapus artikel
        function confirmDelete(articleTitle) {
            // Hapus modal yang sudah ada sebelumnya
            const existingModal = document.getElementById('deleteConfirmModal');
            if (existingModal) {
                document.body.removeChild(existingModal);
            }

            // Buat modal konfirmasi kustom
            const modal = document.createElement('div');
            modal.id = 'deleteConfirmModal';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
            modal.style.display = 'flex';
            modal.style.justifyContent = 'center';
            modal.style.alignItems = 'center';
            modal.style.zIndex = '9999';

            const modalContent = document.createElement('div');
            modalContent.style.backgroundColor = 'white';
            modalContent.style.padding = '20px';
            modalContent.style.borderRadius = '10px';
            modalContent.style.textAlign = 'center';
            modalContent.style.maxWidth = '400px';
            modalContent.style.width = '90%';
            modalContent.style.position = 'relative';

            modalContent.innerHTML = `
                <button id="closeModalBtn" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
                <h3>Konfirmasi Hapus Artikel</h3>
                <p>Apakah Anda yakin ingin menghapus artikel:</p>
                <strong>"${articleTitle}"</strong>
                <div style="margin-top: 20px;">
                    <button id="confirmBtn" style="background-color: #dc3545; color: white; padding: 10px 20px; margin-right: 10px; border: none; border-radius: 5px;">Hapus</button>
                    <button id="cancelBtn" style="background-color: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px;">Batal</button>
                </div>
            `;

            modal.appendChild(modalContent);
            document.body.appendChild(modal);

            return new Promise((resolve) => {
                const confirmBtn = document.getElementById('confirmBtn');
                const cancelBtn = document.getElementById('cancelBtn');
                const closeModalBtn = document.getElementById('closeModalBtn');

                const removeModal = () => {
                    document.body.removeChild(modal);
                };

                const handleConfirm = () => {
                    removeModal();
                    resolve(true);
                };

                const handleCancel = () => {
                    removeModal();
                    resolve(false);
                };

                confirmBtn.addEventListener('click', handleConfirm);
                cancelBtn.addEventListener('click', handleCancel);
                closeModalBtn.addEventListener('click', handleCancel);

                // Tutup modal jika mengklik di luar area
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        removeModal();
                        resolve(false);
                    }
                });

                // Tambahkan event listener untuk tombol escape
                const handleEscapeKey = (e) => {
                    if (e.key === 'Escape') {
                        removeModal();
                        resolve(false);
                        document.removeEventListener('keydown', handleEscapeKey);
                    }
                };
                document.addEventListener('keydown', handleEscapeKey);
            });
        }

        // Tambahkan event listener untuk konfirmasi hapus
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('form[action="hapus_artikel.php"]');
            deleteButtons.forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const articleTitle = this.querySelector('input[name="id"]').closest('tr').querySelector('.article-title').textContent.trim();
                    const confirmed = await confirmDelete(articleTitle);

                    if (confirmed) {
                        this.submit();
                    }
                });
            });
        });

        // Auto-submit search form on Enter
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.closest('form').submit();
                    }
                });
            }
        });
    </script>
</body>

</html>