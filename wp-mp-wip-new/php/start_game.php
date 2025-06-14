<?php
// php/start_game.php
$initialState = [
    "players" => [
        "1" => ["name" => "", "x" => 0, "y" => 3, "skin" => "", "status" => "normal"],
        "2" => ["name" => "", "x" => 6, "y" => 3, "skin" => "", "status" => "normal"]
    ],
    "turn" => 1,
    "mice" => [],
    "items" => [],
    "couch_counter" => ["1" => 0, "2" => 0]
];

file_put_contents('../data/game_state.json', json_encode($initialState, JSON_PRETTY_PRINT));
echo json_encode(["status" => "success"]);
?>

