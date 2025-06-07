<?php
session_start();
require 'koneksi.php';

function loginUser($login, $password) {
    global $pdo;
    // Cari user berdasarkan email atau nickname
    $stmt = $pdo->prepare("SELECT * FROM author WHERE email = ? OR nickname = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['author_id']);
}

// Redirect jika sudah login
if (isset($_SESSION['author_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$login = ''; // Inisialisasi $login sebagai string kosong

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']); // Ganti email menjadi login
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = "Username/Email dan password harus diisi.";
    } else {
        $user = loginUser($login, $password);
        if ($user) {
            // Set session
            $_SESSION['author_id'] = $user['id'];
            $_SESSION['nickname'] = $user['nickname'];
            $_SESSION['email'] = $user['email'];
            
            // Tambahkan pesan toast
            $_SESSION['toast_message'] = 'Login berhasil. Selamat datang, ' . htmlspecialchars($user['nickname']) . '!';
            $_SESSION['toast_type'] = 'success';
            
            // Redirect to index
            header("Location: index.php");
            exit();
        } else {
            $error = "Username/Email atau password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Madiun Blog</title>
    <link rel="icon" href="/assets/img/head_logo.jpg" type="image/jpg">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 80px auto;
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
        
        .alert-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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
            <h1>Login</h1>
            <p>Masuk untuk mengelola artikel Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="login">Username/Email</label>
                <input type="text" id="login" name="login" value="<?= isset($login) ? htmlspecialchars($login) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-auth">Login</button>
        </form>

        <div class="auth-links">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            <p><a href="index.php">Kembali ke Beranda</a></p>
        </div>
    </div>

    <footer style="margin-top: 50px;">
        <p>© <?= date('Y') ?> Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni.</p>
    </footer>
</body>
</html>