<!-- game.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cat Couch Clash - Game</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/game.js"></script>
    <script>
        const sessionId = localStorage.getItem("sessionId");
        const playerId = localStorage.getItem("playerId");

        if (!sessionId || !playerId) {
            alert("Session not found. Redirecting...");
            window.location.href = "index.php";
        }
    </script>

</head>

<body class="game-background">
    <h2>Cat Couch Clash</h2>
    <p id="turn-indicator">Loading...</p>

    <div id="grid-wrapper">
        <div id="grid">
            <?php
            for ($i = 0; $i < 49; $i++) {
                echo "<div class='tile'></div>";
            }
            ?>
        </div>
    </div>
    
    <div class="scoreboard">
        <h3>Scoreboard</h3>
        <p id="player1-score"></p>
        <p id="player2-score"></p>
    </div>

    <p>Use W A S D to move your cat.</p>

    <ul id="inventory"></ul>

    <button class="pixel-button" id="useLaser">Gebruik Laserpointer</button>
    <button class="pixel-button" id="useWool">Gebruik kattenrol (wool)</button>
    <button class="pixel-button" id="useMilk">Gebruik Melk</button>
    <br>
    <button class="pixel-button" id="resetGame">Reset Game</button>
</body>

</html>