<?php
session_start(); // Начинаем сессию

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключение к базе данных
    $conn = new mysqli('localhost', 'root', 'root', 'comsugoitoys');

    // Проверка соединения
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
