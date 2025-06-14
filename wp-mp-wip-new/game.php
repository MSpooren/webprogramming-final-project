<!-- game.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cat Couch Clash - Game</title>
    <link rel="stylesheet" href="styles/main.css">
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
            gap: 2px;
            margin-bottom: 20px;
        }
        .tile {
            width: 50px;
            height: 50px;
            background-color: #f4f4f4;
            border: 1px solid #ccc;
            text-align: center;
            line-height: 50px;
            font-weight: bold;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <h2>Cat Couch Clash</h2>
    <p id="turn-indicator">Loading...</p>

    <div id="grid"></div>

    <div>
        <button onclick="sendMove(0, -1)">↑</button><br>
        <button onclick="sendMove(-1, 0)">←</button>
        <button onclick="sendMove(1, 0)">→</button><br>
        <button onclick="sendMove(0, 1)">↓</button>
    </div>

    <script>
        const playerId = localStorage.getItem("playerId");
        if (!playerId) {
            alert("No player selected. Returning to main menu.");
            window.location.href = "index.php";
        }

        // Load game state every 2 seconds
        setInterval(loadGameState, 2000);
        loadGameState();
    </script>
</body>
</html>
