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
<!-- Inventory list UI -->
<ul id="inventory"></ul>
<!-- Buttons for using power-ups -->
<button class="pixel-button" id="useLaser">Use Laserpointer</button>
<button class="pixel-button" id="useWool">Use Wool</button>
<button class="pixel-button" id="useMilk">Use Milk</button>
<br>
<!-- Reset button to restart the game -->
<button class="pixel-button" id="resetGame">Reset Game</button>
<script src="js/game.js"></script>
<script>
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");
    if (!sessionId || !playerId) {
        alert("Session not found. Redirecting...");
        window.location.href = "index.php";
    }
</script>
<?php include 'templates/footer.php'; ?>