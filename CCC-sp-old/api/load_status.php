<?php
header('Content-Type: application/json');

$dataFile = '../data/game_state.json';

if (file_exists($dataFile)) {
    echo file_get_contents($dataFile);
} else {
    echo json_encode(["error" => "Game state file not found."]);
}
