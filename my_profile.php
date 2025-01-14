<?php
session_start();
require_once('koneksi.php');

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil data user berdasarkan session
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($username, $email, $created_at);
$stmt->fetch();

// Ambil jumlah postingan user
$query_posts = "SELECT COUNT(*) FROM posts WHERE user_id = ?";
$stmt_posts = $conn->prepare($query_posts);
$stmt_posts->bind_param("i", $user_id);
$stmt_posts->execute();
$stmt_posts->store_result();
$stmt_posts->bind_result($total_posts);
$stmt_posts->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="my-profile-style.css">
</head>

<body>
    <?php include_once('header.php'); ?>

    <main>
        <div class="profile-card">
            <div class="profile-header">
                <h2>My Profile</h2>
            </div>
            <table class="profile-table">
                <tr>
                    <td class="label">Username:</td>
                    <td class="value"><?= htmlspecialchars($username); ?></td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td class="value"><?= htmlspecialchars($email); ?></td>
                </tr>
                <tr>
                    <td class="label">Total Posts:</td>
                    <td class="value"><?= $total_posts; ?></td>
                </tr>
                <tr>
                    <td class="label">Account Created:</td>
                    <td class="value"><?= date('Y-m-d', strtotime($created_at)); ?></td>
                </tr>

            </table>
            <div class="profile-footer">
                <a href="edit-profile.php" class="edit-btn">Edit Profile</a>
            </div>
        </div>
    </main>

    <?php include_once('footer.php'); ?>

</body>

</html>