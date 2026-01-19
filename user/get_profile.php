<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $email = $_GET['email'] ?? '';

    $stmt = $conn->prepare("SELECT name, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        echo json_encode(["status" => "success", "data" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }
}
?>