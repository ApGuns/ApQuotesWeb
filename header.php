<?php

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
// Status login
$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ap Quotes</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>
    <header class="site-header">
        <div class="header-left">
            <h1><a href="#" onclick="scrollToTop()" class="logo">Ap Quotes</a></h1>
        </div>
        <nav class="header-links desktop-only">
            <a href="index.php"><i class="fas fa-home"></i></a>
            <?php if ($is_logged_in) { ?>
                <a href="create_post.php">+ Create Post</a>
                <a><?= htmlspecialchars($username); ?></a>
            <?php } else { ?>
                <a href="login.php" onclick="redirectToLogin('+ Create Post')">+ Create Post</a>
                <a href="login.php" onclick="redirectToLogin('My Account')">My Account</a>
            <?php } ?>
        </nav>
        <div class="header-right">
            <button class="openbtn" onclick="openNav()">☰</button>
        </div>
    </header>

    <!-- Sidebar -->
    <div id="mySidepanel" class="sidepanel">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <?php if ($is_logged_in) { ?>
            <a href="create_post.php">+ Create Post</a>
            <a href="my_profile.php">My Profile</a>
            <a href="my_post.php">My Posts</a>
            <a href="javascript:void(0);" onclick="confirmLogout()">Logout</a>
        <?php } else { ?>
            <a href="login.php" onclick="redirectToLogin('+ Create Post')">+ Create Post</a>
            <a href="login.php" onclick="redirectToLogin('My Account')">My Account</a>
            <a href="login.php">Login</a>
        <?php } ?>
    </div>

    <script>
        function openNav() {
            document.getElementById("mySidepanel").classList.add("open");
        }

        function closeNav() {
            document.getElementById("mySidepanel").classList.remove("open");
        }

        function redirectToLogin(page) {
            alert(`Anda harus login terlebih dahulu untuk mengakses ${page}.`);
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function confirmLogout() {
            var confirmation = confirm("Apakah Anda yakin ingin logout?");
            if (confirmation) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>

</html>
