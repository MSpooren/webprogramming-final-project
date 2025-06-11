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
    <div class="skin-select-wrapper">
  <h2>Kies je kat:</h2>
  <div class="carousel-wrapper">
    <button id="prev">‹</button>
    <div class="carousel" id="carousel">
      <img src="images/tile000.png" class="cat active" data-skin="tile000.png">
      <img src="images/tile001.png" class="cat" data-skin="tile001.png">
      <img src="images/tile002.png" class="cat" data-skin="tile002.png">
    </div>
    <button id="next">›</button>
  </div>
  <p>Geselecteerde skin: <span id="skinName">tile000.png</span></p>
</div>

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
