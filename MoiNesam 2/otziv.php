<?php
require 'db.php';
session_start();

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $rating = (int)$_POST['rating'];
    $message = trim($_POST['message']);

    // Валидация
    $errors = [];
    if (empty($name)) $errors[] = 'Укажите имя';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    if ($rating < 1 || $rating > 5) $errors[] = 'Выберите оценку';
    if (strlen($message) < 10) $errors[] = 'Отзыв слишком короткий';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (name, email, rating, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $rating, $message]);
            $_SESSION['success'] = 'Отзыв отправлен на модерацию!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

// Получение одобренных отзывов
try {
    $stmt = $pdo->query("SELECT * FROM reviews WHERE status='approved' ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
    $error = 'Ошибка при загрузке отзывов';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отзывы о нашем сервисе</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Оставьте свой отзыв</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form class="review-form" method="POST">
        <label>Имя:</label><br>
        <input type="text" name="name" required maxlength="50"><br><br>
        
        <label>Email:</label><br>
        <input type="email" name="email" required maxlength="100"><br><br>
        
        <label>Оценка:</label><br>
        <select name="rating" required>
            <option value="">Выберите оценку</option>
            <option value="5">Отлично (5★)</option>
            <option value="4">Хорошо (4★)</option>
            <option value="3">Удовлетворительно (3★)</option>
            <option value="2">Плохо (2★)</option>
            <option value="1">Ужасно (1★)</option>
        </select><br><br>
        
        <label>Отзыв:</label><br>
        <textarea name="message" rows="5" required maxlength="1000"></textarea><br><br>
        
        <button type="submit">Отправить отзыв</button>
    </form>

    <h2>Отзывы наших клиентов</h2>
    
    <?php if (empty($reviews)): ?>
        <p>Пока нет отзывов. Будьте первым!</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review">
                <div class="stars">
                    <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                </div>
                <h3><?= htmlspecialchars($review['name']) ?></h3>
                <p><em><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></em></p>
                <p><?= nl2br(htmlspecialchars($review['message'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>