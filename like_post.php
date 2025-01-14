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

// Cek apakah user sudah memberi like pada post ini
$query_check_like = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
$stmt_check = $conn->prepare($query_check_like);
$stmt_check->bind_param("ii", $post_id, $user_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows == 0) {
    // Tambahkan like jika user belum memberi like
    $query_insert_like = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($query_insert_like);
    $stmt_insert->bind_param("ii", $post_id, $user_id);
    $stmt_insert->execute();
}

$stmt_check->close();
$stmt_insert->close();

echo "Like added successfully.";
?>
