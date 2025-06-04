<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=kota_madiun", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Article Functions
function getAllArticles() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM articles ORDER BY date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticleById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function tambahArtikel($title, $author, $category, $content, $picture) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO articles (title, author, category, content, picture, date) VALUES (?, ?, ?, ?, ?, NOW())");
    return $stmt->execute([$title, $author, $category, $content, $picture]);
}

function editArtikel($id, $title, $author, $category, $content, $picture) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE articles SET title=?, author=?, category=?, content=?, picture=? WHERE id=?");
    return $stmt->execute([$title, $author, $category, $content, $picture, $id]);
}

function hapusArtikel($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id=?");
    return $stmt->execute([$id]);
}

// User Authentication Functions
function registerUser($username, $email, $password, $full_name) {
    global $pdo;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, created_at) VALUES (?, ?, ?, ?, NOW())");
    return $stmt->execute([$username, $email, $hashed_password, $full_name]);
}

function loginUser($username_or_email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username_or_email, $username_or_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserByUsername($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Utility Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date("d F Y", strtotime($date));
}

function truncateText($text, $length = 200) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>