<?php
require_once 'assets/vendor/connect.php'; // Используем общий файл подключения
header('Content-Type: application/json'); // Устанавливаем заголовок после подключения

// $conn уже определен в connect.php
if ($conn->connect_error) {
    // Эта проверка может быть избыточной, если connect.php уже обрабатывает ошибки подключения
    echo json_encode(['success' => false, 'error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['product_ids'])) {
    $ids = implode(',', array_map('intval', $data['product_ids']));
    $query = "DELETE FROM products WHERE id IN ($ids)";
    if ($conn->query($query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No product IDs provided']);
}

$conn->close();
?>
