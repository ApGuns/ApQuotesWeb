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
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; // Periksa status admin

// Mendapatkan post ID dari URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Admin dapat mengedit semua post, user hanya post mereka
    $query = $is_admin ? "SELECT * FROM posts WHERE id = ?" : "SELECT * FROM posts WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    if ($is_admin) {
        $stmt->bind_param("i", $post_id);
    } else {
        $stmt->bind_param("ii", $post_id, $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if ($post) {
        // Jika post ditemukan, tampilkan form untuk mengedit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_content = $_POST['content'];

            // Update post
            $update_query = $is_admin ? "UPDATE posts SET content = ? WHERE id = ?" : "UPDATE posts SET content = ? WHERE id = ? AND user_id = ?";
            $update_stmt = $conn->prepare($update_query);

            if ($is_admin) {
                $update_stmt->bind_param("si", $new_content, $post_id);
            } else {
                $update_stmt->bind_param("sii", $new_content, $post_id, $user_id);
            }

            if ($update_stmt->execute()) {
                header("Location: index.php?message=" . urlencode("Post berhasil diperbarui."));
                exit();
            } else {
                $error_message = "Terjadi kesalahan saat memperbarui post.";
            }
        }
    } else {
        header("Location: index.php?error=" . urlencode("Post tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya."));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="stylesheet" href="create-post-style.css">
</head>
<body>
<?php include_once('header.php'); ?>
<main>
    <div class="content-wrapper">
        <h2>Edit Post</h2>
        <?php if (!empty($error_message)) { ?>
            <p class="error"><?= htmlspecialchars($error_message); ?></p>
        <?php } ?>
        <?php if (!empty($success_message)) { ?>
            <p class="success"><?= htmlspecialchars($success_message); ?></p>
            <a href="index.php" class="back-btn">Lihat Semua Post</a>
        <?php } ?>
        <?php if (empty($success_message)) { ?>
            <form action="edit_post.php?id=<?= $post_id; ?>" method="post">
                <textarea name="content" placeholder="Tulis kembali konten Anda..." required><?= htmlspecialchars($post['content']); ?></textarea>
                <button type="submit">Perbarui Post</button>
            </form>
        <?php } ?>
    </div>
</main>
<?php include_once('footer.php'); ?>
</body>
</html>
