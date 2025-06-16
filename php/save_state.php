<?php
// php/save_state.php

$data = json_decode(file_get_contents('php://input'), true);

$sessionId = $data['sessionId'] ?? null;
$playerId = $data['playerId'] ?? null;
$move = $data['move'] ?? null;

// If opponent is awaiting mirror move, assign the move direction
if (isset($opponent['awaiting_mirror'])) {
    $opponent['mirror_move'] = [
        'dx' => $move['x'],
        'dy' => $move['y']
    ];
    unset($opponent['awaiting_mirror']);
}

$filename = "../data/game_" . $sessionId . ".json";
if (!file_exists($filename)) {
    echo json_encode(["error" => "Game file not found"]);
    exit;
}
$gameState = json_decode(file_get_contents($filename), true);
$player = &$gameState["players"][$playerId];

// Block movement if game is over
if (isset($gameState['winner'])) {
    echo json_encode(["error" => "Game over"]);
    exit;
}

$state['turn'] = $state['turn'] === 1 ? 2 : 1;


$filename = "../data/game_" . $sessionId . ".json";
if (!file_exists($filename)) {
    echo json_encode(["error" => "Game file not found"]);
    exit;
}

// Check turn
if ((int) $gameState["turn"] !== (int) $playerId) {
    echo json_encode(["error" => "Not your turn"]);
    exit;
}

// References
$player = &$gameState["players"][$playerId];
$opponentId = $playerId == "1" ? "2" : "1";
$opponent = &$gameState["players"][$opponentId];

$data = json_decode(file_get_contents('php://input'), true);




if ($move['x'] === 1) $player['last_move'] = "right";
elseif ($move['x'] === -1) $player['last_move'] = "left";
elseif ($move['y'] === 1) $player['last_move'] = "down";
elseif ($move['y'] === -1) $player['last_move'] = "up";

// Track movesThisTurn
if (!isset($player['movesThisTurn'])) {
    $player['movesThisTurn'] = 0;
}
$player['movesThisTurn']++;

// Proposed new coordinates
$newX = $player['x'] + $move['x'];
$newY = $player['y'] + $move['y'];

// Block movement into static obstacles like plants or lamps
if (isset($gameState['obstacles'])) {
    foreach ($gameState['obstacles'] as $obstacle) {
        if ($obstacle['x'] === $newX && $obstacle['y'] === $newY) {
            echo json_encode(["error" => "You cannot move onto an obstacle"]);
            exit;
        }
    }
}
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

// Mirror move logic: apply the same movement to opponent if set
if (isset($opponent['mirror_move'])) {
    $mdx = $opponent['mirror_move']['dx'];
    $mdy = $opponent['mirror_move']['dy'];
    $targetX = $opponent['x'] + $mdx;
    $targetY = $opponent['y'] + $mdy;

    // Check bounds
    if ($targetX >= 0 && $targetX <= 6 && $targetY >= 0 && $targetY <= 6) {
        $blocked = false;

        // Prevent stepping onto anything occupied
        $blockedPositions = [];

        // Include players (except the opponent themselves)
        foreach ($gameState['players'] as $pid => $p) {
            if ($pid !== $opponentId)
                $blockedPositions[] = [$p['x'], $p['y']];
        }

        // Include mice
        foreach ($gameState['mice'] as $m) {
            $blockedPositions[] = [$m['x'], $m['y']];
        }

        // Include couch
        if (isset($gameState['couch'])) {
            $blockedPositions[] = [$gameState['couch']['x'], $gameState['couch']['y']];
        }

        // Include obstacles
        if (isset($gameState['obstacles'])) {
            foreach ($gameState['obstacles'] as $obs) {
                $blockedPositions[] = [$obs['x'], $obs['y']];
            }
        }

        foreach ($blockedPositions as $pos) {
            if ($pos[0] === $targetX && $pos[1] === $targetY) {
                $blocked = true;
                break;
            }
        }

        if (!$blocked) {
            $opponent['x'] = $targetX;
            $opponent['y'] = $targetY;
        }
    }

    // Clear the effect
    unset($opponent['mirror_move']);
}

// Couch logic
updateCouchPointsAndMove($gameState, $playerId, $newX, $newY);

