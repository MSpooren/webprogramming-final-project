<?php 
$bodyClass = 'game-background';
include 'templates/header.php'; 
?>
<!-- Game Title -->
<h2>Cat Couch Clash</h2>
<p id="turn-indicator">Loading...</p>
<!-- Game grid container -->
<div id="grid-wrapper">
    <div id="grid">
        <?php
        // Generate 49 tiles dynamically using PHP
        for ($i = 0; $i < 49; $i++) {
            echo "<div class='tile'></div>";
        }
        ?>
    </div>
</div>
<!-- Scoreboard Section -->
<div class="scoreboard">
    <h3>Scoreboard</h3>
    <p id="player1-score"></p>
    <p id="player2-score"></p>
</div>
<!-- Instruction for keyboard movement -->
<p>Use W A S D to move your cat.</p>
<!-- Buttons for using power-ups -->
<button class="pixel-button" id="useLaser" style="display:none"><img src="images/laserpointer.png" alt="Laserpointer" style="height:24px;vertical-align:middle;"> <span id="laserCount"></span></button>
<button class="pixel-button" id="useWool" style="display:none"><img src="images/wool.png" alt="Wool" style="height:24px;vertical-align:middle;"> <span id="woolCount"></span></button>
<button class="pixel-button" id="useMilk" style="display:none"><img src="images/Milk.png" alt="Milk" style="height:24px;vertical-align:middle;"> <span id="milkCount"></span></button>
<br>
<!-- Reset button to restart the game -->
<button class="pixel-button" id="resetGame">Reset Game</button>
<script src="js/game.js"></script>
<script src="js/powerup_buttons.js"></script>
<script>
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");
    if (!sessionId || !playerId) {
        alert("Session not found. Redirecting...");
        window.location.href = "index.php";
    }
</script>
<?php include 'templates/footer.php'; ?>