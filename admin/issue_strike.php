<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['id'] ?? null;

if (!$userId) {
    echo json_encode(["status" => "error", "message" => "No User ID provided"]);
    exit;
}

// Increment strikes by 1
$stmt = $conn->prepare("UPDATE users SET strikes = strikes + 1 WHERE id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Strike issued"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>