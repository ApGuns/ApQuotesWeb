<?php
session_start();

// Menghapus semua session
session_unset();

// Menghancurkan session
session_destroy();

// Menghapus cookies jika ada
setcookie('user_id', '', time() - 3600, '/');
setcookie('username', '', time() - 3600, '/');

// Mengarahkan ke index.php setelah logout
header("Location: index.php");
exit();
?>
