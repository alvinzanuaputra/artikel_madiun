<?php
session_start();
require 'koneksi.php';

function getArticleById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT a.*, 
                         (SELECT picture FROM article WHERE id = ?) as picture,
                         (SELECT GROUP_CONCAT(author_id) FROM article_author WHERE article_id = ?) as author_ids
                         FROM article a
                         WHERE a.id = ?");
        $stmt->execute([$id, $id, $id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $article;
    } catch (PDOException $e) {
        return false;
    }
}

function hapusArtikel($id) {
    global $pdo, $current_author_id;
    
    try {
        $pdo->beginTransaction();
        
        // Log awal proses hapus
       
        
        // Hapus relasi artikel dengan kategori
        $categoryStmt = $pdo->prepare("DELETE FROM article_category WHERE article_id = ?");
        $categoryResult = $categoryStmt->execute([$id]);
        
        // Hapus relasi artikel dengan penulis
        $authorStmt = $pdo->prepare("DELETE FROM article_author WHERE article_id = ?");
        $authorResult = $authorStmt->execute([$id]);
        
        // Hapus artikel dari tabel artikel
        $stmt = $pdo->prepare("DELETE FROM article WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        // Log hasil hapus artikel
        if (!$result) {
            throw new Exception("Gagal menghapus artikel utama.");
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return $e->getMessage();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Cek apakah user sudah login
if (!isset($_SESSION['author_id'])) {
    $_SESSION['message'] = "Anda harus login terlebih dahulu.";
    $_SESSION['message_type'] = "error";
    header("Location: login.php");
    exit();
}

$current_author_id = $_SESSION['author_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $article_id = $_POST['id'];
    
    // Log awal prose
    
    // Get article data to delete associated image
    $article = getArticleById($article_id);
    
    if ($article) {
        // Cek apakah artikel milik penulis yang sedang login
        $author_ids = explode(',', $article['author_ids'] ?? '');
        
        // Log detail penulis
       
          
        
        if (!in_array($current_author_id, $author_ids)) {
            $error_message = "Anda tidak memiliki izin untuk menghapus artikel ini.";
            
            // Log percobaan hapus artikel yang tidak sah
            $_SESSION['message'] = $error_message;
            $_SESSION['message_type'] = "error";
            header("Location: dashboard.php");
            exit();
        }

        // Delete the article
        $delete_result = hapusArtikel($article_id);
        
        if ($delete_result === true) {
            // Hapus file gambar jika ada
            if ($article['picture'] && file_exists('assets/img/' . $article['picture'])) {
                $image_path = 'assets/img/' . $article['picture'];
                $unlink_result = unlink($image_path);
                
    
            }
            
            // Set success message in session
            $_SESSION['message'] = "Artikel berhasil dihapus.";
            $_SESSION['message_type'] = "success";
        } else {
            // Set error message in session
            $_SESSION['message'] = "Gagal menghapus artikel. " . $delete_result;
            $_SESSION['message_type'] = "error";
        }
    } else {
        // Log artikel tidak ditemukan
        
        $_SESSION['message'] = "Artikel tidak ditemukan.";
        $_SESSION['message_type'] = "error";
    }
} else {
    // Log percobaan hapus tidak valid
    
    $_SESSION['message'] = "Permintaan tidak valid.";
    $_SESSION['message_type'] = "error";
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>