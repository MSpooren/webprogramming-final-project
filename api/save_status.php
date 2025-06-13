<?php
header("Content-Type: application/json");

$dataFile = '../data/game_state.json';

$incoming = json_decode(file_get_contents("php://input"), true);
$state = json_decode(file_get_contents($dataFile), true);
$timeNow = time();

function save_state($state, $file) {
    file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode(["success" => true]);
    exit;
}

if ($incoming['action'] === "set_name") {
    $player = $incoming['player'];
    $name = $incoming['name'];
    $state['players'][$player]['name'] = $name;
    save_state($state, $dataFile);
}

if ($incoming['action'] === "set_skin") {
    $player = $incoming['player'];
    $skin = $incoming['skin'];
    $state['players'][$player]['skin'] = $skin;
    save_state($state, $dataFile);
}

if ($incoming['startGame'] ?? false) {
    if ($state['players']['player1']['name'] && $state['players']['player2']['name']) {
        $state['game_started'] = true;
        save_state($state, $dataFile);
    } else {
        echo json_encode(["error" => "Both names must be set."]);
        exit;
    }
}

if ($incoming['action'] === "move") {
    $player = $incoming['player'];
    $dir = $incoming['direction'];

    if ($state['turn'] !== $player) {
        echo json_encode(["error" => "Not your turn."]);
        exit;
    }

    $lastAction = $state['players'][$player]['last_action_time'] ?? 0;
    if ($timeNow - $lastAction < 2) {
        echo json_encode(["error" => "Wait a moment before acting again."]);
        exit;
    }

    $x = $state['players'][$player]['x'];
    $y = $state['players'][$player]['y'];

    switch ($dir) {
        case "up": if ($y > 0) $y--; break;
        case "down": if ($y < 4) $y++; break;
        case "left": if ($x > 0) $x--; break;
        case "right": if ($x < 4) $x++; break;
        case "roll_up":
    if (in_array("kattenrol", $inventory)) {
        $y = max(0, $y - 3);
        $inventory = array_values(array_filter($inventory, fn($item) => $item !== "kattenrol"));
    }
    break;
case "roll_down":
    if (in_array("kattenrol", $inventory)) {
        $y = min(4, $y + 3);
        $inventory = array_values(array_filter($inventory, fn($item) => $item !== "kattenrol"));
    }
    break;
case "roll_left":
    if (in_array("kattenrol", $inventory)) {
        $x = max(0, $x - 3);
        $inventory = array_values(array_filter($inventory, fn($item) => $item !== "kattenrol"));
    }
    break;
case "roll_right":
    if (in_array("kattenrol", $inventory)) {
        $x = min(4, $x + 3);
        $inventory = array_values(array_filter($inventory, fn($item) => $item !== "kattenrol"));
    }
    break;

    }

    $state['players'][$player]['x'] = $x;
    $state['players'][$player]['y'] = $y;
    $state['players'][$player]['last_action_time'] = $timeNow;
    $inventory = &$state['players'][$player]['inventory'];

if (in_array("kattenmelk", $inventory)) {
    if (!isset($state['players'][$player]['milk_used']) || $state['players'][$player]['milk_used'] === false) {
        $state['players'][$player]['milk_used'] = true;
        // GEEN beurtwissel
    } else {
        // 2e zet gedaan → verwijder melk en wissel beurt
        $state['players'][$player]['milk_used'] = false;
        $inventory = array_values(array_filter($inventory, fn($item) => $item !== "kattenmelk"));
        $state['turn'] = $player === "player1" ? "player2" : "player1";
    }
} else {
    $state['turn'] = $player === "player1" ? "player2" : "player1";
}


    save_state($state, $dataFile);
 }

 if ($incoming['action'] === "attack") {
    $player = $incoming['player'];
    $item = $incoming['item'];
    $opponent = $player === "player1" ? "player2" : "player1";

    if (!in_array($item, $state['players'][$player]['inventory'])) {
        echo json_encode(["error" => "You don't have that item."]);
        exit;
    }

    // Simple range check
    $px = $state['players'][$player]['x'];
    $py = $state['players'][$player]['y'];
    $ox = $state['players'][$player]['x'];
    $oy = $state['players'][$player]['y'];

    if (abs($px - $ox) > 1 || abs($py - $oy) > 1) {
        echo json_encode(["error" => "Too far to attack."]);
        exit;
    }

    // Apply attack
    $state['players'][$opponent]['hp'] -= 1;
    $key = array_search($item, $state['players'][$player]['inventory']);
    unset($state['players'][$player]['inventory'][$key]);
    $state['players'][$player]['last_action_time'] = $timeNow;
    $state['turn'] = $opponent;

    if ($state['players'][$opponent]['hp'] <= 0) {
        $state['game_stated'] = false;
        $state['winner'] = $player;
    }

    save_state($state, $dataFile);
 }

 // Wissel beurt
$data['turn'] = ($player === "player1") ? "player2" : "player1";
