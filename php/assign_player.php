<?php
session_start();

$dataDir = "../data/";
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
}

$sessionId = 1;
$maxPlayersPerSession = 2;

// Find the first session with less than 2 players
while (true) {
    $filename = $dataDir . "game_" . $sessionId . ".json";
    if (!file_exists($filename)) {
        // Create new session file
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
        $state = json_decode(file_get_contents($filename), true);
        if (empty($state["players"]["1"]["name"])) {
            $playerId = 1;
            break;
        } elseif (empty($state["players"]["2"]["name"])) {
            $playerId = 2;
            break;
        }
    }
    $sessionId++;
}

// Voeg muizen toe (zoals in start_game.php)
$occupied = [
    [$state["players"]["1"]["x"], $state["players"]["1"]["y"]],
    [$state["players"]["2"]["x"], $state["players"]["2"]["y"]]
];

$mice = [];

while (count($mice) < 3) {
    $x = rand(0, 6);
    $y = rand(0, 6);

    $tooClose = false;
    foreach ($occupied as $pos) {
        if ($pos[0] == $x && $pos[1] == $y) {
            $tooClose = true;
            break;
        }
    }

    if ($tooClose) continue;

    $mice[] = [
        "x" => $x,
        "y" => $y,
        "direction" => [0, 90, 180, 270][array_rand([0, 90, 180, 270])]
    ];
    $occupied[] = [$x, $y];
}

$state["mice"] = $mice;

file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));


echo json_encode([
    "sessionId" => $sessionId,
    "playerId" => $playerId
]);
?>
