
<?php 
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Получаем список инвентаря
$equipment = $pdo->query("SELECT * FROM equipment WHERE available_quantity > 0")->fetchAll();

// Получаем пункты выдачи
$pickup_points = $pdo->query("SELECT * FROM pickup_points")->fetchAll();

// Получаем заказы текущего пользователя
$stmt = $pdo->prepare("
    SELECT o.*, e.name as equipment_name, p.address as pickup_address
    FROM orders o
    JOIN equipment e ON o.equipment_id = e.equipment_id
    JOIN pickup_points p ON o.point_id = p.point_id
    WHERE o.user_id = :user_id
    ORDER BY o.created_at DESC
");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заказы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .mobile-hidden {
            display: table-cell;
        }
        @media (max-width: 768px) {
            .mobile-hidden {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">СпортGo</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-outline-danger">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="container-form mb-5">
            <h2 class="text-white mb-4">Новый заказ</h2>
            <?php if (!empty($_SESSION['order_errors'])): ?>
                <div class="alert alert-danger">
                    <?php foreach ($_SESSION['order_errors'] as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['order_errors']); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="create_order.php">
                <div class="mb-3">
                    <label class="form-label text-white">Инвентарь</label>
                    <select class="form-select" name="equipment_id" required>
                        <option value="" selected disabled>Выберите инвентарь</option>
                        <?php foreach ($equipment as $item): ?>
                            <option value="<?= $item['equipment_id'] ?>">
                                <?= htmlspecialchars($item['name']) ?> (<?= $item['price_per_hour'] ?>₽/час)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white">Пункт выдачи</label>
                    <select class="form-select" name="point_id" required>
                        <option value="" selected disabled>Выберите пункт выдачи</option>
                        <?php foreach ($pickup_points as $point): ?>
                            <option value="<?= $point['point_id'] ?>">
                                <?= htmlspecialchars($point['address']) ?> (<?= $point['working_hours'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-white">Дата и время начала</label>
                        <input type="datetime-local" class="form-control" name="start_time" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-white">Дата и время окончания</label>
                        <input type="datetime-local" class="form-control" name="end_time" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white">Тип оплаты</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" checked>
                            <label class="form-check-label text-white" for="cash" style="green";>Наличные</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="card">
                            <label class="form-check-label text-white" for="card">Банковская карта</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Создать заказ</button>
            </form>
        </div>

        <div class="container-form">
            <h3 class="text-white mb-4">Мои заказы</h3>
            <?php if (count($orders) > 0): ?> 
                <div class="table-responsive">
                    <table class="table table-hover table-dark">
                        <thead>
                            <tr>
                                <th>Инвентарь</th>
                                <th class="mobile-hidden">Пункт выдачи</th>
                                <th>Период аренды</th>
                                <th class="mobile-hidden">Сумма</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
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
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    У вас пока нет заказов
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>