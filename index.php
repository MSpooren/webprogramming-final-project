<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grid Move Game</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="js/ajax.js"></script>
    <script src="js/game.js"></script>
</head>
<body>
    <!-- Game grid -->
    <div class="container" id="gameContainer">
        <h1>Grid Move Game</h1>
        <p>Move your player on the 5x5 grid. Use the <strong>WASD keys</strong> to move. Turns left: <span id="turnsLeft">20</span></p>
        <div id="grid"></div>
        <p id="result"></p>
        <button id="restartBtn" style="display:none;">Restart Game</button>
        <button id="newGameBtn">New Game</button>
    </div>
</body>
</html>
