<?php
// api/reset_game.php
header('Content-Type: application/json');
$default_state = [
    "players" => [
        "player1" => [
            "id" => 1,
            "name" => "",
            "x" => 0,
            "y" => 0,
            "hp" => 10,
            "inventory" => []
        ],
        "player2" => [
            "id" => 2,
            "name" => "",
            "x" => 5,
            "y" => 5,
            "hp" => 10,
            "inventory" => []
        ]
    ],
    "items" => [],
    "turn" => "player1",
    "last_action_time" => 0,
    "game_started" => false
];
$file = __DIR__ . '/../data/game_state.json';
file_put_contents($file, json_encode($default_state, JSON_PRETTY_PRINT));
echo json_encode(["success" => true]);
