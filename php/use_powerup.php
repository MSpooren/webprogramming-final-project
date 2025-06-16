<?php
$data = json_decode(file_get_contents('php://input'), true);
$sessionId = $data['sessionId'] ?? null;
$playerId = $data['playerId'] ?? null;
$item = $data['item'] ?? null;

$filename = "../data/game_" . $sessionId . ".json";
if (!file_exists($filename)) {
    echo json_encode(["error" => "Game file not found"]);
    exit;
}

$state = json_decode(file_get_contents($filename), true);
$player = &$state['players'][$playerId];
$opponentId = $playerId === "1" ? "2" : "1";
$opponent = &$state['players'][$opponentId];

if (!in_array($item, $player['inventory'])) {
    echo json_encode(["error" => "Item not in inventory"]);
    exit;
}

if ($item === "laserpointer") {
    $direction = $player['last_move'] ?? null;
    if (!$direction) {
        echo json_encode(["error" => "No previous move direction found"]);
        exit;
    }

    $dx = 0;
    $dy = 0;
    switch ($direction) {
        case "up": $dy = -1; break;
        case "down": $dy = 1; break;
        case "left": $dx = -1; break;
        case "right": $dx = 1; break;
    }

    $newX = $opponent['x'] + $dx;
    $newY = $opponent['y'] + $dy;
    $newX = max(0, min(6, $newX));
    $newY = max(0, min(6, $newY));
    $opponent['x'] = $newX;
    $opponent['y'] = $newY;

    $opponent['mirror_move'] = [
        'dx' => $dx,
        'dy' => $dy
    ];

    $player['inventory'] = array_values(array_filter($player['inventory'], fn($i) => $i !== "laserpointer"));

    file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode(["success" => true]);
    exit;
}

elseif ($item === "wool") {
    $direction = $data['direction'] ?? null;

    if (!$direction || !isset($direction['x']) || !isset($direction['y'])) {
        echo json_encode(["error" => "Ongeldige richting"]);
        exit;
    }

    $dx = $direction['x'];
    $dy = $direction['y'];

    if (!((abs($dx) === 3 && $dy === 0) || (abs($dy) === 3 && $dx === 0))) {
        echo json_encode(["error" => "Kattenrol moet 3 vakjes in één richting zijn"]);
        exit;
    }

    $newX = $player['x'] + $dx;
    $newY = $player['y'] + $dy;

    if ($newX < 0 || $newX > 6 || $newY < 0 || $newY > 6) {
        echo json_encode(["error" => "Buiten het speelveld"]);
        exit;
    }

    $player['x'] = $newX;
    $player['y'] = $newY;

    $player['inventory'] = array_values(array_filter($player['inventory'], fn($i) => $i !== "wool"));
    $player['movesThisTurn'] = ($player['movesThisTurn'] ?? 0) + 1;

    if ($player['movesThisTurn'] >= 2) {
        $state['turn'] = ($playerId === "1") ? "2" : "1";
        $state['players']["1"]['movesThisTurn'] = 0;
        $state['players']["2"]['movesThisTurn'] = 0;
    }

    file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["error" => "Unknown item"]);