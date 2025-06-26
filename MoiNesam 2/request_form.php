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
    <title>Новая заявка</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Новая заявка</h1>
        <p><a href="applications.php" style="color: white;">Все заявки</a></p>
        <p><a href="logout.php" style="color: white;">Выйти</a></p>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Заявка успешно создана!</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <div class="request-form">
            <form method="POST">
                <input type="text" name="address" placeholder="Адрес" required>
                <input type="tel" name="phone" placeholder="Телефон" maxlength="11" minlength="11" required>
                <select id="serviceSelect">
                <option value="">-- Выберите блюдо --</option>
                <option value="Общий клининг">Общий клининг</option>
                <option value="Генеральная уборка">Генеральная уборка</option>
                <option value="Послестроительная уборка">Послестроительная уборка</option>
                <option value="Химчистка">Химчистка ковров и мебели</option>
                <option value="Иная услуга">Иная услуга</option>
            </select>

            <!-- Поле для ввода, появляется при выборе "Иная услуга" -->
            <div id="customServiceContainer" style="margin-top: 10px; display: none;">
                <input type="text" name="service" placeholder="Укажите вашу услугу" />
            </div>

            <script>
            // Получаем элементы
            const selectElement = document.getElementById('serviceSelect');
            const customServiceDiv = document.getElementById('customServiceContainer');

            // Обработка изменения выбранного варианта
            selectElement.addEventListener('change', function() {
                if (this.value === 'Иная услуга') {
                customServiceDiv.style.display = 'block';
                } else {
                customServiceDiv.style.display = 'none';
                }
            });
            </script>
                   
                <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>
                <input type="time" name="time" required>
                <select name="payment" required>
                    <option value="Наличные">Наличные</option>
                    <option value="Карта">Банковская карта</option>
                </select>
                <button type="submit" name="create_request">Создать заявку</button>
            </form>
        </div>
        
        <div class="requests-list">
            <h2>История заявок</h2>
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