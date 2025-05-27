<?php session_start(); // Начало сессии ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Наши товары</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>
<body>
<style>
        
        main {
    display: flex;
    flex-direction: column;
    align-items: center; /* Центрирование по горизонтали */
    text-align: center; /* Центрирование текста */
}

.promo-image {
    width: 80%; /* Измените ширину изображения по желанию */
    max-width: 800px; /* Максимальная ширина изображения */
    height: auto;
    margin-top: 20px; /* Отступ сверху */
}

.promo-text {
    font-size: 24px;
    margin-top: 20px;
    color: #A020F0; /* Цвет текста */
    text-shadow: 0.5px 0.5px 0 black, -0.5px -0.5px 0 black, 0.5px -0.5px 0 black, -0.5px 0.5px 0 black;

}

    </style>
<header>
    <nav>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="tovars.php">Товары</a></li>
            <li><a href="abous_us.php">О нас</a></li>
            <li>
                <a href="#" onclick="toggleCart()">
                    <img src="img/card.png" alt="Корзина" class="cart-icon">
                </a>
            </li>
            <?php if (isset($_SESSION['username'])): ?>
                <?php if ($_SESSION['is_admin']): ?>
                    <li><a href="admin.php">Админ панель</a></li>
                <?php endif; ?>
                <li><a href="assets/vendor/logout.php">Выход</a></li>
            <?php else: ?>
                <li><a href="register.php">Зарегистрироваться</a></li>
                <li><a href="login.php">Войти</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>





<main>
    <h1 style="text-align: center;">Оформление заказа</h1>
    
    <a href="https://vk.com/sugoitoys" target="_blank">
        <img src="img/1.jpg" alt="Загляните в нашу группу в ВК" class="promo-image"> 
    </a>
    
    <p class="promo-text">Загляните в нашу группу в ВК, будем рады!</p>
    <div class="quantity-control">
    <button class="" onclick="group_vk()">Группа вк</button>
    </div>
</main>









<footer>
    <p>&copy; 2024 Интернет-каталог товаров</p>
</footer>

<script>
    function group_vk() {
    // Открытие страницы оформления заказа в новой вкладке
    window.open('https://vk.com/sugoitoys', '_blank'); // Замените 'checkout.php' на нужную ссылку
}
</script>
