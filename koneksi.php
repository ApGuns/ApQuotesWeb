<?php
$servername = "localhost";
$username = "root";
$password = "@pguns04";
$dbname = "ap_quotes";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Membuat tabel jika belum ada
$table_queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        role ENUM('user', 'admin') NOT NULL DEFAULT 'user'
    )",
    "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_post_user (post_id, user_id),
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

foreach ($table_queries as $query) {
    if (!$conn->query($query)) {
        die("Error creating table: " . $conn->error);
    }
}

// Periksa apakah admin sudah ada
$username = 'apadmin';
$email = 'apadmin@example.com';
$password_plain = 'apadmin'; // Password asli
$hashed_password = password_hash($password_plain, PASSWORD_BCRYPT);
$role = 'admin';

$check_user_query = "SELECT id FROM users WHERE username = ?";
$stmt_check = $conn->prepare($check_user_query);
if ($stmt_check) {
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        
    } else {
        // Tambahkan admin user
        $insert_user_query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_user_query);
        if ($stmt_insert) {
            $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $role);
            if ($stmt_insert->execute()) {
        
            } else {
                echo "Error: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            echo "Error preparing insert statement: " . $conn->error;
        }
    }
    $stmt_check->close();
} else {
    echo "Error preparing check statement: " . $conn->error;
}


?>
