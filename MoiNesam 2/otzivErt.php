<?php
require 'db.php';
session_start();

// Изменение статуса
if (isset($_POST['approve'])) {
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE reviews SET status='approved' WHERE id=?")->execute([$id]);
}

// Удаление
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$id]);
}

// Получение всех отзывов
$reviews = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Модерация отзывов</title>
</head>
<body>
    <h1>Модерация отзывов</h1>
    <a href="index.php">На сайт</a> | <a href="logout.php">Выйти</a>
    
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Оценка</th>
            <th>Отзыв</th>
            <th>Дата</th>
        </tr>
        <?php foreach ($reviews as $review): ?>
        <tr>
            <td><?= $review['id'] ?></td>
            <td><?= htmlspecialchars($review['name']) ?></td>
            <td><?= $review['rating'] ?></td>
            <td><?= nl2br(htmlspecialchars($review['message'])) ?></td>
            <td><?= $review['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>