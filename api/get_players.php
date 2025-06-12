<?php
header('Content-Type: application/json');

$gameStateFile = __DIR__ . '/../data/game_state.json';

if (!file_exists($gameStateFile)) {
    echo json_encode([
        'player1' => ['name' => 'Player 1', 'skin' => 'tile000.png'],
        'player2' => ['name' => 'Player 2', 'skin' => 'tile004.png']
    ]);
    exit;
}

$data = json_decode(file_get_contents($gameStateFile), true);

$player1 = $data['players']['player1'];
$player2 = $data['players']['player2'];

echo json_encode([
    'player1' => [
        'name' => $player1['name'],
        'skin' => $player1['skin']
    ],
    'player2' => [
        'name' => $player2['name'],
        'skin' => $player2['skin']
    ]
]);
