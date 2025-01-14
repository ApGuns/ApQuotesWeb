<?php
session_start();
require_once('koneksi.php');

// Periksa apakah pengguna sudah login
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

// Data user
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : '';
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; // Cek apakah user adalah admin

// Query untuk mengambil post
$query = "
    SELECT 
        posts.id AS post_id, 
        posts.content, 
        posts.created_at, 
        users.username, 
        (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comment_count,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ap Quotes</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include_once('header.php'); ?>

    <main>
        <div class="content-wrapper">
            <!-- Kata-kata Inspiratif -->
            <div class="inspirational-message">
                <p>Selamat datang di website quotes kami! 🎉</p>
                <p>
                    Bagikan inspirasi Anda kepada dunia! Klik tombol "Create Post" untuk membuat quotes Anda sendiri dan
                    lihat bagaimana kata-kata Anda dapat menyentuh hati orang lain. 🌟
                </p>
            </div>

            <!-- Main Content -->
            <div class="grid-container">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="grid-item">
                        <p>"<?= htmlspecialchars($row['content']); ?>"</p>
                        <p>
                            <strong>Post By:</strong> <?= htmlspecialchars($row['username']); ?> |
                            <strong>Tanggal:</strong> <?= htmlspecialchars(date("Y-m-d", strtotime($row['created_at']))); ?>
                        </p>
                        <div class="post-actions">
                            <button class="like-btn" <?= $is_logged_in ? '' : 'onclick="redirectToLogin(\'Like Post\')"' ?>>
                                👍 <span id="like-count-<?= $row['post_id'] ?>"><?= $row['like_count']; ?></span>
                            </button>
                            <button
                                class="comment-btn"
                                onclick="toggleComments('comments-section-<?= $row['post_id'] ?>')"
                                <?= $is_logged_in ? '' : 'onclick="redirectToLogin(\'Comment\')"' ?>>
                                💬 <span id="comment-count-<?= $row['post_id'] ?>"><?= $row['comment_count']; ?></span> Komentar
                            </button>
                        </div>

                        <!-- Comments Section -->
                        <div id="comments-section-<?= $row['post_id'] ?>" class="comments-section hidden">
                            <?php
                            $post_id = $row['post_id'];
                            $comment_query = "
                            SELECT comments.comment, users.username 
                            FROM comments 
                            JOIN users ON comments.user_id = users.id 
                            WHERE comments.post_id = ? 
                            ORDER BY comments.created_at ASC";

                            $comment_stmt = $conn->prepare($comment_query);
                            $comment_stmt->bind_param("i", $post_id);
                            $comment_stmt->execute();
                            $comment_result = $comment_stmt->get_result();

                            if ($comment_result->num_rows > 0) {
                                while ($comment_row = $comment_result->fetch_assoc()) {
                                    echo '<p><strong>' . htmlspecialchars($comment_row['username']) . ':</strong> ' . htmlspecialchars($comment_row['comment']) . '</p>';
                                }
                            } else {
                                echo '<p>Belum ada komentar.</p>';
                            }
                            ?>
                            <textarea placeholder="Tambahkan komentar..."></textarea>
                            <button class="submit-comment">Kirim</button>
                        </div>

                        <?php if ($is_admin) { ?>
                            <!-- Admin Controls -->
                            <div class="admin-controls">
                                <!-- Edit Button -->
                                <form action="edit_post.php" method="GET" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['post_id']; ?>">
                                    <button type="submit" class="edit-btn">Edit</button>
                                </form>

                                <!-- Delete Button -->
                                <form action="delete_post.php" method="GET" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['post_id']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Yakin ingin menghapus post ini?')">Hapus</button>
                                </form>
                            </div>
                        <?php } ?>

                    </div>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php include_once('footer.php'); ?>

    <script>
        // Fungsi untuk mengarahkan ke halaman login
        function redirectToLogin(action) {
            alert(`Anda harus login terlebih dahulu untuk ${action}.`);
            window.location.href = 'login.php';
        }

        // Fungsi untuk menampilkan/menyembunyikan bagian komentar
        function toggleComments(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.toggle('hidden');
            }
        }
    </script>
</body>

</html>