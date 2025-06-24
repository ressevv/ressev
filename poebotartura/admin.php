<?php 
include 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['order_id'])) {
    $status = $_POST['status'];
    $order_id = (int)$_POST['order_id'];

    try {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ?
            WHERE order_id = ?
        ");
        $stmt->execute([$status, $order_id]);
        
        if ($status === 'confirmed') {
            $stmt = $pdo->prepare("
                UPDATE equipment e
                JOIN orders o ON e.equipment_id = o.equipment_id
                SET e.available_quantity = e.available_quantity - 1
                WHERE o.order_id = ?
            ");
            $stmt->execute([$order_id]);
        }
        
        if ($status === 'cancelled') {
            $stmt = $pdo->prepare("
                UPDATE equipment e
                JOIN orders o ON e.equipment_id = o.equipment_id
                SET e.available_quantity = e.available_quantity + 1
                WHERE o.order_id = ? AND o.status = 'confirmed'
            ");
            $stmt->execute([$order_id]);
        }
    } catch (PDOException $e) {
        $_SESSION['admin_error'] = 'Ошибка при обновлении заказа: ' . $e->getMessage();
    }
}

$stmt = $pdo->query("
    SELECT o.*, u.full_name, u.phone, u.email, 
           e.name as equipment_name, p.address as pickup_address
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN equipment e ON o.equipment_id = e.equipment_id
    JOIN pickup_points p ON o.point_id = p.point_id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Админ-панель СпортGo</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-outline-danger">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="container-form">
            <h2 class="text-white mb-4">Все заказы</h2>
            
            <?php if (isset($_SESSION['admin_error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['admin_error'] ?></div>
                <?php unset($_SESSION['admin_error']); ?>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-hover table-dark">
                    <thead>
                        <tr>
                            <th>ФИО</th>
                            <th class="mobile-hidden">Контакты</th>
                            <th>Инвентарь</th>
                            <th class="mobile-hidden">Пункт выдачи</th>
                            <th>Период аренды</th>
                            <th class="mobile-hidden">Сумма</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['full_name']) ?></td>
                            <td class="mobile-hidden">
                                <?= htmlspecialchars($order['phone']) ?><br>
                                <?= htmlspecialchars($order['email']) ?>
                            </td>
                            <td><?= htmlspecialchars($order['equipment_name']) ?></td>
                            <td class="mobile-hidden"><?= htmlspecialchars($order['pickup_address']) ?></td>
                            <td>
                                <?= date('d.m.Y H:i', strtotime($order['start_time'])) ?> - 
                                <?= date('d.m.Y H:i', strtotime($order['end_time'])) ?>
                            </td>
                            <td class="mobile-hidden"><?= $order['total_price'] ?>₽</td>
                            <td>
                                <span class="badge <?= 
                                    $order['status'] === 'completed' ? 'bg-success' : 
                                    ($order['status'] === 'cancelled' ? 'bg-danger' : 
                                    ($order['status'] === 'confirmed' ? 'bg-info' : 'bg-warning')) ?>">
                                    <?= match($order['status']) {
                                        'new' => 'Новый',
                                        'confirmed' => 'Подтверждён',
                                        'completed' => 'Выполнен',
                                        'cancelled' => 'Отменён'
                                    } ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($order['status'] === 'new'): ?>
                                <div class="admin-actions">
                                    <form method="POST" class="mb-1">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <button type="submit" name="status" value="confirmed" class="btn btn-sm btn-info w-100">Подтвердить</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <button type="submit" name="status" value="cancelled" class="btn btn-sm btn-danger w-100">Отменить</button>
                                    </form>
                                </div>
                                <?php elseif ($order['status'] === 'confirmed'): ?>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <button type="submit" name="status" value="completed" class="btn btn-sm btn-success w-100">Завершить</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>