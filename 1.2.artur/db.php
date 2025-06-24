<?php
$host = 'MySQL-8.0';
$dbname = 'cleaning_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Создание таблиц при первом запуске
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        login VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        address TEXT NOT NULL,
        phone VARCHAR(20) NOT NULL,
        service VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        payment VARCHAR(50) NOT NULL,
        status VARCHAR(50) DEFAULT 'новая',
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>