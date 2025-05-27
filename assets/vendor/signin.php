<?php
session_start(); // Начинаем сессию

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключение к базе данных
    include '../../db_connection.php'; // Используем централизованное подключение

    // Проверка соединения
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Получение данных из формы
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Проверка на пустые поля
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Пожалуйста, заполните все поля.";
        header("Location: ../../login.php");
        exit;
    }

    // Запрос к базе данных
    $stmt = $mysqli->prepare("SELECT password FROM users WHERE username = ?");
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
            $_SESSION['error_message'] = "Неверный логин или пароль. Пожалуйста, попробуйте еще раз.";
            header("Location: ../../login.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Пользователь с таким логином не найден. Пожалуйста, проверьте введенные данные или зарегистрируйтесь.";
        header("Location: ../../login.php");
        exit();
    }

    // Закрытие соединения (этот блок может не достигаться при редиректах выше, но оставим для полноты)
    $stmt->close();
    $mysqli->close();
}
?>
