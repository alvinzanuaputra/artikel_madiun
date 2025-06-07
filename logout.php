<?php
session_start();
session_destroy();

session_start();
$_SESSION['toast_message'] = 'Anda berhasil logout.';
$_SESSION['toast_type'] = 'success';

header("Location: index.php");
exit();
?>