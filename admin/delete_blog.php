<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$blogId = $data['id'] ?? null;

if (!$blogId) {
    echo json_encode(["status" => "error", "message" => "No ID provided"]);
    exit;
}

$stmt = $conn->prepare("SELECT image_path FROM blogs WHERE id = ?");
$stmt->bind_param("i", $blogId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $filePath = "../user/uploads/" . $row['image_path'];
    if (file_exists($filePath)) {
        unlink($filePath); // Delete image file
    }
}

$delStmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
$delStmt->bind_param("i", $blogId);

if ($delStmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Post deleted"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error"]);
}
?>