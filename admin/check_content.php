<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$text = $data['text'] ?? '';

if (empty($text)) {
    echo json_encode(["status" => "error", "message" => "No content provided"]);
    exit;
}

$apiKey = 'AIzaSyAbGI0alqeZL2Bkn_kZG9hn_mk2_nFmdc4'; 
// Note: Changed to v1 endpoint for stability and corrected model name
$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$prompt = "You are a strict moderator for an F1 fan community. Analyze this post for toxicity.
Respond ONLY in JSON format with these exact keys:
{
  \"flagged\": true/false,
  \"category\": \"(e.g., Toxic Negativity, Harassment, or None)\",
  \"targets\": \"(Who is being insulted?)\",
  \"evidence\": \"(Short snippet of the worst part)\",
  \"reason\": \"(1 sentence explanation)\"
}
Content: " . $text;

$payload = [
    "contents" => [["parts" => [["text" => $prompt]]]]
];

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
    // Strip markdown backticks that Gemini often adds
    $verdict = trim(str_replace(['```json', '```'], '', $verdict));
    echo $verdict;
} else {
    echo json_encode(["status" => "error", "message" => "AI Service Error"]);
}
?>