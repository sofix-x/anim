<?php
session_start();
include '../../db_connection.php'; // Централизованное подключение

header('Content-Type: application/json');

// Проверка, что текущий пользователь является админом
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'error' => 'Доступ запрещен.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['username'])) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных (ID пользователя и имя пользователя обязательны).']);
    exit;
}

$user_id_to_edit = (int)$data['user_id'];
$new_username = trim($data['username']);
$new_password = isset($data['password']) ? trim($data['password']) : null;

if ($user_id_to_edit === 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID пользователя.']);
    exit;
}

if (empty($new_username)) {
    echo json_encode(['success' => false, 'error' => 'Имя пользователя не может быть пустым.']);
    exit;
}

// Нельзя редактировать основного админа (username 'admin') или себя таким образом, чтобы потерять доступ
// Проверка, не пытаемся ли мы изменить username 'admin' на что-то другое, или другого пользователя на 'admin' (если 'admin' уже существует)
$checkTargetUserStmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
$checkTargetUserStmt->bind_param("i", $user_id_to_edit);
$checkTargetUserStmt->execute();
$targetUserResult = $checkTargetUserStmt->get_result();
$targetUser = $targetUserResult->fetch_assoc();
$checkTargetUserStmt->close();

if (!$targetUser) {
    echo json_encode(['success' => false, 'error' => 'Редактируемый пользователь не найден.']);
    $mysqli->close();
    exit;
}

// Запрещаем менять имя пользователя 'admin'
if ($targetUser['username'] === 'admin' && $new_username !== 'admin') {
    echo json_encode(['success' => false, 'error' => "Имя пользователя 'admin' не может быть изменено."]);
    $mysqli->close();
    exit;
}

// Запрещаем назначать имя 'admin' другому пользователю, если 'admin' уже существует и это не тот же пользователь
if ($new_username === 'admin' && $targetUser['username'] !== 'admin') {
    $checkAdminExistsStmt = $mysqli->prepare("SELECT id FROM users WHERE username = 'admin'");
    $checkAdminExistsStmt->execute();
    if ($checkAdminExistsStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => "Имя пользователя 'admin' уже занято."]);
        $checkAdminExistsStmt->close();
        $mysqli->close();
        exit;
    }
    $checkAdminExistsStmt->close();
}


// Проверка, не занято ли новое имя пользователя (если оно меняется)
if ($new_username !== $targetUser['username']) {
    $checkUsernameStmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $checkUsernameStmt->bind_param("si", $new_username, $user_id_to_edit);
    $checkUsernameStmt->execute();
    if ($checkUsernameStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Это имя пользователя уже занято.']);
        $checkUsernameStmt->close();
        $mysqli->close();
        exit;
    }
    $checkUsernameStmt->close();
}


$sql_parts = [];
$params = [];
$types = "";

$sql_parts[] = "username = ?";
$params[] = $new_username;
$types .= "s";

if (!empty($new_password)) {
    if (strlen($new_password) < 1) { // Минимальная длина пароля, можно сделать строже
        echo json_encode(['success' => false, 'error' => 'Пароль слишком короткий.']);
        $mysqli->close();
        exit;
    }
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql_parts[] = "password = ?";
    $params[] = $hashed_password;
    $types .= "s";
}

if (empty($sql_parts)) {
    echo json_encode(['success' => true, 'message' => 'Нет данных для обновления.']); // Или ошибка, если это не ожидается
    $mysqli->close();
    exit;
}

$params[] = $user_id_to_edit;
$types .= "i";

$stmt = $mysqli->prepare("UPDATE users SET " . implode(", ", $sql_parts) . " WHERE id = ?");
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Если изменяли текущего админа и поменяли его username, нужно обновить сессию
    if ($_SESSION['user_id'] == $user_id_to_edit && $new_username !== $_SESSION['username']) {
        $_SESSION['username'] = $new_username;
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка обновления данных пользователя: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
