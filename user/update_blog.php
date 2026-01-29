<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Suppress HTML errors to prevent the SyntaxError in Next.js
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/db.php';
require_once '../config/load_env.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';
$email = $data['email'] ?? '';

try {
    if (!$id || !$email) throw new Exception("Missing required fields: ID or Email.");

    // --- AI MODERATION SCAN ---
    $apiKey = getenv('GEMINI_API_KEY');
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    $prompt = "Analyze this F1 blog for toxicity. Return JSON only: { \"flagged\": bool, \"reason\": \"string\" }. Content: $title $content";
    $payload = ["contents" => [["parts" => [["text" => $prompt]]]]];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $resData = json_decode($response, true);
    // Clean the AI response of markdown triple backticks
    $cleanJson = preg_replace('/```(?:json)?|```/', '', $resData['candidates'][0]['content']['parts'][0]['text']);
    $verdict = json_decode(trim($cleanJson), true);

    if ($verdict['flagged']) {
        // Penalty: Issue a strike for attempting toxic edits
        $strikeStmt = $conn->prepare("UPDATE users SET strikes = strikes + 1 WHERE email = ?");
        $strikeStmt->bind_param("s", $email);
        $strikeStmt->execute();

        echo json_encode(["status" => "error", "message" => "Content blocked. Strike issued: " . $verdict['reason']]);
        exit;
    }

    // --- DATABASE UPDATE ---
    // This will trigger the 'updated_at' column automatically
    $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $content, $id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Post updated and verified."]);
    } else {
        throw new Exception("Database update failed: " . $conn->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>