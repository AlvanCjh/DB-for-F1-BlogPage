<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/db.php';

$email = $_GET['email'] ?? null;

if (!$email) {
    echo json_encode(["status" => "error", "message" => "Email required"]);
    exit;
}

// Fetch the 'strikes' column along with other details
$stmt = $conn->prepare("SELECT id, name, email, profile_pic, strikes FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo json_encode(["status" => "success", "data" => $user]);
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}
?>