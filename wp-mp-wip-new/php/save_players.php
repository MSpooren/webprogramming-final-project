<?php
// php/save_players.php
$data = json_decode(file_get_contents('php://input'), true);

$sessionId = $data['sessionId'];
$playerId = $data['playerId'];
$filename = "../data/game_" . $sessionId . ".json";

if (!file_exists($filename)) {
    echo json_encode(["error" => "Game file not found."]);
    exit;
}

$gameState = json_decode(file_get_contents($filename), true);

$gameState["players"][$playerId]["name"] = $data['name'];
$gameState["players"][$playerId]["skin"] = $data['skin'];

file_put_contents($filename, json_encode($gameState, JSON_PRETTY_PRINT));
echo json_encode(["status" => "saved"]);
?>
