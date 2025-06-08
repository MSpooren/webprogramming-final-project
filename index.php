<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grid Move Game</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Grid Move Game</h1>
        <p>Move your player on the 5x5 grid. Use the <strong>WASD keys</strong> to move. Turns left: <span id="turnsLeft">20</span></p>
        <div id="grid"></div>
        <p id="result"></p>
        <button id="restartBtn" style="display:none;">Restart Game</button>
    </div>
    <script src="js/game.js"></script>
</body>
</html>
