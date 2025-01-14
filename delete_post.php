<?php
session_start();
require_once('koneksi.php');

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; // Periksa status admin

// Mendapatkan post ID dari URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Admin dapat menghapus semua post, user hanya post mereka
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
        // Menghapus post berdasarkan kondisi
        $query = $is_admin ? "DELETE FROM posts WHERE id = ?" : "DELETE FROM posts WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        if ($is_admin) {
            $stmt->bind_param("i", $post_id);
        } else {
            $stmt->bind_param("ii", $post_id, $user_id);
        }

        if ($stmt->execute()) {
            header("Location: index.php?message=" . urlencode("Post berhasil dihapus"));
            exit();
        } else {
            $error_message = "Terjadi kesalahan saat menghapus post.";
        }
    } else {
        header("Location: index.php?error=" . urlencode("Post tidak ditemukan atau Anda tidak memiliki izin."));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
