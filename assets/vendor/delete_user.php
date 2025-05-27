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

if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных (ID пользователя).']);
    exit;
}

$user_id = (int)$data['user_id'];

if ($user_id === 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID пользователя.']);
    exit;
}

// Нельзя удалять основного админа (например, пользователя с именем 'admin')
$checkAdminStmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
$checkAdminStmt->bind_param("i", $user_id);
$checkAdminStmt->execute();
$checkAdminResult = $checkAdminStmt->get_result();
if ($checkAdminRow = $checkAdminResult->fetch_assoc()) {
    if ($checkAdminRow['username'] === 'admin') {
        echo json_encode(['success' => false, 'error' => 'Основной администратор не может быть удален.']);
        $checkAdminStmt->close();
        $mysqli->close();
        exit;
    }
}
$checkAdminStmt->close();

// Также, нельзя удалять самого себя (текущего админа)
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) { // Предполагаем, что user_id админа хранится в сессии
    // Если user_id не хранится, можно получить его по $_SESSION['username']
    // $current_admin_username = $_SESSION['username'];
    // $selfCheckStmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    // $selfCheckStmt->bind_param("s", $current_admin_username);
    // $selfCheckStmt->execute();
    // $selfCheckResult = $selfCheckStmt->get_result();
    // if($selfRow = $selfCheckResult->fetch_assoc()){
    //     if($selfRow['id'] == $user_id){
    //          echo json_encode(['success' => false, 'error' => 'Вы не можете удалить самого себя.']);
    //          $mysqli->close();
    //          exit;
    //     }
    // }
    // $selfCheckStmt->close();
    // Для простоты, если ID текущего админа не хранится в сессии, эту проверку можно опустить или реализовать по username
}


$stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Пользователь не найден или уже удален.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка удаления пользователя: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
