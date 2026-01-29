<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/db.php';

$email = $_GET['email'] ?? null;

if (!$email) {
    echo json_encode(["status" => "error", "message" => "Identity required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, title, content, created_at, updated_at FROM blogs WHERE user_email = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $blogs = [];
    while($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $blogs]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>