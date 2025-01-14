<?php
// Sambungkan ke database
include_once('komeksi.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dan sesi
    $quote = $_POST['quote'];
    $username = $_SESSION['username']; // Nama pengguna dari sesi login

    // Validasi sederhana
    if (empty($quote)) {
        die('Quote cannot be empty!');
    }

    // Query untuk menyimpan data ke dalam database
    $query = "INSERT INTO posts (quote, author, post_date) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $quote, $username);

    if ($stmt->execute()) {
        header('Location: ../index.php'); // Redirect ke halaman utama
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
