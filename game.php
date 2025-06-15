<!-- game.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cat Couch Clash - Game</title>
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
            position: relative;
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

    <div id="grid"></div>

    <p>Use W A S D to move your cat.</p>
    <p id="inventory">Inventory: </p>
    <button id="useLaser">Gebruik Laserpointer</button>


    <script>
        // Load game state every 2 seconds
        setInterval(loadGameState, 2000);
        loadGameState();

        $(document).on("keydown", function (e) {
            const key = e.key.toLowerCase();
            console.log("Key pressed:", key);
            if (key === "w") sendMove(0, -1);
            if (key === "s") sendMove(0, 1);
            if (key === "a") sendMove(-1, 0);
            if (key === "d") sendMove(1, 0);
        });
    </script>
</body>
</html>
