<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cat Couch Clash</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Step 1: Title -->
<div id="title-screen">
    <h1>🐾 Cat Couch Clash 🛋️</h1>
    <button class="pixel-button" id="start-btn">Start</button>
</div>

<!-- Step 2: Player Setup -->
<div id="setup-screen">
    <h2>Enter your cat's name!</h2>
    <input type="text" id="name" placeholder="Cat Name :3" required><br><br>

    <div id="skin-selector-wrapper">
        <button class="pixel-button" id="prev-skin">←</button>

        <div id="skin-strip">
            <?php
            $catTypes = [
                "tile01_white" => "White Cat",
                "tile02_tuxedo" => "Tuxedo Cat",
                "tile03_ginger" => "Ginger Cat",
                "tile04_tabby" => "Tabby Cat",
                "tile05_siamese" => "Siamese Cat",
            ];

            $images = glob("images/tile*.png");

            foreach ($images as $img) {
                $basename = basename($img, ".png");
                $catName = isset($catTypes[$basename]) ? $catTypes[$basename] : "Unknown Cat";

                echo "<div class='skin-container'>";
                echo "<img src='$img' class='skin-option' data-skin='$basename' data-catname='$catName'>";
                echo "</div>";
            }
            ?>
        </div>

        <button class="pixel-button" id="next-skin">→</button>
    </div>

    <div id="selected-skin-label">
        Selected skin: <span id="selected-skin-name">None</span>
        <input type="hidden" id="skin" name="skin" value="">
    </div>

    <button class="pixel-button" id="ready-btn">Ready!</button>
</div>

<!-- Step 3: Waiting -->
<div id="waiting-screen">
    <h3>Waiting for your opponent...</h3>
    <p id="status-msg">Checking status...</p>
</div>

<script>
    let selectedSkin = null;
    let sessionId = null;
    let playerId = null;

    $("#title-screen").show();

    $("#start-btn").on("click", function () {
        $("#title-screen").hide();
        $("#setup-screen").show();
    });

    $("#skin-selection img").on("click", function () {
        $("#skin-selection img").removeClass("selected");
        $(this).addClass("selected");
        selectedSkin = $(this).data("skin");
        $("#skin").val(selectedSkin);
    });

    $("#ready-btn").on("click", function () {
        const playerName = $("#name").val().trim();
        const pickedSkin = $("#skin").val(); // ✅ renamed

        if (!playerName || !pickedSkin) {
            alert("Please enter a name and select a skin.");
            return;
        }

        $.getJSON("php/assign_player.php", function (data) {
            localStorage.setItem("sessionId", data.sessionId);
            localStorage.setItem("playerId", data.playerId);

            $.ajax({
                url: "php/save_players.php",
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    sessionId: data.sessionId,
                    playerId: data.playerId,
                    name: playerName,
                    skin: pickedSkin // ✅ fixed
                }),
                success: function () {
                    $("#setup-screen").hide();
                    $("#waiting-screen").show();
                    waitForOpponent();
                }
            });
        });
    });

    function waitForOpponent() {
        const sessionId = localStorage.getItem("sessionId");
        const playerId = localStorage.getItem("playerId");

        const interval = setInterval(() => {
            $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
                const hasP1 = state.players["1"].name;
                const hasP2 = state.players["2"].name;

                if (hasP1 && hasP2) {
                    clearInterval(interval);
                    if (playerId === "2") {
                        $.get("php/start_game.php?sessionId=" + sessionId, function () {
                            window.location.href = "game.php";
                        });
                    } else {
                        window.location.href = "game.php";
                    }
                } else {
                    const missing = !hasP1 ? "Player 1" : "Player 2";
                    $("#status-msg").text("Waiting for " + missing + "...");
                }
            });
        }, 1500);
    }
</script>
<script src="js/skin_carousel.js"></script>

</body>
</html>
