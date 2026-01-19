<?php
// htdocs/api/user/update_profile.php
require_once '../config/db.php'; // Link to your centralized database config

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Using the helper function we created in db.php
    $data = get_json_input();
    
    $email = $data['email'] ?? '';
    $newName = $data['newName'] ?? '';

    // Prepare the SQL to prevent SQL injection
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