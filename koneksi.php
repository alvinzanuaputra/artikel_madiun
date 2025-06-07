<?php
try {
    $pdo = new PDO("mysql:host=localhost;port=3308;dbname=kota_madiun", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?> 