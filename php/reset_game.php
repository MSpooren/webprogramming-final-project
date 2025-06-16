<?php
// php/reset_game.php

$sessionId = isset($_GET['sessionId']) ? $_GET['sessionId'] : null;
if (!$sessionId) {
    echo json_encode(["success" => false, "message" => "Session ID missing"]);
    exit;
}

$filename = "../data/game_" . $sessionId . ".json";
if (file_exists($filename)) {
    unlink($filename);
    echo json_encode(["success" => true, "message" => "Game reset."]);
} else {
    echo json_encode(["success" => false, "message" => "Game file not found."]);
}
?>
