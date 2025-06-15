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

    // Beweging bepalen
    $dx = 0;
    $dy = 0;
    switch ($direction) {
        case "up": $dy = -1; break;
        case "down": $dy = 1; break;
        case "left": $dx = -1; break;
        case "right": $dx = 1; break;
    }

    // Nieuwe coördinaten bepalen
    $newX = $opponent['x'] + $dx;
    $newY = $opponent['y'] + $dy;

    // Zorgen dat hij binnen het speelveld blijft (optioneel)
    $newX = max(0, min(6, $newX)); // grid van 7x7
    $newY = max(0, min(6, $newY));

    $opponent['x'] = $newX;
    $opponent['y'] = $newY;

    // Verwijder item uit inventory
    $player['inventory'] = array_filter($player['inventory'], function ($i) {
        return $i !== "laserpointer";
    });

    file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["error" => "Unknown item"]);
