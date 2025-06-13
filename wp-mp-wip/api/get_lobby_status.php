
<?php
$gameName = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['game_name'] ?? '');
$gameFile = "../data/{$gameName}.json";

header('Content-Type: application/json');

if (!file_exists($gameFile)) {
    echo json_encode(['error' => 'Game not found']);
    exit;
}

$data = json_decode(file_get_contents($gameFile), true);
echo json_encode([
    'players' => $data['players'] ?? [],
    'started' => $data['started'] ?? false
]);
?>
