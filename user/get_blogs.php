<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $sql = "SELECT blogs.*, users.name as author_name 
            FROM blogs 
            JOIN users ON blogs.user_email = users.email 
            ORDER BY blogs.created_at DESC";
    
    $result = $conn->query($sql);
    $blogs = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $blogs]);
    } else {
        echo json_encode(["status" => "success", "data" => []]);
    }
}
?>