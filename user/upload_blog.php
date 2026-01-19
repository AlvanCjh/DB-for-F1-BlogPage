<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $filename = null;

    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($file['tmp_name'], "uploads/" . $filename);
    }

    $stmt = $conn->prepare("INSERT INTO blogs (user_email, title, content, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $title, $content, $filename);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Blog posted!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
}
?>