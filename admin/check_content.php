<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once '../config/load_env.php';

$data = json_decode(file_get_contents("php://input"), true);
$text = $data['text'] ?? '';

if (empty($text)) {
    echo json_encode(["status" => "error", "message" => "No content provided"]);
    exit;
}

$apiKey = getenv('GEMINI_API_KEY'); 
// FIX: Change 'gemini-2.5-flash' to 'gemini-1.5-flash'
$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$prompt = "You are a strict moderator for an F1 community. Analyze for toxicity. 
Return ONLY raw JSON with these exact keys: 
{
  \"flagged\": true/false, 
  \"category\": \"Toxic/Harassment/None\", 
  \"targets\": \"Who is attacked?/None\", 
  \"evidence\": \"(CRITICAL: Keep this snippet UNDER 15 words)\", 
  \"reason\": \"(If flagged: why. If clean: why it is safe. Keep it to 1 sentence)\"
}
Content: " . $text;

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
    $verdict = $resData['candidates'][0]['content']['parts'][0]['text'];
    $verdict = preg_replace('/```(?:json)?|```/', '', $verdict);
    echo trim($verdict);
} else {
    // If Google returns an error, this will help you debug
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $resData['error']['message'] ?? 'AI Service Error']);
}
?>