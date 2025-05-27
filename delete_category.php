<?php
session_start();
include 'db_connection.php'; // Используем централизованное подключение

$data = json_decode(file_get_contents("php://input"), true);
$category_ids = $data['category_ids'];

if (!empty($category_ids)) {
    $placeholders = implode(",", array_fill(0, count($category_ids), "?"));
    $stmt = $mysqli->prepare("DELETE FROM categories WHERE id IN ($placeholders)");
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

$mysqli->close();
?>
