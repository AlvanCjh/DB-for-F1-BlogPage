<?php
require_once '../config/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = get_json_input();
    
    $email = $data['email'] ?? '';
    $newName = $data['newName'] ?? '';

    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE email = ?");
    $stmt->bind_param("ss", $newName, $email);
    
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success", 
            "message" => "Profile updated successfully!"
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Failed to update profile in database."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Invalid request method."
    ]);
}
?>