<?php
// php/reset_game.php

// Get the session ID from the URL query parameter
$sessionId = isset($_GET['sessionId']) ? $_GET['sessionId'] : null;
// If no session ID is provided, return an error response and stop
if (!$sessionId) {
    echo json_encode(["success" => false, "message" => "Session ID missing"]);
    exit;
}

// Build the path to the session's JSON file
$filename = "../data/game_" . $sessionId . ".json";
// Check if the game session file exists
if (file_exists($filename)) {
    // Delete the game file
    unlink($filename);
    echo json_encode(["success" => true, "message" => "Game reset."]);
} else {
    // If the file doesn't exist, return an error message
    echo json_encode(["success" => false, "message" => "Game file already deleted, returning to Main Menu."]);
}
?>
