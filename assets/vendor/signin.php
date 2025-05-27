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
    $stmt = $mysqli->prepare("SELECT id, password, is_admin FROM users WHERE username = ?"); // Получаем также id и is_admin
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Проверка, найден ли пользователь
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $is_admin_db); // Связываем user_id и is_admin
        $stmt->fetch();

        // Проверка пароля
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id; // Сохраняем ID пользователя в сессии
            $_SESSION['username'] = $username; // Сохраняем имя пользователя в сессии
            $_SESSION['is_admin'] = (bool)$is_admin_db; // Устанавливаем флаг админа из БД

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
