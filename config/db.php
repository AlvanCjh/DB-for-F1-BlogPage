<?php
// htdocs/api/config/db.php

// 1. Set Security & CORS Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

// Handle preflight "OPTIONS" requests (standard for APIs)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 2. Database Connection
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "my_website";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// 3. Helper function to get JSON input
function get_json_input() {
    return json_decode(file_get_contents("php://input"), true);
}
?>