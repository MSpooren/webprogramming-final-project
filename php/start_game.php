<?php
// php/start_game.php

// Get the session ID from the query parameter
$sessionId = isset($_GET['sessionId']) ? $_GET['sessionId'] : null;
if (!$sessionId) {
    echo "Session ID missing";
    exit;
}

// Construct the game file path
$filename = "../data/game_" . $sessionId . ".json";
// Check if the game file exists
if (!file_exists($filename)) {
    echo "Game file not found";
    exit;
}

// Load the existing game state
$gameState = json_decode(file_get_contents($filename), true);

// Preserve existing player info
$player1 = $gameState["players"]["1"];
$player2 = $gameState["players"]["2"];

// Initialize new game state with player positions and base values
$gameState = [
    "players" => [
        "1" => array_merge($player1, ["x" => 0, "y" => 3, "status" => "normal", "movesThisTurn" => 0]),
        "2" => array_merge($player2, ["x" => 6, "y" => 3, "status" => "normal", "movesThisTurn" => 0]),
    ],
    "turn" => 1,
    "turnCounter" => 1,
    "mice" => [],
    "items" => [],
    "couch_counter" => [
        "1" => 0,
        "2" => 0
    ],
    "couch" => [
        "x" => 3,
        "y" => 3
    ]
];

// Track occupied positions (players + couch)
$occupied = [
    [$gameState["players"]["1"]["x"], $gameState["players"]["1"]["y"]],
    [$gameState["players"]["2"]["x"], $gameState["players"]["2"]["y"]],
    [$gameState["couch"]["x"], $gameState["couch"]["y"]]
];

// Add 3 mice, placed away from occupied positions
$mice = [];

while (count($mice) < 3) {
    $x = rand(0, 6);
    $y = rand(0, 6);

    // Ensure no overlap with occupied tiles
    $tooClose = false;
    foreach ($occupied as $pos) {
        if ($pos[0] == $x && $pos[1] == $y) {
            $tooClose = true;
            break;
        }
    }

    if ($tooClose) continue;

    // Add mouse with a random direction
    $mice[] = [
        "x" => $x,
        "y" => $y,
        "direction" => [0, 90, 180, 270][array_rand([0, 90, 180, 270])]
    ];
    $occupied[] = [$x, $y]; // mark as used
}

$gameState["mice"] = $mice;

// Define a helper function to find a free position
function getRandomFreePosition($occupied) {
    do {
        $x = rand(0, 6);
        $y = rand(0, 6);
        $pos = [$x, $y];
    } while (in_array($pos, $occupied));
    return $pos;
}

// Define obstacle types
$obstacleTypes = ['plant', 'lamp', 'lamp'];
$obstacles = [];

foreach ($obstacleTypes as $type) {
    [$x, $y] = getRandomFreePosition($occupied);
    $occupied[] = [$x, $y];
    $obstacles[] = ['type' => $type, 'x' => $x, 'y' => $y];
}

$gameState["obstacles"] = $obstacles;

// Save the initialized game state back to the file
file_put_contents($filename, json_encode($gameState, JSON_PRETTY_PRINT));
// Notify the client
echo "Game started";
?>