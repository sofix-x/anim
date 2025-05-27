<?php
session_start();
include '../../db_connection.php'; // Централизованное подключение

header('Content-Type: application/json');

// Проверка, что пользователь является админом
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'error' => 'Доступ запрещен.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных.']);
    exit;
}

$user_id = (int)$data['user_id'];
$action = $data['action']; // 'make_admin' или 'remove_admin'

if ($user_id === 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID пользователя.']);
    exit;
}

// Нельзя изменять статус основного админа (если такой есть и его ID известен, например 1 или по имени 'admin')
// Для примера, предположим, что пользователь с именем 'admin' не должен изменяться
$checkAdminStmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
$checkAdminStmt->bind_param("i", $user_id);
$checkAdminStmt->execute();
$checkAdminResult = $checkAdminStmt->get_result();
if ($checkAdminRow = $checkAdminResult->fetch_assoc()) {
    if ($checkAdminRow['username'] === 'admin') {
        echo json_encode(['success' => false, 'error' => 'Статус основного администратора не может быть изменен.']);
        $checkAdminStmt->close();
        $mysqli->close();
        exit;
    }
}
$checkAdminStmt->close();


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
$stmt->bind_param("ii", $new_is_admin_status, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Статус пользователя не был изменен (возможно, он уже такой).']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка обновления статуса пользователя: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
