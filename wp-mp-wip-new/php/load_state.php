<?php
$sessionId = isset($_GET['sessionId']) ? $_GET['sessionId'] : null;

if (!$sessionId) {
    echo json_encode(["error" => "Session ID missing."]);
    exit;
}

$filename = "../data/game_" . $sessionId . ".json";

header('Content-Type: application/json');

if (!file_exists($filename)) {
    echo json_encode(["error" => "Game state not found."]);
    exit;
}

$gameState = json_decode(file_get_contents($filename), true);
echo json_encode($gameState);
?>
