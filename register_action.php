<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi panjang password
    if (strlen($password) < 8) {
        echo "<script>
                alert('Password harus minimal 8 karakter.');
                window.location.href = 'register.php';
                </script>";
        exit;
    }

    // Periksa apakah username atau email sudah ada
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email); // 'ss' -> dua parameter tipe string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
                alert('Username atau email sudah terdaftar.');
                window.location.href = 'register.php';
                </script>";
    } else {
        // Tambahkan pengguna baru
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword); // 'sss' -> tiga parameter tipe string

        if ($stmt->execute()) {
            echo "<script>
                    alert('Akun berhasil dibuat. Silakan login.');
                    window.location.href = 'login.php';
                    </script>";
        } else {
            echo "<script>
                    alert('Gagal membuat akun. Silakan coba lagi.');
                    window.location.href = 'register.php';
                    </script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
