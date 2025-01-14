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

// Ambil ID pengguna dari session
$user_id = $_SESSION['user_id'];

// Ambil data pengguna
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($username, $email);
$stmt->fetch();

// Inisialisasi variabel untuk pesan kesalahan atau sukses
$error_message = "";
$success_message = "";

// Proses formulir jika ada pengeditan profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Proses update profil
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);

        if (empty($new_username) || empty($new_email)) {
            $error_message = "Semua kolom harus diisi.";
        } else {
            // Update data pengguna di database
            $update_query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bind_param("ssi", $new_username, $new_email, $user_id);

            if ($stmt_update->execute()) {
                $success_message = "Profil berhasil diperbarui!";
                // Memperbarui session username setelah berhasil memperbarui profil
                $_SESSION['username'] = $new_username;
            } else {
                $error_message = "Terjadi kesalahan saat memperbarui profil.";
            }
        }
    } elseif (isset($_POST['update_password'])) {
        // Proses update password
        $old_password = trim($_POST['old_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "Semua kolom password harus diisi.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Password baru dan konfirmasi password tidak cocok.";
        } else {
            // Cek apakah password lama benar
            $query_password = "SELECT password FROM users WHERE id = ?";
            $stmt_password = $conn->prepare($query_password);
            $stmt_password->bind_param("i", $user_id);
            $stmt_password->execute();
            $stmt_password->store_result();
            $stmt_password->bind_result($hashed_password);
            $stmt_password->fetch();

            if (password_verify($old_password, $hashed_password)) {
                // Hash password baru dan update
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password_query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt_update_password = $conn->prepare($update_password_query);
                $stmt_update_password->bind_param("si", $hashed_new_password, $user_id);

                if ($stmt_update_password->execute()) {
                    $success_message = "Password berhasil diperbarui!";
                } else {
                    $error_message = "Terjadi kesalahan saat memperbarui password.";
                }
            } else {
                $error_message = "Password lama tidak valid.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="edit-profile-style.css">
</head>
<body>
<?php include_once('header.php'); ?>

<main>
    <div class="profile-card">
        <div class="profile-header">
            <h2>Edit Profile</h2>
        </div>
        <?php if (!empty($error_message)) { ?>
            <p class="error"><?= htmlspecialchars($error_message); ?></p>
        <?php } ?>
        <?php if (!empty($success_message)) { ?>
            <p class="success"><?= htmlspecialchars($success_message); ?></p>
        <?php } ?>

        <!-- Edit Profile Form -->
        <form action="edit-profile.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email); ?>" required>
            </div>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <!-- Change Password Form -->
        <div class="divider"></div>
        <h3>Change Password</h3>
        <form action="edit-profile.php" method="POST">
            <div class="form-group">
                <label for="old_password">Old Password:</label>
                <input type="password" name="old_password" id="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit" name="update_password">Change Password</button>
        </form>
    </div>
</main>

<?php include_once('footer.php'); ?>

</body>
</html>
