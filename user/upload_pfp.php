<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $filename = null;

    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "pfp_" . time() . "_" . uniqid() . "." . $ext;

        if (move_uploaded_file($file['tmp_name'], "pfp/" . $filename)) {

            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE email = ?");
            $stmt->bind_param("ss", $filename, $email);
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "image_path" => $filename]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database update failed"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save file"]);
        }
    }
}
?>