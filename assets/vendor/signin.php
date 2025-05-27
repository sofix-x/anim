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
    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE username = ?"); // Получаем только id и password
    if ($stmt === false) {
        // Залогировать $mysqli->error для отладки на сервере
        $_SESSION['error_message'] = "Произошла ошибка на сервере. Пожалуйста, попробуйте войти позже.";
        $mysqli->close();
        header("Location: ../../login.php");
        exit;
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Проверка, найден ли пользователь
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password); // Связываем user_id и hashed_password
        $stmt->fetch();

        // Проверка пароля
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id; // Сохраняем ID пользователя в сессии
            $_SESSION['username'] = $username; // Сохраняем имя пользователя в сессии
            
            // Устанавливаем флаг админа на основе имени пользователя
            if ($username === 'admin') {
                $_SESSION['is_admin'] = true;
            } else {
                $_SESSION['is_admin'] = false;
            }
            
            $stmt->close();
            $mysqli->close();
            header("Location: ../../index.php"); // Перенаправляем на главную страницу
            exit();
        } else {
            $_SESSION['error_message'] = "Неверный логин или пароль. Пожалуйста, попробуйте еще раз.";
            $stmt->close();
            $mysqli->close();
            header("Location: ../../login.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Пользователь с таким логином не найден. Пожалуйста, проверьте введенные данные или зарегистрируйтесь.";
        $stmt->close(); // Закрываем стейтмент, даже если пользователь не найден
        $mysqli->close();
        header("Location: ../../login.php");
        exit();
    }

    // Этот блок не должен достигаться, если все ветки выше имеют exit()
    // Если стейтмент или соединение не были закрыты выше, закрываем здесь.
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}
?>
