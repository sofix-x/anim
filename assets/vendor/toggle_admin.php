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

if (!isset($data['user_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных.']);
    exit;
}

$user_id_to_toggle = (int)$data['user_id'];
$action = $data['action']; // 'make_admin' или 'remove_admin'

if ($user_id_to_toggle === 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID пользователя.']);
    exit;
}

// Защита от изменения статуса основного админа (например, пользователя с ID 1 или username 'admin')
// Также нельзя изменять свой собственный статус через этот интерфейс
$current_user_id = $_SESSION['user_id'];

if ($user_id_to_toggle == $current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Вы не можете изменить свой собственный статус администратора.']);
    $mysqli->close();
    exit;
}

// Дополнительная проверка для пользователя 'admin', если он имеет фиксированную роль
$checkTargetUserStmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
$checkTargetUserStmt->bind_param("i", $user_id_to_toggle);
$checkTargetUserStmt->execute();
$targetUserResult = $checkTargetUserStmt->get_result();
if ($targetUserRow = $targetUserResult->fetch_assoc()) {
    if ($targetUserRow['username'] === 'admin') {
        echo json_encode(['success' => false, 'error' => 'Статус основного администратора (username: admin) не может быть изменен.']);
        $checkTargetUserStmt->close();
        $mysqli->close();
        exit;
    }
}
$checkTargetUserStmt->close();


$new_is_admin_status = 0;
if ($action === 'make_admin') {
    $new_is_admin_status = 1;
} elseif ($action === 'remove_admin') {
    $new_is_admin_status = 0;
} else {
    echo json_encode(['success' => false, 'error' => 'Неверное действие.']);
    $mysqli->close();
    exit;
}

$stmt = $mysqli->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
$stmt->bind_param("ii", $new_is_admin_status, $user_id_to_toggle);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        // Это может произойти, если статус уже был таким, или пользователь не найден (хотя ID должен быть валидным)
        echo json_encode(['success' => false, 'error' => 'Статус пользователя не был изменен (возможно, он уже такой или пользователь не найден).']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка обновления статуса пользователя: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
