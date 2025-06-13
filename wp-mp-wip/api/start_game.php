
<?php
$data = json_decode(file_get_contents("php://input"), true);
$gameName = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['game_name'] ?? '');
$gameFile = "../data/{$gameName}.json";

if (!file_exists($gameFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'Game not found']);
    exit;
}

$state = json_decode(file_get_contents($gameFile), true);
$state['started'] = true;
file_put_contents($gameFile, json_encode($state));
echo json_encode(['status' => 'started']);
?>
