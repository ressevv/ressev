<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Проверяем, существует ли пользователь
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userExists = $stmt->fetch();

if (!$userExists) {
    $_SESSION['order_errors'] = ['Ошибка: пользователь не найден'];
    header("Location: orders.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = (int)($_POST['equipment_id'] ?? 0);
    $point_id = (int)($_POST['point_id'] ?? 0);
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cash';

    $errors = [];
    
    // Проверка инвентаря
    if ($equipment_id <= 0) $errors[] = 'Выберите инвентарь';
    
    // Проверка пункта выдачи
    if ($point_id <= 0) $errors[] = 'Выберите пункт выдачи';
    
    // Валидация дат
    try {
        $start_date = new DateTime($start_time);
        $end_date = new DateTime($end_time);
        $now = new DateTime();
        
        if ($start_date < $now) {
            $errors[] = 'Дата начала не может быть в прошлом';
        }
        
        if ($end_date <= $start_date) {
            $errors[] = 'Дата окончания должна быть позже даты начала';
        }
        
        // Проверка на слишком далекое будущее (например, больше 10 лет)
        $max_future_date = (new DateTime())->modify('+10 years');
        if ($start_date > $max_future_date || $end_date > $max_future_date) {
            $errors[] = 'Дата не может быть более чем на 10 лет вперед';
        }
        
    } catch (Exception $e) {
        $errors[] = 'Некорректный формат даты. Пожалуйста, введите реальную дату';
    }
    
    // Проверяем доступность инвентаря
    if ($equipment_id > 0 && empty($errors)) {
        $stmt = $pdo->prepare("SELECT price_per_hour, available_quantity FROM equipment WHERE equipment_id = ?");
        $stmt->execute([$equipment_id]);
        $equipment = $stmt->fetch();
        
        if (!$equipment) {
            $errors[] = 'Выбранный инвентарь не найден';
        } elseif ($equipment['available_quantity'] <= 0) {
            $errors[] = 'Выбранный инвентарь временно недоступен';
        }
    }

    if (empty($errors)) {
        try {
            // Рассчитываем стоимость аренды
            $interval = $start_date->diff($end_date);
            $hours = $interval->h + ($interval->days * 24);
            $total_price = $hours * $equipment['price_per_hour'];
            
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                    (user_id, equipment_id, point_id, start_time, end_time, total_price, payment_method) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $equipment_id,
                $point_id,
                $start_date->format('Y-m-d H:i:s'),
                $end_date->format('Y-m-d H:i:s'),
                $total_price,
                $payment_method
            ]);
        } catch (PDOException $e) {
            $_SESSION['order_errors'] = ['Ошибка при создании заказа: ' . $e->getMessage()];
        }
    } else {
        $_SESSION['order_errors'] = $errors;
    }
}

header("Location: orders.php");
exit;
?>