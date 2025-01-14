<?php
session_start();
require_once('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Tambahkan komentar ke database
    $query = "INSERT INTO comments (post_id, user_id, comment, created_at) VALUES ('$post_id', '$user_id', '$comment', NOW())";
    mysqli_query($conn, $query);

    // Hitung jumlah komentar baru
    $count_query = "SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = '$post_id'";
    $count_result = mysqli_query($conn, $count_query);
    $comment_count = mysqli_fetch_assoc($count_result)['comment_count'];

    echo json_encode([
        'success' => true,
        'username' => $_SESSION['username'],
        'comment' => $comment,
        'comment_count' => $comment_count
    ]);
}
