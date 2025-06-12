<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter Cat Name</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="js/ajax.js"></script>
    <script src="js/name-setup.js"></script>
</head>
<body>
    <!-- Skin selection -->
    <div class="skin-select-wrapper">
  <h2>Kies je kat:</h2>
  <div class="carousel-wrapper">
    <button id="prev">‹</button>
    <div class="carousel" id="carousel">
      <img src="images/tile000.png" class="cat active" data-skin="tile000.png">
      <img src="images/tile001.png" class="cat" data-skin="tile001.png">
      <img src="images/tile002.png" class="cat" data-skin="tile002.png">
    </div>
    <button id="next"></button>
  </div>
  <p>Geselecteerde skin: <span id="skinName">tile000.png</span></p>
</div>

    <!-- Name setup form -->
    <div id="nameSetup">
        <h2>Enter your cat name!</h2>
        <form id="nameForm">
            <label>Player:</label>
            <select id="player">
                <option value="player1">Player 1</option>
                <option value="player2">Player 2</option>
            </select>
            <br><br>
            <label for="catName">Cat Name:</label>
            <input type="text" id="catName" required>
            <br><br>
            <button type="submit">Save Name</button>
        </form>
        <div id="nameSavedMsg"></div>
        <button id="startGameBtn">Start Game</button>
    </div>
</body>
</html>
