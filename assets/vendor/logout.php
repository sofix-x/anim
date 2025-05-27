<?php
session_start(); // Начинаем сессию

// Удаляем все сессионные переменные
$_SESSION = [];

// Уничтожаем сессию
session_destroy();

// Перенаправляем на главную страницу
header("Location: ../../index.php");
exit();
?>
