
<?php
$mode = $_GET['mode'] ?? '';
$gameName = $_GET['game_name'] ?? '';
$playerName = $_GET['player_name'] ?? '';
$role = $_GET['role'] ?? '';

function sanitize($value) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $value);
}

$gameName = sanitize($gameName);
$playerName = sanitize($playerName);

if ($mode === 'create') {
    echo '<h1>Create Game</h1>';
    echo '<form method="GET" action="game.php">';
    echo '<input type="hidden" name="mode" value="lobby">';
    echo '<input type="hidden" name="role" value="host">';
    echo '<input type="text" name="game_name" placeholder="Enter Game Name" required>';
    echo '<input type="text" name="player_name" placeholder="Enter Your Nickname" required>';
    echo '<button type="submit">Create Game</button>';
    echo '</form>';
    exit;
}

if ($mode === 'join') {
    echo '<h1>Join Game</h1>';
    echo '<form method="GET" action="game.php">';
    echo '<input type="hidden" name="mode" value="lobby">';
    echo '<input type="hidden" name="role" value="guest">';
    echo '<input type="text" name="game_name" placeholder="Enter Game Name" required>';
    echo '<input type="text" name="player_name" placeholder="Enter Your Nickname" required>';
    echo '<button type="submit">Join Game</button>';
    echo '</form>';
    exit;
}

if ($mode === 'lobby' && $gameName && $playerName && $role) {
    $gameFile = "data/{$gameName}.json";
    if (!file_exists($gameFile)) {
        $state = [
            'game_name' => $gameName,
            'players' => [
                $role => $playerName
            ],
            'started' => false,
            'grid' => array_fill(0, 5, array_fill(0, 5, null)),
        ];
        file_put_contents($gameFile, json_encode($state));
    } else {
        $state = json_decode(file_get_contents($gameFile), true);
        $state['players'][$role] = $playerName;
        file_put_contents($gameFile, json_encode($state));
    }
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Lobby - $gameName</title>
    <link rel='stylesheet' href='css/style.css'>
    <script>
        const gameName = '$gameName';
        const playerRole = '$role';
        const playerName = '$playerName';
    </script>
    <script src='js/lobby.js'></script>
</head>
<body>
    <div id='lobby'>
        <h1>Lobby: $gameName</h1>
        <p>You are: <strong>$playerName</strong> ($role)</p>
        <div id='playerList'></div>
        <button id='startGameBtn' style='display:none;'>Start Game</button>
    </div>
</body>
</html>";
    exit;
}

if ($mode === 'play' && $gameName) {
    $gameFile = "data/{$gameName}.json";
    if (!file_exists($gameFile)) {
        echo "<p>Game not found. <a href='index.php'>Return to menu</a></p>";
        exit;
    }
    $gameState = json_decode(file_get_contents($gameFile), true);
} else {
    echo "<p>Invalid request. <a href='index.php'>Return to menu</a></p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grid Move Game - <?php echo htmlspecialchars($gameName); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="js/ajax.js"></script>
    <script src="js/game.js"></script>
</head>
<body>
    <div class="container" id="gameContainer">
        <h1>Grid Move Game - <?php echo htmlspecialchars($gameName); ?></h1>
        <p>Move your player on the 5x5 grid. Use the <strong>WASD keys</strong> to move. Turns left: <span id="turnsLeft">20</span></p>
        <div id="grid"></div>
        <p id="result"></p>
        <button id="restartBtn" style="display:none;">Restart Game</button>
        <button id="newGameBtn">New Game</button>
    </div>
</body>
</html>