// Win condition: first to 5 couch points
if (
    isset($gameState['couch_counter']['1']) && $gameState['couch_counter']['1'] >= 5
) {
    $gameState['winner'] = 1;
} elseif (
    isset($gameState['couch_counter']['2']) && $gameState['couch_counter']['2'] >= 5
) {
    $gameState['winner'] = 2;
}

// Initialize inventory if needed
if (!isset($player['inventory'])) {
    $player['inventory'] = [];
}

// Catch mouse only at new location — preserve others
$remainingMice = [];
$mouseCaught = false;

foreach ($gameState['mice'] as $mouse) {
    if ($mouse['x'] === $newX && $mouse['y'] === $newY) {
        // 50% kans om iets te krijgen
        if (rand(0, 1) === 1) {
            $possibleItems = ["laserpointer", "wool", "milk"];
            $randomItem = $possibleItems[array_rand($possibleItems)];
            $player['inventory'][] = $randomItem;
        }
        $mouseCaught = true;
        continue; // Verwijder deze muis
    }
    $remainingMice[] = $mouse;
}

$gameState['mice'] = $remainingMice;


// Respawn a mouse if one was caught
if ($mouseCaught) {
    // Find all unoccupied positions
    $occupiedPositions = [];
    foreach ($gameState['players'] as $p) {
        $occupiedPositions[] = [$p['x'], $p['y']];
    }
    foreach ($gameState['mice'] as $m) {
        $occupiedPositions[] = [$m['x'], $m['y']];
    }
    if (isset($gameState['couch'])) {
        $occupiedPositions[] = [$gameState['couch']['x'], $gameState['couch']['y']];
    }

    $freePositions = [];
    for ($x = 0; $x < 7; $x++) {
        for ($y = 0; $y < 7; $y++) {
            $isOccupied = false;
            foreach ($occupiedPositions as $pos) {
                if ($pos[0] == $x && $pos[1] == $y) {
                    $isOccupied = true;
                    break;
                }
            }
            if (!$isOccupied) {
                $freePositions[] = [$x, $y];
            }
        }
    }
    if (count($freePositions) > 0) {
        $newPos = $freePositions[array_rand($freePositions)];
        $directions = [0, 90, 180, 270];
        $gameState['mice'][] = [
            'x' => $newPos[0],
            'y' => $newPos[1],
            'direction' => $directions[array_rand($directions)]
        ];
    }
}

$occupied = [];
foreach ($gameState['players'] as $p) {
    $occupied[] = [$p['x'], $p['y']];
}
foreach ($gameState['mice'] as $m) {
    $occupied[] = [$m['x'], $m['y']];
}
// Prevent mice from stepping on the couch
if (isset($gameState['couch'])) {
    $occupied[] = [$gameState['couch']['x'], $gameState['couch']['y']];
}
// Prevent mice from stepping on obstacles
if (isset($gameState['obstacles'])) {
    foreach ($gameState['obstacles'] as $obstacle) {
        $occupied[] = [$obstacle['x'], $obstacle['y']];
    }
}
$updatedMice = [];
foreach ($gameState['mice'] as $mouse) {
    $dx = $dy = 0;
    switch ($mouse['direction']) {
        case 0:
            $dy = -1;
            break;
        case 90:
            $dx = 1;
            break;
        case 180:
            $dy = 1;
            break;
        case 270:
            $dx = -1;
            break;
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

// After all move logic, handle turn switching:
if ($player['movesThisTurn'] >= 2) {
    // Switch turn
    $gameState["turn"] = ((int) $playerId === 1) ? 2 : 1;
    // Reset movesThisTurn for both players
    $gameState["players"][$playerId]['movesThisTurn'] = 0;
    $gameState["players"][$opponentId]['movesThisTurn'] = 0;
}

// Save new state
file_put_contents($filename, json_encode($gameState, JSON_PRETTY_PRINT));
echo json_encode(["status" => "moved"]);

function moveCouch(&$gameState)
{
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

function updateCouchPointsAndMove(&$gameState, $playerId, $newX, $newY)
{
    if (isset($gameState['couch']) && $gameState['couch']['x'] === $newX && $gameState['couch']['y'] === $newY) {
        if (!isset($gameState['couch_counter'][$playerId])) {
            $gameState['couch_counter'][$playerId] = 0;
        }
        $gameState['couch_counter'][$playerId]++;
        moveCouch($gameState);
    }
}
?>