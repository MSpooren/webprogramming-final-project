<?php
// php/save_state.php
$data = json_decode(file_get_contents('php://input'), true);
$gameState = json_decode(file_get_contents('../data/game_state.json'), true);

$playerId = $data['playerId'];
$move = $data['move']; // ['x' => 1, 'y' => 0] for example

$player = &$gameState["players"][$playerId];

if ($gameState["turn"] != $playerId) {
    echo json_encode(["error" => "Not your turn."]);
    exit;
}

// Apply move
$player['x'] += $move['x'];
$player['y'] += $move['y'];

// Update turn
$gameState["turn"] = $playerId == "1" ? 2 : 1;

file_put_contents('../data/game_state.json', json_encode($gameState, JSON_PRETTY_PRINT));
echo json_encode(["status" => "moved"]);
?>

