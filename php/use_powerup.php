<?php
$data = json_decode(file_get_contents('php://input'), true);
$sessionId = $data['sessionId'] ?? null;
$playerId = $data['playerId'] ?? null;
$item = $data['item'] ?? null;

// ✅ Eerst het bestand en state laden
$filename = "../data/game_" . $sessionId . ".json";
if (!file_exists($filename)) {
    echo json_encode(["error" => "Game file not found"]);
    exit;
}

$state = json_decode(file_get_contents($filename), true);

// ✅ Nu mag je controleren wiens beurt het is
if ((string)$state["turn"] !== (string)$playerId) {
    echo json_encode(["success" => false, "error" => "It is not your turn."]);
    exit;
}

// ✅ Daarna spelersgegevens ophalen
$player = &$state['players'][$playerId];
$opponentId = $playerId === "1" ? "2" : "1";
$opponent = &$state['players'][$opponentId];

// ✅ Check of speler het item heeft
if (!in_array($item, $player['inventory'])) {
    echo json_encode(["error" => "Item not in inventory"]);
    exit;
}

// ✅ Couch-functies blijven hier onder
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

function moveCouch(&$gameState)
{
    $occupied = [];
    foreach ($gameState['players'] as $p) {
        $occupied[] = [$p['x'], $p['y']];
    }
    foreach ($gameState['mice'] as $m) {
        $occupied[] = [$m['x'], $m['y']];
    }
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

function isObstacleAt($state, $x, $y) {
    if (!isset($state['obstacles'])) return false;
    foreach ($state['obstacles'] as $obstacle) {
        if ($obstacle['x'] === $x && $obstacle['y'] === $y) {
            return true;
        }
    }
    return false;
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

    // Check bounds
    $newX = max(0, min(6, $newX));
    $newY = max(0, min(6, $newY));

    // ➕ Voeg hier de obstakel-check toe
    if (isObstacleAt($state, $newX, $newY)) {
        echo json_encode(["error" => "You can't push your opponent into an object!"]);
        exit;
    }

    // Move opponent
    $opponent['x'] = $newX;
    $opponent['y'] = $newY;

    updateCouchPointsAndMove($state, $opponentId, $newX, $newY);

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

    if (isObstacleAt($state, $newX, $newY)) {
        echo json_encode(["error" => "You can't move into an object!"]);
        exit;
    }

    // Check if opponent is on new spot
    if ($opponent['x'] === $newX && $opponent['y'] === $newY) {
        // Vind alle vrije vakjes rondom
        $freeSpots = [];
        for ($dxo = -1; $dxo <= 1; $dxo++) {
            for ($dyo = -1; $dyo <= 1; $dyo++) {
                if ($dxo === 0 && $dyo === 0) continue;
                $ox = $newX + $dxo;
                $oy = $newY + $dyo;

                if ($ox < 0 || $ox > 6 || $oy < 0 || $oy > 6) continue;

                if (
                    !isObstacleAt($state, $ox, $oy) &&
                    !($player['x'] === $ox && $player['y'] === $oy)
                ) {
                    $freeSpots[] = ['x' => $ox, 'y' => $oy];
                }
            }
        }

        if (!empty($freeSpots)) {
            $chosen = $freeSpots[array_rand($freeSpots)];
            $opponent['x'] = $chosen['x'];
            $opponent['y'] = $chosen['y'];

            $state['turn'] = ($playerId === "1") ? "2" : "1";
            $state['players']["1"]['movesThisTurn'] = 0;
            $state['players']["2"]['movesThisTurn'] = 0;

        }
    }

        // Verplaats speler
    $player['x'] = $newX;
    $player['y'] = $newY;

    updateCouchPointsAndMove($state, $playerId, $newX, $newY);

    // Verwijder wool uit inventory
    $player['inventory'] = array_values(array_filter($player['inventory'], fn($i) => $i !== "wool"));

    // Zet movesThisTurn +1
    $player['movesThisTurn'] = ($player['movesThisTurn'] ?? 0) + 1;

    // Wissel beurt als movesThisTurn 2 of meer is
    if ($player['movesThisTurn'] >= 2) {
        // Wissel beurt en reset moves
        $state['turn'] = ($playerId === "1") ? "2" : "1";
        $state['players']['1']['movesThisTurn'] = 0;
        $state['players']['2']['movesThisTurn'] = 0;
    }

    file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode(["success" => true]);
    exit;
}



elseif ($item === "milk") {
    $direction = $data['direction'] ?? null;

    if (!$direction || !isset($direction['x']) || !isset($direction['y'])) {
        echo json_encode(["error" => "Ongeldige richting"]);
        exit;
    }

    $dx = $direction['x'];
    $dy = $direction['y'];

    if (!((abs($dx) === 1 && abs($dy) === 1))) {
        echo json_encode(["error" => "Beweging moet diagonaal zijn"]);
        exit;
    }

    $newX = $player['x'] + $dx;
    $newY = $player['y'] + $dy;

    if ($newX < 0 || $newX > 6 || $newY < 0 || $newY > 6) {
        echo json_encode(["error" => "Buiten het speelveld"]);
        exit;
    }

    if (isObstacleAt($state, $newX, $newY)) {
    echo json_encode(["error" => "You can't move into an object!"]);
    exit;
    }

    $player['x'] = $newX;
    $player['y'] = $newY;

    updateCouchPointsAndMove($state, $playerId, $newX, $newY);

    $player['inventory'] = array_values(array_filter($player['inventory'], fn($i) => $i !== "milk"));

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