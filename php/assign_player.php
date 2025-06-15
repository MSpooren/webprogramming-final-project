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
            "couch_counter" => ["1" => 0, "2" => 0]
        ];
        file_put_contents($filename, json_encode($state, JSON_PRETTY_PRINT));
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

echo json_encode([
    "sessionId" => $sessionId,
    "playerId" => $playerId
]);
?>
