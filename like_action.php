<?php
session_start();
require_once('koneksi.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id']; // Ambil ID pengguna yang sedang login

    // Tambahkan like ke database
    $query = "INSERT INTO likes (post_id, user_id, created_at) VALUES ('$post_id', '$user_id', NOW())";
    mysqli_query($conn, $query);

    // Hitung jumlah likes baru
    $count_query = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = '$post_id'";
    $count_result = mysqli_query($conn, $count_query);
    $like_count = mysqli_fetch_assoc($count_result)['like_count'];

    echo json_encode(['success' => true, 'like_count' => $like_count]);
}
