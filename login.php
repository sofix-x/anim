<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="assets/css/log-reg.css">
</head>
<body>

<header>
    <nav>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="register.php">Зарегистрироваться</a></li>
        </ul>
    </nav>
</header>

<main>
<div class="form-section">
    <h2>Вход</h2>
    <form action="assets/vendor/signin.php" method="post">
    <div class="form-group">
        <label for="username">Логин:</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="submit-button">Войти</button>
    </form>
</div>

</main>

<footer>
    <p>&copy; 2024 Интернет-каталог товаров</p>
</footer>

</body>
</html>
