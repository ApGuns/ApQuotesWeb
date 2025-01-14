<?php
session_start();
require_once('koneksi.php');

// Validasi apakah form telah dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    // Ambil input dari form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input kosong
    if (!empty($username) && !empty($password)) {
        // Query untuk mencocokkan username
        $query = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Login berhasil, simpan data di session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time(); // Catat waktu login terakhir

                // Buat cookie untuk menyimpan status login (15 menit)
                $expiry = time() + (15 * 60); // 15 menit
                setcookie('user_id', $user['id'], $expiry, "/");
                setcookie('username', $user['username'], $expiry, "/");

                // Redirect ke index.php setelah login
                header("Location: index.php");
                exit();
            }
        }
        // Jika username atau password salah
        header("Location: login.php?error=" . urlencode("Username atau Password salah."));
        exit();
    } else {
        // Input kosong
        header("Location: login.php?error=" . urlencode("Username dan Password harus diisi."));
        exit();
    }
} else {
    // Form tidak valid
    header("Location: login.php?error=" . urlencode("Form login tidak valid."));
    exit();
}
?>
