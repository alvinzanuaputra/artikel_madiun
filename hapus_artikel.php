<?php
session_start();
require 'functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $article_id = $_POST['id'];
    
    // Get article data to delete associated image
    $article = getArticleById($article_id);
    
    if ($article) {
        // Delete the article
        if (hapusArtikel($article_id)) {
            // Delete associated image file if exists
            if ($article['picture'] && file_exists('img/' . $article['picture'])) {
                unlink('img/' . $article['picture']);
            }
            
            // Set success message in session
            $_SESSION['message'] = "Artikel berhasil dihapus.";
            $_SESSION['message_type'] = "success";
        } else {
            // Set error message in session
            $_SESSION['message'] = "Gagal menghapus artikel.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Artikel tidak ditemukan.";
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "ID artikel tidak valid.";
    $_SESSION['message_type'] = "error";
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
?>