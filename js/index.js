/* JS for index.php excluding carousel script */
let selectedSkin = null;
let sessionId = null;
let playerId = null;

$("#title-screen").show();

$("#start-btn").on("click", function () {
    $("#title-screen").hide();
    $("#setup-screen").show();
});

$("#help-btn").on("click", function () {
    $("#title-screen").hide();
    $("#help-screen").show();
});

$("#credits-btn").on("click", function () {
    $("#title-screen").hide();
    $("#credits-screen").show();
});

$("#menu-c-btn").on("click", function () {
    $("#credits-screen").hide();
    $("#title-screen").show();
});

$("#menu-h-btn").on("click", function () {
    $("#help-screen").hide();
    $("#title-screen").show();
});

$("#menu-s-btn").on("click", function () {
    $("#setup-screen").hide();
    $("#title-screen").show();
});

$("#skin-selection img").on("click", function () {
    $("#skin-selection img").removeClass("selected");
    $(this).addClass("selected");
    selectedSkin = $(this).data("skin");
    $("#skin").val(selectedSkin);
});

$("#ready-btn").on("click", function () {
    const playerName = $("#name").val().trim();
    const pickedSkin = $("#skin").val();

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
                skin: pickedSkin
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