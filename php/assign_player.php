<?php
session_start();

// Define the directory to store session data files
$dataDir = "../data/";
//Create the data directory if it doesn't exist
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true); // Recursively create with full permissions
}

// Initialize session ID
$sessionId = 1;
$maxPlayersPerSession = 2;

// Finds or creates the first available game session with a free player slot
while (true) {
    $filename = $dataDir . "game_" . $sessionId . ".json";
    // If the session file doesn't exist, create a new game session
    if (!file_exists($filename)) {
        // Default initial game state
        $state = [
            "players" => [
                "1" => ["name" => "", "x" => 0, "y" => 3, "skin" => "", "status" => "normal"],
                "2" => ["name" => "", "x" => 6, "y" => 3, "skin" => "", "status" => "normal"]
            ],
            "turn" => 1,
            "mice" => [],
            "items" => [],
            "couch_counter" => ["1" => 0, "2" => 0],
            "couch" => [
                "x" => 3,
                "y" => 3
            ]
        ];

        $playerId = 1;
        break;
    } else {
        // If the session file exists, decode its state
        $state = json_decode(file_get_contents($filename), true);
        // Check if player 1 slot is available
        if (empty($state["players"]["1"]["name"])) {
            $playerId = 1;
            break;
        } 
        // Otherwise, check if player 2slot is available
        elseif (empty($state["players"]["2"]["name"])) {
            $playerId = 2;
            break;
        }
    }
    // If both slots are filled, try the next session ID
    $sessionId++;
}

// Adds mice to the game grid (like in start_game.php)
$occupied = [
    [$state["players"]["1"]["x"], $state["players"]["1"]["y"]],
    [$state["players"]["2"]["x"], $state["players"]["2"]["y"]]
];

$mice = [];

// Generate 3 random mice not overlapping with players or other mice
while (count($mice) < 3) {
    $x = rand(0, 6);
    $y = rand(0, 6);

    $tooClose = false;
    foreach ($occupied as $pos) {
        // If the generated mouse is too close to another entity, skip
        if ($pos[0] == $x && $pos[1] == $y) {
            $tooClose = true;
            break;
        }
    }

    if ($tooClose) continue;

    // Add mouse with random direction
    $mice[] = [
        "x" => $x,
        "y" => $y,
        "direction" => [0, 90, 180, 270][array_rand([0, 90, 180, 270])]
    ];
    // Mark this tile as now occupied
    $occupied[] = [$x, $y];
}

// Add generated mice to the game state
$state["mice"] = $mice;

// Save the updated game state to the JSON file
file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));

// Return session and player ID to the client as JSON
echo json_encode([
    "sessionId" => $sessionId,
    "playerId" => $playerId
]);
?>
