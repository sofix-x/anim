<?php
session_start();
$conn = new mysqli('localhost', 'root', 'root', 'comsugoitoys');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);
$category_ids = $data['category_ids'];

if (!empty($category_ids)) {
    $placeholders = implode(",", array_fill(0, count($category_ids), "?"));
    $stmt = $conn->prepare("DELETE FROM categories WHERE id IN ($placeholders)");
    $types = str_repeat("i", count($category_ids));
    $stmt->bind_param($types, ...$category_ids);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Нет выбранных категорий для удаления."]);
}

$conn->close();
?>
