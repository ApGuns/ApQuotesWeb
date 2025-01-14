<?php
// Mulai sesi
session_start();

// Cek apakah pengguna sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Ambil pesan error dari query string jika ada
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Login</h2>

        <!-- Tampilkan pesan error jika ada -->
        <?php if (!empty($error_message)) : ?>
            <div class="error-message">
                <p><?= $error_message; ?></p>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form method="POST" action="login_action.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <div class="toggle">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>

    <!-- Styling untuk error message -->
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</body>

</html>
