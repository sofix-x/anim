<?php
// Подключение к базе данных
$db_host = 'localhost';
$db_name = 'second_site_db'; // Ваше имя БД
$db_user = 'second_site_user'; // Ваш пользователь БД
$db_pass = '1'; // Ваш пароль

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Проверка на ошибки подключения
if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}
?>
