<?php
ob_clean();
header('Content-Type: application/json');
// Get incoming JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);
// Extract required parameters from the request
$sessionId = $data['sessionId'] ?? null;
$playerId = $data['playerId'] ?? null;
$item = $data['item'] ?? null;

// Load game state from file
$filename = "../data/game_" . $sessionId . ".json";
if (!file_exists($filename)) {
    echo json_encode(["error" => "Game file not found"]);
    exit;
}

$state = json_decode(file_get_contents($filename), true);

// Check if it's the player's turn
if ((string) $state["turn"] !== (string) $playerId) {
    echo json_encode(["success" => false, "error" => "It is not your turn."]);
    exit;
}

// Load player and opponent data
$player = &$state['players'][$playerId];
$opponentId = $playerId === "1" ? "2" : "1";
$opponent = &$state['players'][$opponentId];

// Ensure player owns the item they're trying to use
if (!in_array($item, $player['inventory'])) {
    echo json_encode(["error" => "Item not in inventory"]);
    exit;
}

// Checks if player landed on couch, updates points, and moves couch
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

// Moves the couch to a random unoccupied space
function moveCouch(&$gameState)
{
    $occupied = [];
    // Add all player and mouse positions
    foreach ($gameState['players'] as $p) {
        $occupied[] = [$p['x'], $p['y']];
    }
    foreach ($gameState['mice'] as $m) {
        $occupied[] = [$m['x'], $m['y']];
    }
    // Include couch's current position
    if (isset($gameState['couch'])) {
        $occupied[] = [$gameState['couch']['x'], $gameState['couch']['y']];
    }

    // Voeg obstakels toe aan de bezette plekken
    if (isset($gameState['obstacles'])) {
        foreach ($gameState['obstacles'] as $obs) {
            $occupied[] = [$obs['x'], $obs['y']];

        }
    }
    // Find all free tiles
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

    // Move couch to a random free tile
    if (count($free) > 0) {
        $newPos = $free[array_rand($free)];
        $gameState['couch']['x'] = $newPos[0];
        $gameState['couch']['y'] = $newPos[1];
    }
}

// Check if there's an obstacle at a given position
function isObstacleAt($state, $x, $y)
{
    if (!isset($state['obstacles']))
        return false;
    foreach ($state['obstacles'] as $obstacle) {
        if ($obstacle['x'] === $x && $obstacle['y'] === $y) {
            return true;
        }
    }
    return false;
}

// Laserpointer item logic
if ($item === "laserpointer") {
    $direction = $player['last_move'] ?? null;
    if (!$direction) {
        echo json_encode(["error" => "No previous move direction found"]);
        exit;
    }

    // Determine direction vector
    $dx = 0;
    $dy = 0;
    switch ($direction) {
        case "up":
            $dy = -1;
            break;
        case "down":
            $dy = 1;
            break;
        case "left":
            $dx = -1;
            break;
        case "right":
            $dx = 1;
            break;
    }

    // Calculate opponent's new position
    $newX = $opponent['x'] + $dx;
    $newY = $opponent['y'] + $dy;

    // Clamp within board boundaries
    $newX = max(0, min(6, $newX));
    $newY = max(0, min(6, $newY));

    // Prevent moving into an obstacle
    if (isObstacleAt($state, $newX, $newY)) {
        echo json_encode(["error" => "You can't push your opponent into an object!"]);
        exit;
    }

    // Update opponent position
    $opponent['x'] = $newX;
    $opponent['y'] = $newY;

    // Handle possible couch interaction
    updateCouchPointsAndMove($state, $opponentId, $newX, $newY);

    // Store last laser direction for mirror effect
    $opponent['mirror_move'] = [
        'dx' => $dx,
        'dy' => $dy
    ];

    // Remove laserpointer from inventory
    $index = array_search("laserpointer", $player['inventory']);
    if ($index !== false) {
        unset($player['inventory'][$index]);
        $player['inventory'] = array_values($player['inventory']);
    }

    // Save changes back into state
    $state['players'][$opponentId] = $opponent;
    $state['players'][$playerId] = $player;

    error_log("Laserpointer response: " . json_encode(["success" => true]));
    // Save state
    file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode(["success" => true]);
    exit;
}

// Wool item logic
elseif ($item === "wool") {
    $direction = $data['direction'] ?? null;

    // Validate direction format
    if (!$direction || !isset($direction['x']) || !isset($direction['y'])) {
        echo json_encode(["error" => "Invalid direction."]);
        exit;
    }

    $dx = $direction['x'];
    $dy = $direction['y'];

    // Must move exactly 3 tiles in one axis
    if (!((abs($dx) === 3 && $dy === 0) || (abs($dy) === 3 && $dx === 0))) {
        echo json_encode(["error" => "Catroll must be 3 tiles in one direction."]);
        exit;
    }

    // Calculate destination
    $newX = $player['x'] + $dx;
    $newY = $player['y'] + $dy;

    // Check board bounds
    if ($newX < 0 || $newX > 6 || $newY < 0 || $newY > 6) {
        echo json_encode(["error" => "Outside the playingfield."]);
        exit;
    }

    // Prevent movement into obstacle
    if (isObstacleAt($state, $newX, $newY)) {
        echo json_encode(["error" => "You can't move into an object!"]);
        exit;
    }

    // Update player position
    $player['x'] = $newX;
    $player['y'] = $newY;

    updateCouchPointsAndMove($state, $playerId, $newX, $newY);

    // Remove wool from inventory without counting as a move
    $index = array_search("wool", $player['inventory']);
    if ($index !== false) {
        unset($player['inventory'][$index]);
        $player['inventory'] = array_values($player['inventory']);
    }

    // Save updated player state
    $state['players'][$playerId] = $player;

    // Save overall game state
    file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));

    // Respond success
    echo json_encode(["success" => true]);
    exit;
}



// Milk item logic
elseif ($item === "milk") {
    $direction = $data['direction'] ?? null;

    // Validate direction format
    if (!$direction || !isset($direction['x']) || !isset($direction['y'])) {
        echo json_encode(["error" => "Invalid direction."]);
        exit;
    }

    $dx = $direction['x'];
    $dy = $direction['y'];

    // Must move exactly 1 tile diagonally
    if (!(abs($dx) === 1 && abs($dy) === 1)) {
        echo json_encode(["error" => "You must move exactly 1 tile diagonally."]);
        exit;
    }

    $newX = $player['x'] + $dx;
    $newY = $player['y'] + $dy;

    // Check board bounds
    if ($newX < 0 || $newX > 6 || $newY < 0 || $newY > 6) {
        echo json_encode(["error" => "Out of bounds."]);
        exit;
    }

    // Prevent moving into obstacle
    if (isObstacleAt($state, $newX, $newY)) {
        echo json_encode(["error" => "You can't move into an object!"]);
        exit;
    }

    // Update player position
    $player['x'] = $newX;
    $player['y'] = $newY;

    // Handle possible couch interaction
    updateCouchPointsAndMove($state, $playerId, $newX, $newY);

    // Remove milk from inventory
    $index = array_search("milk", $player['inventory']);
    if ($index !== false) {
        unset($player['inventory'][$index]);
        $player['inventory'] = array_values($player['inventory']);
    }

    // DO NOT increment movesThisTurn
    // DO NOT change turn

    // Save player state
    $state['players'][$playerId] = $player;

    // Save updated game state
    file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode(["success" => true]);
    exit;
}


// Fallback: Unknown item type
echo json_encode(["error" => "Unknown item"]);