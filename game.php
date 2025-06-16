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

    <style>
        #grid {
            display: grid;
            grid-template-columns: repeat(7, 50px);
            grid-template-rows: repeat(7, 50px);
            gap: 0;
            padding: 0;
            margin: 0;
            margin-bottom: 20px;
        }
        .tile {
            width: 50px;
            height: 50px;
            background-color: #f4f4f4;
            border: none;
            margin: 0;
            padding: 0;
            text-align: center;
            line-height: 50px;
            font-weight: bold;
            font-size: 20px;
            position: relative;
            box-sizing: border-box;

            /* Backrgound PNG for each tile */
            background-image: url('images/wooden_plank.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }
        .tile img {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>
    <h2>Cat Couch Clash</h2>
    <p id="turn-indicator">Loading...</p>
    
    <div id="grid">
        <?php
        for ($i = 0; $i < 49; $i++) {
            echo "<div class='tile'></div>";
        }
        ?>
    </div>

    <div class="scoreboard">
        <h3>Scoreboard</h3>
        <p id="player1-score"></p>
        <p id="player2-score"></p>
    </div>

    <p>Use W A S D to move your cat.</p>

    <p>Inventory:</p>
    <ul id="inventory"></ul>

    <button class="pixel-button" id="useLaser">Gebruik Laserpointer</button>
    <button class="pixel-button" id="useWool">Gebruik kattenrol (wool)</button>
    <button class="pixel-button" id="useMilk">Gebruik Melk</button>
    <br>
    <button class="pixel-button" id="resetGame">Reset Game</button>
</body>
</html>
