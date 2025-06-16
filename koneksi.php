<?php
$host = 'sql111.infinityfree.com';
$dbname = 'if0_39226153_kota_madiun';
$username = 'if0_39226153';
$password = 'sxZ44ICvZxSE';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
<!-- 

<?php
// Konfigurasi Database
// $host = 'sql111.infinityfree.com';
// $dbname = 'if0_39226153_kota_madiun';
// $username = 'if0_39226153';
// $password = 'sxZ44ICvZxSE';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?> -->