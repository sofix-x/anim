<?php
session_start(); // Начинаем сессию

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Подключение к базе данных
    include '../../db_connection.php'; // Используем централизованное подключение

    // Проверка соединения
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Получение данных из формы
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Проверка, чтобы пароли совпадали
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Пароли не совпадают! Пожалуйста, попробуйте еще раз.";
        header("Location: ../../register.php"); // Перенаправление на страницу регистрации
        exit(); // Прекращаем выполнение скрипта
    }

    // Хеширование пароля
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Вставка данных в базу
    $stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Регистрация успешна! Теперь вы можете войти.";
        // Закрытие соединения перед редиректом на главную
        $stmt->close();
        $mysqli->close();
        header("Location: ../../index.php"); // Перенаправление на главную страницу
        exit();
    } else {
        $_SESSION['error_message'] = "Ошибка регистрации: " . $stmt->error . " Пожалуйста, попробуйте еще раз.";
        // Закрытие соединения перед редиректом на регистрацию
        $stmt->close();
        $mysqli->close();
        header("Location: ../../register.php"); // Перенаправление на страницу регистрации в случае ошибки
        exit();
    }
}
?>
