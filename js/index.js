/* JS for index.php excluding carousel script */

// Track the selected skin and session/player IDs
let selectedSkin = null;
let sessionId = null;
let playerId = null;

// Show the title screen on page load
$("#title-screen").show();

// Start button -> go to setup
$("#start-btn").on("click", function () {
    $("#title-screen").hide();
    $("#setup-screen").show();
});

// Help button -> go to help screen
$("#help-btn").on("click", function () {
    $("#title-screen").hide();
    $("#help-screen").show();
});

// Credits button -> go to credits screen
$("#credits-btn").on("click", function () {
    $("#title-screen").hide();
    $("#credits-screen").show();
});

// Menu button from credits -> return to title screen
$("#menu-c-btn").on("click", function () {
    $("#credits-screen").hide();
    $("#title-screen").show();
});

// Menu button from help -> return to title screen
$("#menu-h-btn").on("click", function () {
    $("#help-screen").hide();
    $("#title-screen").show();
});

// Menu button from setup -> return to title screen
$("#menu-s-btn").on("click", function () {
    $("#setup-screen").hide();
    $("#title-screen").show();
});

// Handle skin selection
$("#skin-selection img").on("click", function () {
    // Remove highlight from all skins and highlight selected one
    $("#skin-selection img").removeClass("selected");
    $(this).addClass("selected");
    // Save selected skin value
    selectedSkin = $(this).data("skin");
    $("#skin").val(selectedSkin);
});

// Ready button -> validate and send player data
$("#ready-btn").on("click", function () {
    const playerName = $("#name").val().trim();
    const pickedSkin = $("#skin").val();

    // Validate inputs
    if (!playerName || !pickedSkin) {
        alert("Please enter a name and select a skin.");
        return;
    }

    // Request new player assignment from backend
    $.getJSON("php/assign_player.php", function (data) {
        // Store session and player info in local storage
        localStorage.setItem("sessionId", data.sessionId);
        localStorage.setItem("playerId", data.playerId);

        // Send player name and skin to server
        $.ajax({
            url: "php/save_players.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                sessionId: data.sessionId,
                playerId: data.playerId,
                name: playerName,
                skin: pickedSkin
            }),
            success: function () {
                // Switch to waiting screen after success
                $("#setup-screen").hide();
                $("#waiting-screen").show();
                waitForOpponent();
            }
        });
    });
});

// Repeatedly check if both players have joined
function waitForOpponent() {
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");

    const interval = setInterval(() => {
        // Poll current game state
        $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
            const hasP1 = state.players["1"].name;
            const hasP2 = state.players["2"].name;

            // Both players are present -> proceed to game
            if (hasP1 && hasP2) {
                clearInterval(interval);
                // If player 2 just joined, trigger game start
                if (playerId === "2") {
                    $.get("php/start_game.php?sessionId=" + sessionId, function () {
                        window.location.href = "game.php";
                    });
                } else {
                    window.location.href = "game.php";
                }

            } else {
                // Update waiting message baed on which player is missing
                const missing = !hasP1 ? "Player 1" : "Player 2";
                $("#status-msg").text("Waiting for " + missing + "...");
            }
        });
    }, 1500); // Check every 1.5 seconds
}
