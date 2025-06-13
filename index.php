<?php
session_start();
require 'koneksi.php';

// Fungsi untuk memeriksa status login
function isLoggedIn() {
    return isset($_SESSION['author_id']);
}

// Fungsi untuk mendapatkan semua kategori
function getAllCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT name FROM category ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mendapatkan artikel dengan filter pencarian dan kategori
function getFilteredArticles($search = '', $category = '') {
    global $pdo;
    
    $whereConditions = [];
    $params = [];
    
    // Base query
    $sql = "SELECT a.id, a.date, a.title, a.content, a.picture, 
            GROUP_CONCAT(DISTINCT au.nickname) as author_nickname, 
            GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names
            FROM article a
            JOIN article_author aa ON a.id = aa.article_id
            JOIN author au ON aa.author_id = au.id
            LEFT JOIN article_category ac ON a.id = ac.article_id
            LEFT JOIN category c ON ac.category_id = c.id";
    
    // Add search condition
    if (!empty($search)) {
        $whereConditions[] = "(a.title LIKE ? OR a.content LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    // Add category condition
    if (!empty($category) && $category !== 'semua') {
        $whereConditions[] = "c.name = ?";
        $params[] = $category;
    }
    
    // Combine conditions
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $sql .= " GROUP BY a.id, a.date, a.title, a.content, a.picture
              ORDER BY a.date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mendapatkan parameter pencarian dan kategori
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

// Mendapatkan artikel berdasarkan filter
$articles = getFilteredArticles($search, $selectedCategory);
$categories = getAllCategories();

// Jika $articles tidak array, inisialisasi sebagai array kosong
if (!is_array($articles)) {
    $articles = [];
}

// Hitung total artikel
$totalArticles = count($articles);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Blog tentang keindahan dan pesona Kota Madiun - kuliner, tempat wisata, dan budaya">
    <title>Madiun : Kota Pendekar, Surga Kuliner dan Pesona yang Tak Terlupakan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="./assets/img/head_logo.jpg" type="image/jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Navigation Styles */
        .navigation {
            background: #fff;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .nav-menu {
            display: flex;
            gap: 25px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .nav-menu a:hover, .nav-menu a.active {
            background-color: #e74c3c;
            color: white;
        }
        
        /* Search & Filter Section */
        .search-filter-section {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        /* Results Info */
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
        
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .no-results i {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .no-results p {
            color: #999;
            margin-bottom: 20px;
        }
        
        /* Toast Styles */
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
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-menu {
                justify-content: center;
                width: 100%;
            }
            
            .search-filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .category-filter {
                justify-content: center;
            }
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
        <h1>Madiun : Kota Pendekar, Surga Kuliner dan Pesona yang Tak Terlupakan</h1>
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

    <!-- Search & Filter Section -->
    <div class="search-filter-section">
        <form method="GET" action="" class="search-filter-container">
            <div class="search-box">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Cari artikel, kuliner, tempat wisata..." 
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
                    <a href="index.php" class="clear-filters">
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
            <p>Ditemukan <?= $totalArticles ?> artikel</p>
        </div>
    <?php endif; ?>

    <!-- Articles Container -->
    <div class="blog-container">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $article): ?>
            <div class="article-card">
                <h2 class="title">
                    <?php
                    $title = htmlspecialchars($article['title']);
                    if (!empty($search)) {
                        $title = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<span class="highlight">$1</span>', $title);
                    }
                    echo $title;
                    ?>
                </h2>
                <p class="meta">
                    Dipublikasikan: <?= date("d F Y", strtotime($article['date'])) ?> |
                    Penulis: <?= htmlspecialchars($article['author_nickname']) ?> |
                    Kategori: <?= htmlspecialchars($article['category_names']) ?>
                </p>

                <img src="assets/img/<?= htmlspecialchars($article['picture']) ?>" 
                     class="article-image" 
                     alt="<?= htmlspecialchars($article['title']) ?>" 
                     loading="lazy">

                <div class="content">
                    <p>
                        <?php
                        $content = htmlspecialchars(substr($article['content'], 0, 200)) . '...';
                        if (!empty($search)) {
                            $content = preg_replace('/(' . preg_quote($search, '/') . ')/i', '<span class="highlight">$1</span>', $content);
                        }
                        echo $content;
                        ?>
                    </p>

                    <button onclick="showArticle(<?= $article['id'] ?>)" class="more-btn">
                        <i class="fas fa-book-open"></i> Selengkapnya
                    </button>
                </div>
            </div>

            <div id="fullscreen-<?= $article['id'] ?>" class="fullscreen-article" style="display: none;">
                <div class="fullscreen-content">
                    <button class="close-btn" onclick="hideArticle(<?= $article['id'] ?>)" aria-label="Tutup artikel">×</button>
                    <h2><?= htmlspecialchars($article['title']) ?></h2>
                    <p class="meta">
                        Dipublikasikan: <?= date("d F Y", strtotime($article['date'])) ?> |
                        Penulis: <?= htmlspecialchars($article['author_nickname']) ?> |
                        Kategori: <?= htmlspecialchars($article['category_names']) ?>
                    </p>
                    <img src="assets/img/<?= htmlspecialchars($article['picture']) ?>" 
                         style="max-width: 100%; height: auto; margin-bottom: 15px;" 
                         alt="<?= htmlspecialchars($article['title']) ?>">
                    <div class="full-content">
                        <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- No Results Found -->
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Tidak ada artikel ditemukan</h3>
                <p>
                    <?php if (!empty($search) && !empty($selectedCategory)): ?>
                        Tidak ada artikel yang cocok dengan pencarian "<?= htmlspecialchars($search) ?>" dalam kategori "<?= htmlspecialchars($selectedCategory) ?>"
                    <?php elseif (!empty($search)): ?>
                        Tidak ada artikel yang cocok dengan pencarian "<?= htmlspecialchars($search) ?>"
                    <?php elseif (!empty($selectedCategory)): ?>
                        Belum ada artikel dalam kategori "<?= htmlspecialchars($selectedCategory) ?>"
                    <?php else: ?>
                        Belum ada artikel yang dipublikasikan
                    <?php endif; ?>
                </p>
                <a href="index.php" class="btn">Lihat Semua Artikel</a>
            </div>
        <?php endif; ?>
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

            // Auto-submit search form on Enter
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

        // Highlight search terms in content
        function highlightSearchTerms() {
            const searchTerm = "<?= htmlspecialchars($search) ?>";
            if (searchTerm) {
                const regex = new RegExp(`(${searchTerm})`, 'gi');
                const elements = document.querySelectorAll('.content p, .title');
                
                elements.forEach(element => {
                    if (element.innerHTML && !element.querySelector('.highlight')) {
                        element.innerHTML = element.innerHTML.replace(regex, '<span class="highlight">$1</span>');
                    }
                });
            }
        }

        // Call highlight function after DOM is loaded
        document.addEventListener('DOMContentLoaded', highlightSearchTerms);
    </script>
</body>
</html>