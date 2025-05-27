<?php
session_start();
include 'db_connection.php'; // Используем централизованное подключение

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_POST['category_id']) && isset($_POST['category_name'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];

    $stmt = $mysqli->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $category_name, $category_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}
header("Location: admin.php");

$mysqli->close();
?>
