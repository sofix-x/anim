<?php
header('Content-Type: application/json');
include 'db_connection.php'; // Используем централизованное подключение

if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'error' => $mysqli->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['product_ids'])) {
    $ids = implode(',', array_map('intval', $data['product_ids']));
    $query = "DELETE FROM products WHERE id IN ($ids)";
    if ($mysqli->query($query) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $mysqli->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No product IDs provided']);
}

$mysqli->close();
?>
