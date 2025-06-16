<?php
// Set session timeout 1 jam (3600 detik) SEBELUM session_start()
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);

session_start();
require 'koneksi.php';

// Fungsi untuk cek session timeout
function isLoggedIn()
{
    // Cek apakah session masih valid
    if (isset($_SESSION['author_id']) && isset($_SESSION['last_activity'])) {
        // Cek apakah session sudah expired (1 jam = 3600 detik)
        if (time() - $_SESSION['last_activity'] > 3600) {
            // Session expired, destroy session
            $_SESSION['toast_message'] = 'Session Anda telah berakhir. Silakan login kembali.';
            $_SESSION['toast_type'] = 'error';
            session_unset();
            session_destroy();
            return false;
        }
        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

// Fungsi untuk cek role/permission author
function isAuthorized()
{
    global $pdo;
    
    // Pastikan user sudah login
    if (!isLoggedIn()) {
        return false;
    }
    
    // Cek apakah user memiliki role yang sesuai
    if (isset($_SESSION['role'])) {
        // Hanya role 'penulis' yang diizinkan mengakses dashboard
        return $_SESSION['role'] === 'penulis';
    }
    
    // Jika tidak ada role di session, cek dari database
    $stmt = $pdo->prepare("SELECT role FROM author WHERE id = ?");
    $stmt->execute([$_SESSION['author_id']]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($author) {
        $_SESSION['role'] = $author['role']; // Simpan role ke session untuk cek selanjutnya
        // Hanya role 'penulis' yang diizinkan
        return $author['role'] === 'penulis';
    }
    
    return false;
}

// Fungsi untuk mendapatkan informasi lengkap author
function getAuthorInfo($author_id)
{
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, nickname, email, role FROM author WHERE id = ?");
    $stmt->execute([$author_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Pastikan hanya penulis yang bisa mengakses halaman dashboard
if (!isAuthorized()) {
    // Cek apakah user sudah login tapi role-nya pengunjung
    if (isset($_SESSION['author_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'pengunjung') {
        // User login sebagai pengunjung - akses ditolak
        $_SESSION['toast_message'] = 'Akses ditolak! Hanya penulis yang dapat mengakses dashboard. Anda login sebagai pengunjung.';
        $_SESSION['toast_type'] = 'error';
        
        // Log attempt untuk keamanan
        error_log("Dashboard access denied for visitor: " . ($_SESSION['email'] ?? $_SESSION['nickname'] ?? 'unknown') . " (ID: " . $_SESSION['author_id'] . ")");
        
        // Redirect ke halaman utama
        header("Location: main.php");
        exit();
    } 
    // Jika belum login sama sekali atau ada masalah dengan session
    else {
        $_SESSION['toast_message'] = 'Silakan login sebagai penulis untuk mengakses dashboard.';
        $_SESSION['toast_type'] = 'error';
        header("Location: login.php");
        exit();
    }
}

// Double check - pastikan role benar-benar 'penulis'
if ($_SESSION['role'] !== 'penulis') {
    $_SESSION['toast_message'] = 'Akses ditolak! Hanya penulis yang dapat mengakses dashboard.';
    $_SESSION['toast_type'] = 'error';
    
    // Hapus session yang tidak valid
    session_unset();
    session_destroy();
    
    header("Location: login.php");
    exit();
}

// Ambil informasi author yang sudah terverifikasi
$current_author_id = $_SESSION['author_id'];
$current_author_nickname = $_SESSION['nickname'];
$current_author_role = $_SESSION['role']; // Pasti 'penulis' karena sudah dicek di atas

// Fungsi untuk logging aktivitas (opsional - jika ingin menambah tabel log)
function logUserActivity($action, $details = '')
{
    global $pdo;
    
    try {
        // Pastikan tabel user_activity_log ada, jika tidak buat sederhana
        $stmt = $pdo->prepare("INSERT INTO user_activity_log (author_id, action, details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW()) 
                               ON DUPLICATE KEY UPDATE action = action"); // Ignore jika error
        $stmt->execute([
            $_SESSION['author_id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Jika tabel belum ada, bisa diabaikan atau buat log ke file
        error_log("User activity: " . $_SESSION['nickname'] . " - " . $action . " - " . $details);
    }
}

// Log akses dashboard
logUserActivity('dashboard_access', 'Penulis mengakses dashboard');

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

// Hitung sisa waktu session
$sessionTimeLeft = 0;
if (isset($_SESSION['last_activity'])) {
    $sessionTimeLeft = 3600 - (time() - $_SESSION['last_activity']); // 3600 detik = 1 jam
    if ($sessionTimeLeft < 0) $sessionTimeLeft = 0;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Madiun Blog</title>
    <link rel="stylesheet" href="./css/style.css">
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

        .session-info-bar {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex-wrap: wrap;
            gap: 10px;
        }

        .session-timer {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: bold;
        }

        .session-timer i {
            color: #ffc107;
        }

        .session-warning {
            background: linear-gradient(135deg, #fd7e14, #e63946) !important;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }

            100% {
                opacity: 1;
            }
        }

        .extend-session-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .extend-session-btn:hover {
            background: #218838;
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

            .session-info-bar {
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
            <a href="main.php" class="btn" style="background-color: #28a745; color: white;"><i class="fas fa-eye"></i> Semua Artikel</a>
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
                                    <img src="./assets/img/<?= htmlspecialchars($article['picture']) ?>"
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
        <p>Ditulis oleh Sasabila Alya – Universitas Negeri Malang | Artikel Madiun Blog</p>
    </footer>

    <script>
        // Session Timer
        let sessionTimeLeft = <?= $sessionTimeLeft ?>;
        const sessionTimer = document.getElementById('sessionTimer');
        const sessionInfoBar = document.getElementById('sessionInfoBar');

        function updateSessionTimer() {
            if (sessionTimeLeft <= 0) {
                // Session habis, redirect ke login
                showToast('Session Anda telah berakhir. Silakan login kembali.', 'error');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
                return;
            }

            // Update display timer
            const hours = Math.floor(sessionTimeLeft / 3600);
            const minutes = Math.floor((sessionTimeLeft % 3600) / 60);
            const seconds = sessionTimeLeft % 60;

            sessionTimer.textContent =
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');

            // Berikan warning jika tinggal 5 menit
            if (sessionTimeLeft <= 300) { // 5 menit = 300 detik
                sessionInfoBar.classList.add('session-warning');

                // Tampilkan notifikasi warning setiap 1 menit
                if (sessionTimeLeft % 60 === 0) {
                    const minutesLeft = Math.floor(sessionTimeLeft / 60);
                    showToast(`Anda akan dikeluarkan otomatis dalam ${minutesLeft} menit!`, 'warning', 'setelahnya') ;
                }
            }

            sessionTimeLeft--;
        }

        // Update timer setiap detik
        setInterval(updateSessionTimer, 1000);

        // Fungsi untuk menampilkan toast notification
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');

            // Set class berdasarkan type
            let bgColor;
            switch (type) {
                case 'success':
                    bgColor = '#28a745';
                    break;
                case 'error':
                    bgColor = '#dc3545';
                    break;
                case 'warning':
                    bgColor = '#ffc107';
                    break;
                default:
                    bgColor = '#6c757d';
            }

            toast.style.cssText = `
                background-color: ${bgColor};
                color: white;
                padding: 15px 20px;
                margin-bottom: 10px;
                border-radius: 5px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease;
                cursor: pointer;
                position: relative;
                max-width: 400px;
                word-wrap: break-word;
            `;

            toast.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: 10px;">×</button>
                </div>
            `;

            toastContainer.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, 8000);

            // Click to dismiss
            toast.addEventListener('click', () => {
                toast.remove();
            });
        }

        // Fungsi untuk menampilkan artikel dalam fullscreen
        function showArticle(id) {
            const fullscreenDiv = document.getElementById('fullscreen-' + id);
            if (fullscreenDiv) {
                fullscreenDiv.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            }
        }

        // Fungsi untuk menyembunyikan artikel fullscreen
        function hideArticle(id) {
            const fullscreenDiv = document.getElementById('fullscreen-' + id);
            if (fullscreenDiv) {
                fullscreenDiv.style.display = 'none';
                document.body.style.overflow = 'auto'; // Enable scrolling
            }
        }

        // Fungsi konfirmasi hapus artikel
        function confirmDelete(title) {
            return confirm(`Apakah Anda yakin ingin menghapus artikel "${title}"?\n\nTindakan ini tidak dapat dibatalkan.`);
        }

        // CSS Animation untuk toast
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            .fullscreen-article {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.9);
                z-index: 10000;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
                box-sizing: border-box;
            }
            
            .fullscreen-content {
                background: white;
                padding: 30px;
                border-radius: 10px;
                max-width: 90%;
                max-height: 90%;
                overflow-y: auto;
                position: relative;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            }
            
            .close-btn {
                position: absolute;
                top: 15px;
                right: 20px;
                background: #dc3545;
                color: white;
                border: none;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                font-size: 20px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background-color 0.3s ease;
            }
            
            .close-btn:hover {
                background: #c82333;
            }
            
            .fullscreen-content h2 {
                margin-top: 0;
                color: #333;
                padding-right: 50px;
            }
            
            .fullscreen-content .meta {
                color: #666;
                font-size: 14px;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid #eee;
            }
            
            .fullscreen-content .full-content {
                line-height: 1.6;
                color: #333;
                text-align: justify;
            }
            
            /* Responsive design untuk fullscreen modal */
            @media (max-width: 768px) {
                .fullscreen-content {
                    padding: 20px;
                    max-width: 95%;
                    max-height: 95%;
                }
                
                .fullscreen-content h2 {
                    font-size: 1.5em;
                    padding-right: 40px;
                }
                
                .close-btn {
                    top: 10px;
                    right: 15px;
                    width: 30px;
                    height: 30px;
                    font-size: 18px;
                }
            }
        `;
        document.head.appendChild(style);

        // Event listener untuk ESC key pada fullscreen modal
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const fullscreenModals = document.querySelectorAll('.fullscreen-article');
                fullscreenModals.forEach(modal => {
                    if (modal.style.display === 'flex') {
                        modal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });

        // Auto-refresh halaman setiap 30 menit untuk mencegah session timeout yang tidak terduga
        setInterval(function() {
            // Hanya refresh jika tidak ada modal yang terbuka
            const openModals = document.querySelectorAll('.fullscreen-article[style*="flex"]');
            if (openModals.length === 0 && sessionTimeLeft > 1800) { // Hanya jika masih ada lebih dari 30 menit
                showToast('Halaman akan direfresh untuk menjaga keamanan session...', 'info');
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        }, 1800000); // 30 menit

        // Tampilkan toast jika ada pesan dari server
        <?php if (isset($_SESSION['toast_message'])): ?>
            showToast(
                '<?= addslashes($_SESSION['toast_message']) ?>',
                '<?= $_SESSION['toast_type'] ?? 'info' ?>'
            );
        <?php
            unset($_SESSION['toast_message']);
            unset($_SESSION['toast_type']);
        endif;
        ?>

        // Fungsi untuk smooth scroll ke hasil pencarian
        function scrollToResults() {
            const resultsInfo = document.querySelector('.results-info');
            if (resultsInfo) {
                resultsInfo.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        // Auto scroll ke hasil pencarian jika ada parameter search atau category
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search') || urlParams.has('category')) {
                setTimeout(scrollToResults, 500);
            }
        });

        // Lazy loading untuk gambar artikel
        const articleImages = document.querySelectorAll('.article-image-cell img');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '0';
                    img.style.transition = 'opacity 0.3s ease';

                    img.onload = () => {
                        img.style.opacity = '1';
                    };

                    observer.unobserve(img);
                }
            });
        });

    
        console.log('Dashboard berhasil dimuat!');
    </script>
</body>

</html>