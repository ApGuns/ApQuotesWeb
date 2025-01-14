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
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pulihkan session dari cookie jika session belum ada
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
}

// Status login
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : '';
?>


<?php
require_once('koneksi.php');
// Cek login


// Inisialisasi variabel
$error_message = "";
$success_message = "";

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);

    if (empty($content)) {
        $error_message = "Konten tidak boleh kosong.";
    } else {
        // Insert post ke database
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user_id, $content);

        if ($stmt->execute()) {
            $success_message = "Post berhasil dibuat!";
        } else {
            $error_message = "Terjadi kesalahan saat membuat post.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link rel="stylesheet" href="create-post-style.css">

</head>
<body>
<?php include_once('header.php'); ?>
<main>
    <div class="content-wrapper">
        <h2>Create Post</h2>
        <?php if (!empty($error_message)) { ?>
            <p class="error"><?= htmlspecialchars($error_message); ?></p>
        <?php } ?>
        <?php if (!empty($success_message)) { ?>
            <p class="success"><?= htmlspecialchars($success_message); ?></p>
            <a href="index.php" class="back-btn">Lihat Semua Post</a>
        <?php } ?>
        <?php if (empty($success_message)) { ?>
            <form action="create_post.php" method="post">
                <textarea name="content" placeholder="Tulis Quotes Anda di sini..." required></textarea>
                <button type="submit">Post</button>
            </form>
        <?php } ?>
    </div>
</main>
<?php include_once('footer.php'); ?>
</body>
</html>
