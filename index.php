<?php
session_start();

include 'koneksi.php';


// Cek status login
$isLoggedIn = isset($_SESSION['author_id']) && !empty($_SESSION['author_id']);
$userNickname = $isLoggedIn && isset($_SESSION['nickname']) ? $_SESSION['nickname'] : null;

// Ambil role dari database jika belum ada di session
$userRole = null;
if ($isLoggedIn) {
    if (isset($_SESSION['role'])) {
        $userRole = $_SESSION['role'];
    } else {
        // Query ke database untuk mendapatkan role
        $stmt = $koneksi->prepare("SELECT role FROM author WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['author_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $userRole = $row['role'];
                $_SESSION['role'] = $userRole; // Simpan ke session untuk penggunaan selanjutnya
            } else {
                $userRole = 'pengunjung'; // Default jika user tidak ditemukan
            }
            $stmt->close();
        } else {
            $userRole = 'pengunjung'; // Default jika query gagal
        }
    }
}

// // Debug sementara (hapus setelah selesai debug)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// // Debug session (hapus setelah selesai debug)
// echo "<pre>";
// echo "SESSION: ";
// print_r($_SESSION);
// echo "isLoggedIn: " . ($isLoggedIn ? 'true' : 'false') . "\n";
// echo "userRole: " . $userRole . "\n";
// echo "userNickname: " . $userNickname . "\n";
// echo "author_id: " . (isset($_SESSION['author_id']) ? $_SESSION['author_id'] : 'not set') . "\n";
// echo "</pre>";

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<!-- Sisa kode HTML sama seperti sebelumnya -->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Artikel - Madiun Blog</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        /* Additional styles for index page */
        .welcome-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 2.5rem;
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .welcome-title {
            font-family: 'Pacifico', cursive;
            font-size: 2.2rem;
            color: var(--dark-pink);
            margin-bottom: 1rem;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .user-info {
            background: linear-gradient(135deg, var(--light-pink), var(--accent-pink));
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            color: var(--text-color);
        }

        .user-info strong {
            color: var(--dark-pink);
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-custom {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1rem;
            font-family: 'Quicksand', sans-serif;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-pink), var(--accent-pink));
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(240, 128, 170, 0.3);
        }

        .btn-success-custom {
            background: linear-gradient(135deg, var(--dark-pink), var(--primary-pink));
            color: white;
        }

        .btn-success-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(224, 107, 139, 0.3);
        }

        .btn-danger-custom {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(231, 76, 60, 0.3);
        }

        /* Toaster Styles */
        .toaster {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #d4749b;
            /* pink agak gelap */
            color: black;
            /* tulisan hitam */
            padding: 1rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 8px 20px rgba(224, 107, 139, 0.4);
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.4s cubic-bezier(0.26, 1.36, 0.65, 1);
            max-width: 320px;
            font-weight: 600;
            font-family: 'Quicksand', sans-serif;
        }

        .toaster.show {
            opacity: 1;
            transform: translateX(0);
        }

        .toaster .close-btn {
            background: none;
            border: none;
            color: black;
            font-size: 1.2rem;
            font-weight: bold;
            float: right;
            cursor: pointer;
            margin-left: 1rem;
            padding: 0;
            line-height: 1;
        }

        .toaster .close-btn:hover {
            transform: rotate(90deg);
            transition: transform 0.3s ease;
        }

        /* Decorative elements */
        .welcome-container::before {
            content: '♡';
            position: absolute;
            top: -10px;
            right: 20px;
            font-size: 2rem;
            color: var(--light-pink);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <h1>Madiun Blog</h1>
        <p>Madiun : Kota Pendekar, Surga Kuliner dan Pesona yang Tak Terlupakan</p>
    </header>

    <!-- Welcome Container -->
    <div class="welcome-container">
        <h2 class="welcome-title">Selamat Datang!</h2>
        <p class="welcome-subtitle">
            Bergabunglah dengan komunitas penulis dan pembaca di Madiun Blog.
            Temukan artikel menarik atau bagikan cerita Anda sendiri.
        </p>

        <?php if ($isLoggedIn): ?>
            <div class="user-info">
                <p>🌸 Selamat datang kembali, <strong><?= htmlspecialchars($userNickname) ?></strong>!</p>
                <p>Status Anda: <strong><?= ucfirst($userRole) ?></strong> ✨</p>
            </div>
        <?php endif; ?>

        <div class="button-container">
            <!-- Tombol 1: Lihat Semua Artikel -->
            <button class="btn-custom btn-primary-custom" onclick="handleLihatArtikel()">
                📚 Lihat Semua Artikel
            </button>

            <!-- Tombol 2: Portal dinamis -->
            <?php if ($isLoggedIn): ?>
                <?php if ($userRole === 'penulis'): ?>
                    <a href="dashboard.php" class="btn-custom btn-success-custom">✍️ Portal Penulis</a>
                <?php elseif ($userRole === 'pengunjung'): ?>
                    <a href="main.php" class="btn-custom btn-success-custom">👁‍🗨 Portal Pengunjung</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn-custom btn-success-custom">🚪 Masuk</a>
            <?php endif; ?>

            <!-- Tombol 3: Keluar (hanya tampil jika login) -->
            <?php if ($isLoggedIn): ?>
                <a href="?logout=1" class="btn-custom btn-danger-custom" onclick="return confirm('Yakin ingin keluar?')">
                    🚪 Keluar
                </a>
            <?php endif; ?>
        </div>
    </div>

    <footer style="margin-top: 50px; padding: 46px 0 46px 0;">
        <p>© <?= date('Y') ?> Jelajah Nusantara | Kuliner jadi puisi, budaya jadi simfoni.</p>
        <p>Ditulis oleh Sasabila Alya – Universitas Negeri Malang | Artikel Madiun Blog</p>
    </footer>

    <!-- Toaster container -->
    <div id="toaster" class="toaster">
        <button class="close-btn" onclick="hideToaster()">&times;</button>
        <span id="toaster-message"></span>
    </div>

    <script>
        function showToaster(message) {
            const toaster = document.getElementById('toaster');
            const messageElement = document.getElementById('toaster-message');

            messageElement.textContent = message;
            toaster.classList.add('show');

            // Auto hide after 5 seconds
            setTimeout(() => {
                hideToaster();
            }, 5000);
        }

        function hideToaster() {
            document.getElementById('toaster').classList.remove('show');
        }

        function handleLihatArtikel() {
            <?php if ($isLoggedIn): ?>
                <?php if ($userRole === 'penulis'): ?>
                    showToaster("Anda sudah masuk sebagai penulis");
                <?php elseif ($userRole === 'pengunjung'): ?>
                    showToaster("Anda sudah masuk sebagai pengunjung");
                <?php endif; ?>
            <?php else: ?>
                showToaster("Masuk untuk melihat artikel atau menulis artikel");
            <?php endif; ?>
        }
    </script>

</body>

</html>