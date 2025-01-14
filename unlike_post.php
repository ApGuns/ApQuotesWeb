<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek waktu aktivitas terakhir
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 900) { // 900 detik = 15 menit
    // Hapus session dan cookie
    session_unset();
    session_destroy();
    setcookie('user_id', '', time() - 3600, "/");
    setcookie('username', '', time() - 3600, "/");

    header("Location: login.php?error=" . urlencode("Sesi telah habis. Silakan login kembali."));
    exit();
}

// Perbarui waktu aktivitas terakhir
$_SESSION['last_activity'] = time();
$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

// Hapus like jika sudah ada
$query_delete_like = "DELETE FROM likes WHERE post_id = ? AND user_id = ?";
$stmt_delete = $conn->prepare($query_delete_like);
$stmt_delete->bind_param("ii", $post_id, $user_id);
$stmt_delete->execute();

$stmt_delete->close();

echo "Like removed successfully.";
?>
