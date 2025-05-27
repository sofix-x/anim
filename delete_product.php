<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', 'root', 'comsugoitoys');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => $conn->connect_error]);
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
