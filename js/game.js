function loadGameState() {
    const sessionId = localStorage.getItem("sessionId");
    $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
        renderGrid(state);
        updateTurnIndicator(state.turn);
        updateInventory(state);
    });
}

function renderGrid(state) {
    const grid = $("#grid");
    grid.empty();

    for (let y = 0; y < 7; y++) {
        for (let x = 0; x < 7; x++) {
            const tile = $("<div>").addClass("tile");

            // Render players
            for (let pid in state.players) {
                const p = state.players[pid];
                if (p.x === x && p.y === y) {
                    const isCurrentPlayer = parseInt(pid) === parseInt(localStorage.getItem("playerId"));

                    const container = $("<div>").css({
                        position: "relative",
                        width: "100%",
                        height: "100%"
                    });

                    if (isCurrentPlayer) {
                        const indicator = $("<div>").addClass("player-indicator");
                        container.append(indicator);
                    }

                    const img = $("<img>")
                        .attr("src", "images/" + p.skin + ".png")
                        .css({
                            width: "100%",
                            height: "100%",
                            objectFit: "contain",
                            position: "relative",
                            zIndex: 5
                        })
                        .on("error", function () {
                            $(this).attr("src", "images/default.png");
                        });

                    container.append(img);
                    tile.append(container);
                }
            }

            // Render mice
            if (state.mice) {
                for (let m of state.mice) {
                    if (m.x === x && m.y === y) {
                        const mouseImg = $("<img>")
                            .attr("src", "images/mouse.png")
                            .css({
                                width: "100%",
                                height: "100%",
                                objectFit: "cover",
                                position: "absolute",
                                top: 0,
                                left: 0,
                                transform: `rotate(${m.direction}deg)`
                            });
                        tile.append(mouseImg);
                    }
                }
            }

            grid.append(tile);
        }
    }
}

function updateTurnIndicator(turnId) {
    const playerId = localStorage.getItem("playerId");
    if (parseInt(playerId) === turnId) {
        $("#turn-indicator").text("Your turn!");
    } else {
        $("#turn-indicator").text("Waiting for opponent...");
    }
}

function updateInventory(state) {
    const playerId = localStorage.getItem("playerId");
    const player = state.players[playerId];
    const items = player.inventory || [];
    $("#inventory").text("Inventory: " + items.join(", "));
}

function sendMove(dx, dy) {
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");

    $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
        if (parseInt(playerId) !== state.turn) {
            alert("It's not your turn!");
            return;
        }

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
    });
}

$("#useLaser").on("click", function () {
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");

    $.ajax({
        url: "php/use_powerup.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            sessionId: sessionId,
            playerId: playerId,
            item: "laserpointer"
        }),
        success: function (res) {
            if (res.success) {
                alert("Laserpointer geactiveerd! De tegenstander zal jouw richting op bewegen.");
            } else {
                alert("Fout bij powerup: " + res.error);
            }
            loadGameState();
        }
    });
});


// WASD key movement
$(document).on("keydown", function (e) {
    const key = e.key.toLowerCase();
    if (key === "w") sendMove(0, -1);
    if (key === "s") sendMove(0, 1);
    if (key === "a") sendMove(-1, 0);
    if (key === "d") sendMove(1, 0);
});

// Auto-update the game every 2 seconds
setInterval(loadGameState, 2000);
loadGameState();
