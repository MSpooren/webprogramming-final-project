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
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            text-align: center;
            font-family: sans-serif;
            padding: 40px;
        }
        #title-screen, #setup-screen, #waiting-screen {
            display: none;
        }
        #skin-selection img {
            width: 48px;
            height: 48px;
            margin: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 6px;
        }
        #skin-selection img.selected {
            border-color: gold;
            box-shadow: 0 0 5px gold;
        }
    </style>
</head>
<body>

<!-- Step 1: Title -->
<div id="title-screen">
    <h1>🐾 Cat Couch Clash 🛋️</h1>
    <button id="start-btn">Start</button>
</div>

<!-- Step 2: Player Setup -->
<div id="setup-screen">
    <h2>Enter your cat's info</h2>
    <input type="text" id="name" placeholder="Your name" required><br><br>

    <div id="skin-selection">
        <?php
        $images = glob("images/tile*.png");
        foreach ($images as $img) {
            $basename = basename($img, ".png");
            echo "<img src='$img' alt='$basename' data-skin='$basename'>";
        }
        ?>
    </div>
    <input type="hidden" id="skin"><br><br>

    <button id="ready-btn">I'm Ready</button>
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
    const selectedSkin = $("#skin").val();

    if (!playerName || !selectedSkin) {
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
                skin: selectedSkin
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

        const interval = setInterval(() => {
            $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
                if (state.players["1"].name && state.players["2"].name) {
                    clearInterval(interval); // ✅ stop polling
                    window.location.href = "game.php";
                } else {
                    const missing = (!state.players["1"].name) ? "Player 1" : "Player 2";
                    $("#status-msg").text("Waiting for " + missing + "...");
                }
            });
        }, 1500);
    }   
</script>

</body>
</html>
