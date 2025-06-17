<?php
// Get the session ID from the URL query parameters
$sessionId = isset($_GET['sessionId']) ? $_GET['sessionId'] : null;

// If no session ID was provided, return an error and stop execution
if (!$sessionId) {
    echo json_encode(["error" => "Session ID missing."]);
    exit;
}

// Construct the path to the session file based on the session ID
$filename = "../data/game_" . $sessionId . ".json";

// Set the response header to indicate JSON output
header('Content-Type: application/json');

// If the session file doesn't exist, return an error
if (!file_exists($filename)) {
    echo json_encode(["error" => "Game state not found."]);
    exit;
}

// Read the game state from the JSON file and decode it into an array
$gameState = json_decode(file_get_contents($filename), true);
// Return the game state as a JSON response
echo json_encode($gameState);
?>
