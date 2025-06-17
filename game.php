<!-- game.php -->
<?php 
$bodyClass = 'game-background';
include 'templates/header.php'; 
?>
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