<?php
session_start();
require 'koneksi.php';

// User Authentication Functions
function registerUser($nickname, $email, $password) {
    global $pdo;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO author (nickname, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$nickname, $email, $hashed_password]);
}

function loginUser($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM author WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function getAuthorById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM author WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAuthorByNickname($nickname) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM author WHERE nickname = ?");
    $stmt->execute([$nickname]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAuthorByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM author WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Utility Functions
function isLoggedIn() {
    return isset($_SESSION['author_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Redirect jika sudah login
if (isset($_SESSION['author_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = trim($_POST['nickname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi input
    if (empty($nickname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi.";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek apakah nickname atau email sudah ada
        if (getAuthorByNickname($nickname)) {
            $error = "Nickname sudah terdaftar.";
        } elseif (getAuthorByEmail($email)) {
            $error = "Email sudah terdaftar.";
        } else {
            // Register author
            if (registerUser($nickname, $email, $password)) {
                $message = "Registrasi berhasil! Silakan login.";
                // Reset form
                $nickname = $email = '';
            } else {
                $error = "Gagal mendaftar. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Madiun Blog</title>
    <link rel="icon" href="./assets/img/head_logo.jpg" type="image/jpg">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: #666;
            margin: 0;
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
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #e74c3c;
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
        
        .btn-auth {
            width: 100%;
            padding: 12px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-auth:hover {
            background-color: #c0392b;
        }
        
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        
        .auth-links a {
            color: #e74c3c;
            text-decoration: none;
        }
        
        .auth-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Registrasi</h1>
            <p>Daftar untuk mulai berbagi cerita tentang Madiun</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nickname">Username *</label>
                <input type="text" id="nickname" name="nickname" value="<?= isset($nickname) ? htmlspecialchars($nickname) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
                <small>Minimal 6 karakter</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-auth">Daftar</button>
        </form>

        <div class="auth-links">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            <p><a href="index.php">Kembali ke Beranda</a></p>
        </div>
    </div>

    <footer style="margin-top: 50px;">
        <p>© <?= date('Y') ?> Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni.</p>
    </footer>
</body>
</html>