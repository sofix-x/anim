<?php
require_once 'connect.php'; // Используем общий файл подключения

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $conn уже определен в connect.php

    // Проверка соединения (уже есть в connect.php, но можно оставить для дополнительной проверки, если connect.php не делает die() при ошибке)
    // Однако, если connect.php уже делает die() при ошибке, эта проверка здесь избыточна.
    // Для чистоты, если connect.php надежно обрабатывает ошибки подключения, эту проверку можно убрать.
    // Предположим, connect.php не делает die(), поэтому оставим проверку.
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Получение данных из формы
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Проверка на пустые поля
    if (empty($username) || empty($password)) {
        echo "Пожалуйста, заполните все поля.";
        exit;
    }

    // Запрос к базе данных
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Проверка, найден ли пользователь
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Проверка пароля
        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $username; // Сохраняем имя пользователя в сессии

            // Проверка на админа
            if ($username === 'admin') {
                $_SESSION['is_admin'] = true; // Устанавливаем флаг для админа
            } else {
                $_SESSION['is_admin'] = false; // Устанавливаем флаг для обычного пользователя
            }

            header("Location: ../../index.php"); // Перенаправляем на главную страницу
            exit();
        } else {
            echo "Неверный логин или пароль.";
        }
    } else {
        echo "Пользователь не найден.";
    }

    // Закрытие соединения
    $stmt->close();
    $conn->close();
}
?>
