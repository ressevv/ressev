<?php
session_start();
require '../db.php';

if (!isset($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit;
}

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->execute([$status, $request_id]);
    header("Location: admin.php?updated=" . $request_id);
    exit;
}

// Получение всех заявок
$stmt = $pdo->query("SELECT r.*, u.full_name, u.email, u.phone as user_phone 
                     FROM requests r 
                     JOIN users u ON r.user_id = u.id
                     ORDER BY r.date DESC, r.time DESC");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Панель администратора</h1>
        <p><a href="../logout.php">Выйти</a></p>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="success">Статус заявки #<?= $_GET['updated'] ?> обновлен!</div>
        <?php endif; ?>

        <div class="requests-list">
            <?php if (empty($requests)): ?>
                <p>Нет активных заявок</p>
            <?php else: ?>
                <?php foreach ($requests as $request): ?>
                <div class="request-card">
                    <h3>Заявка #<?= $request['id'] ?></h3>
                    <p><strong>Клиент:</strong> <?= htmlspecialchars($request['full_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($request['email']) ?></p>
                    <p><strong>Телефон:</strong> <?= htmlspecialchars($request['user_phone']) ?></p>
                    <p><strong>Услуга:</strong> <?= htmlspecialchars($request['service']) ?></p>
                    <p><strong>Адрес:</strong> <?= htmlspecialchars($request['address']) ?></p>
                    <p><strong>Дата:</strong> <?= $request['date'] ?> <?= $request['time'] ?></p>
                    <p><strong>Оплата:</strong> <?= $request['payment'] ?></p>
                    <p><strong>Статус:</strong> 
                        <span class="status-<?= $request['status'] ?>">
                            <?= $request['status'] ?>
                        </span>
                    </p>
                    
                    <form method="POST" class="status-form">
                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                        <select name="status">
                            <option value="новая" <?= $request['status'] == 'новая' ? 'selected' : '' ?>>Новая</option>
                            <option value="подтверждена" <?= $request['status'] == 'подтверждена' ? 'selected' : '' ?>>Подтверждена</option>
                            <option value="выполнена" <?= $request['status'] == 'выполнена' ? 'selected' : '' ?>>Выполнена</option>
                            <option value="отменена" <?= $request['status'] == 'отменена' ? 'selected' : '' ?>>Отменена</option>
                        </select>
                        <button type="submit" name="update_status">Обновить статус</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>