<?php
$data = json_decode(file_get_contents('php://input'), true);
$sessionId = $data['sessionId'] ?? null;
$playerId = $data['playerId'] ?? null;
$item = $data['item'] ?? null;

$filename = "../data/game_" . $sessionId . ".json";
if (!$sessionId || !$playerId || !$item || !file_exists($filename)) {
    echo json_encode(["error" => "Ongeldige request"]);
    exit;
}

$state = json_decode(file_get_contents($filename), true);
$player = &$state['players'][$playerId];
$opponentId = $playerId === "1" ? "2" : "1";
$opponent = &$state['players'][$opponentId];

// Check of speler dit item heeft
$index = array_search($item, $player['inventory']);
if ($index === false) {
    echo json_encode(["error" => "Item niet in inventory"]);
    exit;
}

// Verwijder item uit inventory
array_splice($player['inventory'], $index, 1);

// Zorg dat last_move bestaat
if (!isset($player['last_move']) || !$player['last_move']) {
    echo json_encode(["error" => "Geen vorige beweging bekend"]);
    exit;
}

// Pas de forced_direction van de tegenstander aan
$opponent['forced_direction'] = $player['last_move'];

file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));
echo json_encode(["success" => true]);
?>
