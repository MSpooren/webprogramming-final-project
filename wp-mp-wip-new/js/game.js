// js/game.js
let playerId = null;

function loadGameState() {
    const sessionId = localStorage.getItem("sessionId");
    $.getJSON("php/load_state.php?sessionId=" + sessionId, function (data) {
        renderGrid(data);
        updateTurnIndicator(data.turn);
    });
}

function sendMove(dx, dy) {
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");

    $.ajax({
        url: "php/save_state.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            sessionId: sessionId,
            playerId: playerId,
            move: { x: dx, y: dy }
        }),
        success: function () {
            loadGameState();
        }
    });
}

function registerPlayer(name, skin, id) {
    playerId = id;
    $.ajax({
        url: "php/save_players.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({ playerId, name, skin }),
        success: function () {
            console.log("Player registered.");
            loadGameState();
        }
    });
}

function renderGrid(state) {
    // Basic 7x7 grid
    const grid = $("#grid");
    grid.empty();
    for (let y = 0; y < 7; y++) {
        for (let x = 0; x < 7; x++) {
            let tile = $("<div>").addClass("tile");

            for (let pid in state.players) {
                let p = state.players[pid];
                if (p.x === x && p.y === y) {
                    tile.text(p.name[0]); // Simple display
                }
            }

            grid.append(tile);
        }
    }
}

function updateTurnIndicator(turn) {
    $("#turn-indicator").text(`Turn: Player ${turn}`);
}
