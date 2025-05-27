<?php
session_start(); // Начинаем сессию

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Подключение к базе данных
    $conn = new mysqli('localhost', 'root', 'root', 'comsugoitoys');

    // Проверка соединения
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Получение данных из формы
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Проверка, чтобы пароли совпадали
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Пароли не совпадают!";
        header("Location: ../../login.php"); // Перенаправление на страницу регистрации
        exit();
    }

    // Хеширование пароля
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Вставка данных в базу
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Регистрация успешна!"; // Сохраните сообщение в сессии
    } else {
        $_SESSION['error_message'] = "Ошибка: " . $stmt->error; // Сохраните ошибку в сессии
    }

    // Закрытие соединения
    $stmt->close();
    $conn->close();

    // Перенаправление на главную страницу
    header("Location: ../../index.php");
    exit();
}
?>
