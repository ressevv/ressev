<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5" style="max-width: 600px;">
        <div class="container-form">
            <h2 class="text-white mb-4">Вход в систему</h2>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Проверка администратора
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE login = ?");
                $stmt->execute([$_POST['login']]);
                $admin = $stmt->fetch();
                
                if ($admin && password_verify($_POST['password'], $admin['password'])) {
                    $_SESSION['admin'] = true;
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    header("Location: http://".$_SERVER['HTTP_HOST']."/admin.php");
                    exit;
                }

                
                // Проверка обычного пользователя
                $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
                $stmt->execute([$_POST['login']]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($_POST['password'], $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_login'] = $user['login'];
                    header("Location: orders.php");
                    exit;
                }
                
                // Если дошли сюда - ошибка авторизации
                echo '<div class="alert alert-danger">Неверный логин или пароль</div>';
            }
            ?>
            <form method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control" name="login" placeholder="Логин" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Пароль" required>
                </div>
                <button type="submit" class="btn btn-primary w-100" color="black">Войти</button>
            </form>
            <p class="mt-3 text-center text-white-50">
                Нет аккаунта? <a href="register.php" class="text-white">Зарегистрироваться</a>
            </p>
        </div>
    </div>
</body>
</html>