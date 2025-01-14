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

// Ambil data post milik pengguna
$sql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Proses hapus post
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Hapus post dari database
    $delete_sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $delete_id, $user_id);
    if ($delete_stmt->execute()) {
        // Setelah berhasil menghapus, lakukan pengalihan
        header("Location: my_post.php");
        exit(); // pastikan tidak ada kode yang dieksekusi setelah pengalihan
    } else {
        echo "Error deleting post.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts</title>
    <link rel="stylesheet" href="my-posts-style.css">
</head>

<body>
    <?php include_once('header.php'); ?>
    <main>
        <div class="content-wrapper">
            <h2>My Posts</h2>

            <?php if ($result->num_rows > 0): ?>
                <ul class="post-list">
                    <?php while ($post = $result->fetch_assoc()): ?>
                        <li class="post-card">
                            <div class="post-content">
                                <p><?= htmlspecialchars($post['content']); ?></p>
                                <small>Created on: <?= $post['created_at']; ?></small>
                            </div>
                            <div class="post-actions">
                                <a href="edit_post.php?id=<?= $post['id']; ?>" class="edit-btn">Edit</a>
                                <a href="my_post.php?delete_id=<?= $post['id']; ?>" class="delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus post ini?')">Delete</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have not posted anything yet.</p>
            <?php endif; ?>
        </div>
    </main>
    <?php include_once('footer.php'); ?>
</body>

</html>
