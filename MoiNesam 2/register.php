
<?php
session_start();
require 'db.php';


$errors = [];
$values = [
    'full_name' => '',
    'phone' => '',
    'email' => '',
    'login' => '',
    'password' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение значений из формы
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $login = trim($_POST['login']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Сохраняем для повторного отображения
    $values['full_name'] = htmlspecialchars($full_name);
    $values['phone'] = htmlspecialchars($phone);
    $values['email'] = htmlspecialchars($email);
    $values['login'] = htmlspecialchars($login);
    $values['password'] = ''; // пароль не выводится

    // Валидация ФИО: не пустое, допустим, содержит только буквы, пробелы и дефисы
    if (empty($full_name)) {
        $errors['full_name'] = "Пожалуйста, введите ваше полное имя.";
    } elseif (!preg_match("/^[а-яА-ЯёЁ\s\-]+$/u", $full_name)) {
        $errors['full_name'] = "Имя должно содержать только буквы, пробелы и дефисы.";
    }

    // Валидация номера телефона: должен содержать ровно 11 цифр, можем проверить только цифры
    $digits_only = preg_replace('/\D/', '', $phone);
    if (strlen($digits_only) !== 11) {
        $errors['phone'] = "Введите корректный номер телефона (11 цифр).";
    }

    // Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Некорректный формат email.";
    }

    // Валидация логина: не пустой, можно добавить правила по символам
    if (empty($login)) {
        $errors['login'] = "Пожалуйста, введите логин.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $login)) {
        $errors['login'] = "Логин должен содержать 3-20 символов: буквы, цифры, подчёркивание.";
    }

    // Валидация пароля: минимум 6 символов, можно расширить требования
    if (strlen($password) < 6) {
        $errors['password'] = "Пароль должен быть не менее 6 символов.";
    }

    // Если ошибок нет, можно обработать регистрацию (например, сохранить в базу)
    if (empty($errors)) {
        // Здесь ваш код сохранения данных
        // После успешной регистрации перенаправление или сообщение
        $stmt = $pdo->prepare("INSERT INTO users (full_name, phone, email, login, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $phone, $email, $login, $password]);
    header("Location: index.php");
    exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
    <script>
        
    </script>
</head>
<body>
<!-- Сам HTML с отображением ошибок -->
<div class="container">
    <h1>Регистрация</h1>
    <form method="POST" action="">
        <input type="text" name="full_name" placeholder="ФИО" required value="<?php echo $values['full_name']; ?>">
        <?php if(isset($errors['full_name'])): ?>
            <div style="color:red;"><?php echo $errors['full_name']; ?></div>
        <?php endif; ?>

        <input type="tel" name="phone" placeholder="+7 (999) 999-99-99" maxlength="11" minlength="11" required value="<?php echo $values['phone']; ?>">
        <?php if(isset($errors['phone'])): ?>
            <div style="color:red;"><?php echo $errors['phone']; ?></div>
        <?php endif; ?>

        <input type="email" name="email" placeholder="Email" required value="<?php echo $values['email']; ?>">
        <?php if(isset($errors['email'])): ?>
            <div style="color:red;"><?php echo $errors['email']; ?></div>
        <?php endif; ?>

        <input type="text" name="login" placeholder="Логин" required value="<?php echo $values['login']; ?>">
        <?php if(isset($errors['login'])): ?>
            <div style="color:red;"><?php echo $errors['login']; ?></div>
        <?php endif; ?>

        <input type="password" name="password" placeholder="Пароль" maxlength="16" minlength="6" required>
        <?php if(isset($errors['password'])): ?>
            <div style="color:red;"><?php echo $errors['password']; ?></div>
        <?php endif; ?>

        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт? <a href="index.php">Войти</a></p>
</div>
</body>
</html>