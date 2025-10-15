<?php
header('Content-Type: application/json');

$apiKey = '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';

if (!$apiKey) {
    echo json_encode(['reply' => 'Sorry, the API key is not set.']);
    exit;
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!isset($input['message'])) {
    echo json_encode(['reply' => 'Sorry, no message received.']);
    exit;
}

$userMessage = $input['message'];

$data = [
    "model" => "gpt-4o",
    "messages" => [
        ["role" => "system", "content" => "You are a chatbot assistant for PrimeBuild Construction Company. Answer questions about the company's services, location, contact, and portfolio."],
        ["role" => "user", "content" => $userMessage]
    ]
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['reply' => "Sorry, something went wrong."]);
    exit;
}

$responseData = json_decode($response, true);

if (isset($responseData['choices'][0]['message']['content'])) {
    echo json_encode(['reply' => $responseData['choices'][0]['message']['content']]);
} else {
    echo json_encode(['reply' => "Sorry, I couldn't process that."]);
}
?>
