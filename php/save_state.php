<?php
// php/save_state.php

$data = json_decode(file_get_contents('php://input'), true);

$sessionId = $data['sessionId'] ?? null;
$playerId = $data['playerId'] ?? null;
$move = $data['move'] ?? null;

$filename = "../data/game_" . $sessionId . ".json";
if (!file_exists($filename)) {
    echo json_encode(["error" => "Game file not found"]);
    exit;
}
$gameState = json_decode(file_get_contents($filename), true);
$player = &$gameState["players"][$playerId];

if (isset($player['forced_direction']) && is_string($player['forced_direction'])) {
    // Forceer move op basis van forced_direction
    switch ($player['forced_direction']) {
        case "right": $move = ['x' => 1, 'y' => 0]; break;
        case "left":  $move = ['x' => -1, 'y' => 0]; break;
        case "down":  $move = ['x' => 0, 'y' => 1]; break;
        case "up":    $move = ['x' => 0, 'y' => -1]; break;
        default: $move = ['x' => 0, 'y' => 0]; break;
    }
    $gameState["players"][$playerId]['forced_direction'] = null;
}

// Na deze forced-move-check kun je veilig controleren:
if (!$sessionId || !$playerId || !$move || !isset($move['x']) || !isset($move['y'])) {
    echo json_encode(["error" => "Missing or invalid move data"]);
    exit;
}

$filename = "../data/game_" . $sessionId . ".json";
if (!file_exists($filename)) {
    echo json_encode(["error" => "Game file not found"]);
    exit;
}

// Check turn
if ((int)$gameState["turn"] !== (int)$playerId) {
    echo json_encode(["error" => "Not your turn"]);
    exit;
}

// References
$player = &$gameState["players"][$playerId];
$opponentId = $playerId == "1" ? "2" : "1";
$opponent = &$gameState["players"][$opponentId];

if (!isset($player['last_move'])) $player['last_move'] = null;
if (!isset($player['forced_direction'])) $player['forced_direction'] = null;


if ($move['x'] === 1) $player['last_move'] = "right";
elseif ($move['x'] === -1) $player['last_move'] = "left";
elseif ($move['y'] === 1) $player['last_move'] = "down";
elseif ($move['y'] === -1) $player['last_move'] = "up";

// Proposed new coordinates
$newX = $player['x'] + $move['x'];
$newY = $player['y'] + $move['y'];

// Check bounds
if ($newX < 0 || $newX > 6 || $newY < 0 || $newY > 6) {
    echo json_encode(["error" => "Move out of bounds"]);
    exit;
}

// If moving into opponent — try push
if ($opponent['x'] === $newX && $opponent['y'] === $newY) {
    $pushX = $opponent['x'] + $move['x'];
    $pushY = $opponent['y'] + $move['y'];

    // Prevent pushing off-grid
    if ($pushX < 0 || $pushX > 6 || $pushY < 0 || $pushY > 6) {
        echo json_encode(["error" => "Can't push opponent off the board"]);
        exit;
    }

    // Push the opponent
    $opponent['x'] = $pushX;
    $opponent['y'] = $pushY;
}

// Apply player's move
$player['x'] = $newX;
$player['y'] = $newY;

// Couch logic
updateCouchPointsAndMove($gameState, $playerId, $newX, $newY);

// Initialize inventory if needed
if (!isset($player['inventory'])) {
    $player['inventory'] = [];
}

// ✅ Catch mouse only at new location — preserve others
$remainingMice = [];
foreach ($gameState['mice'] as $mouse) {
    if ($mouse['x'] === $newX && $mouse['y'] === $newY) {
        $player['inventory'][] = 'laserpointer';
        continue; // Don't keep this mouse
    }
    $remainingMice[] = $mouse;
}
$gameState['mice'] = $remainingMice;

$occupied = [];
foreach ($gameState['players'] as $p) {
    $occupied[] = [$p['x'], $p['y']];
}
foreach ($gameState['mice'] as $m) {
    $occupied[] = [$m['x'], $m['y']];
}

$updatedMice = [];
foreach ($gameState['mice'] as $mouse) {
    $dx = $dy = 0;
    switch ($mouse['direction']) {
        case 0: $dy = -1; break;
        case 90: $dx = 1; break;
        case 180: $dy = 1; break;
        case 270: $dx = -1; break;
    }

    $nextX = $mouse['x'] + $dx;
    $nextY = $mouse['y'] + $dy;

    $blocked = false;
    foreach ($occupied as $pos) {
        if ($pos[0] == $nextX && $pos[1] == $nextY) {
            $blocked = true;
            break;
        }
    }

    if ($nextX >= 0 && $nextX <= 6 && $nextY >= 0 && $nextY <= 6 && !$blocked) {
        $mouse['x'] = $nextX;
        $mouse['y'] = $nextY;
    } else {
        $mouse['direction'] = [0, 90, 180, 270][rand(0, 3)];
    }

    $updatedMice[] = $mouse;
    $occupied[] = [$mouse['x'], $mouse['y']];
}

$gameState['mice'] = $updatedMice;

// Switch turn
$gameState["turn"] = ((int)$playerId === 1) ? 2 : 1;

// Save new state
file_put_contents($filename, json_encode($gameState, JSON_PRETTY_PRINT));
echo json_encode(["status" => "moved"]);

<<<<<<< HEAD:wp-mp-wip-new/php/save_state.php
function moveCouch(&$gameState) {
    // Move couch to a random unoccupied position
    $occupied = [];
    foreach ($gameState['players'] as $p) {
        $occupied[] = [$p['x'], $p['y']];
    }
    foreach ($gameState['mice'] as $m) {
        $occupied[] = [$m['x'], $m['y']];
    }
    // Prevent placing on current couch position
    if (isset($gameState['couch'])) {
        $occupied[] = [$gameState['couch']['x'], $gameState['couch']['y']];
    }

    $free = [];
    for ($x = 0; $x < 7; $x++) {
        for ($y = 0; $y < 7; $y++) {
            $isOccupied = false;
            foreach ($occupied as $pos) {
                if ($pos[0] == $x && $pos[1] == $y) {
                    $isOccupied = true;
                    break;
                }
            }
            if (!$isOccupied) {
                $free[] = [$x, $y];
            }
        }
    }
    if (count($free) > 0) {
        $newPos = $free[array_rand($free)];
        $gameState['couch']['x'] = $newPos[0];
        $gameState['couch']['y'] = $newPos[1];
    }
}

function updateCouchPointsAndMove(&$gameState, $playerId, $newX, $newY) {
    if (isset($gameState['couch']) && $gameState['couch']['x'] === $newX && $gameState['couch']['y'] === $newY) {
        if (!isset($gameState['couch_counter'][$playerId])) {
            $gameState['couch_counter'][$playerId] = 0;
        }
        $gameState['couch_counter'][$playerId]++;
        moveCouch($gameState);
    }
}
=======

>>>>>>> 2f6cd225304ac532fdfbdaa3855001641a905c4f:php/save_state.php
?>
