<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../config/load_env.php';

$data = json_decode(file_get_contents("php://input"), true);
$text = $data['text'] ?? '';
$blogId = $data['blog_id'] ?? null; 

if (empty($text)) {
    echo json_encode(["status" => "error", "message" => "No content provided"]);
    exit;
}

$apiKey = getenv('GEMINI_API_KEY'); 
// CORRECTED MODEL NAME
$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$prompt = "Analyze for toxicity. Return ONLY raw JSON: { \"flagged\": bool, \"category\": string, \"targets\": string, \"evidence\": string, \"reason\": string } Content: " . $text;

$payload = ["contents" => [["parts" => [["text" => $prompt]]]]];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$resData = json_decode($response, true);

if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
    $rawVerdict = $resData['candidates'][0]['content']['parts'][0]['text'];
    $cleanVerdict = preg_replace('/```(?:json)?|```/', '', $rawVerdict);
    $verdictArray = json_decode(trim($cleanVerdict), true);

    // If the content is safe, update the timestamp to remove the badge
    if ($verdictArray && isset($verdictArray['flagged']) && $verdictArray['flagged'] === false && $blogId) {
        try {
            $stmt = $conn->prepare("UPDATE blogs SET last_scan_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->bind_param("i", $blogId);
            $stmt->execute();
        } catch (Exception $e) {
            // Log locally but keep the AI response flowing
        }
    }
    echo trim($cleanVerdict);
} else {
    // Return specific Google error message if available
    http_response_code(500);
    $errorMsg = $resData['error']['message'] ?? 'AI Service Error or Invalid API Key';
    echo json_encode(["status" => "error", "message" => $errorMsg]);
}
?>