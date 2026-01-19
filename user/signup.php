<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_json_input();
    
    $name = $data['name'] ?? ''; 
    $email = $data['email'] ?? '';
    $password = password_hash($data['password'] ?? '', PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already registered"]);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        // "sss" means three strings
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "User created successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    }
}
?>