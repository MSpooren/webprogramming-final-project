<?php
// php/save_players.php

// Read the raw POST data and decode it from JSON into a PHP array
$data = json_decode(file_get_contents('php://input'), true);

// Extract the session ID from the received data
$sessionId = $data['sessionId'];
// Construct the filename path to the session file
$filename = "../data/game_" . $sessionId . ".json";

// Check if the session file exists
if (!file_exists($filename)) {
    // If the game session file doesn't exist, return an error and exit
    echo json_encode(["error" => "Game file not found"]);
    exit;
}

// Load the existing game state from the file
$gameState = json_decode(file_get_contents($filename), true);

// Get the player's ID from the request data
$playerId = $data['playerId'];
// Update the player's name and selected skin in the game state
$gameState["players"][$playerId]["name"] = $data['name'];
$gameState["players"][$playerId]["skin"] = $data['skin'];

// Save the updated game state back to the session file
file_put_contents($filename, json_encode($gameState, JSON_PRETTY_PRINT));
// Respond with a success message
echo json_encode(["status" => "saved"]);
?>
