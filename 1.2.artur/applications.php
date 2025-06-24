<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';

// Обработка создания заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_request'])) {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $service = $_POST['service'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $payment = $_POST['payment'];
    
    // Валидация даты
    if (strtotime($date) < strtotime('today')) {
        $error = "Дата не может быть в прошлом";
    } else {
        $stmt = $pdo->prepare("INSERT INTO requests (user_id, address, phone, service, date, time, payment, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'новая')");
        $stmt->execute([$user_id, $address, $phone, $service, $date, $time, $payment]);
        header("Location: request_form.php?success=1");
        exit;
    }
}

// Получение заявок пользователя
$stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY date DESC, time DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заявки</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Заявки</h1>
        <p><a href="logout.php">Выйти</a></p>
        <p><a href="request_form.php">Новая заявка</a></p>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Заявка успешно создана!</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        
        <div class="requests-list">
            <?php if (empty($requests)): ?>
                <p>У вас пока нет заявок</p>
            <?php else: ?>
                <?php foreach ($requests as $request): ?>
                <div class="request-card">
                    <p><strong>Услуга:</strong> <?= htmlspecialchars($request['service']) ?></p>
                    <p><strong>Адрес:</strong> <?= htmlspecialchars($request['address']) ?></p>
                    <p><strong>Дата:</strong> <?= $request['date'] ?> <?= $request['time'] ?></p>
                    <p><strong>Статус:</strong> 
                        <span class="status-<?= $request['status'] ?>">
                            <?= $request['status'] ?>
                        </span>
                    </p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>