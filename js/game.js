// game.js

console.log("✅ game.js loaded");

function loadGameState() {
    const sessionId = localStorage.getItem("sessionId");
    $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
        const lastTurn = localStorage.getItem("lastTurn");
        if (lastTurn === null || parseInt(lastTurn) !== state.turn) {
            moveBuffer = [];
            localStorage.setItem("lastTurn", state.turn);
        }
        renderGrid(state);
        updateTurnIndicator(state.turn);
        updateInventory(state);
        updateScoreboard(state);
    });
}

function renderGrid(state) {
    const grid = $("#grid");
    grid.empty();

    for (let y = 0; y < 7; y++) {
        for (let x = 0; x < 7; x++) {
            const tile = $("<div>").addClass("tile");

            // Players
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

            // Couch
            if (state.couch && state.couch.x === x && state.couch.y === y) {
                const couchImg = $("<img>")
                    .attr("src", "images/couch.png")
                    .css({
                        width: "100%",
                        height: "100%",
                        objectFit: "cover",
                        position: "absolute",
                        top: 0,
                        left: 0,
                    })
                    .on("error", function () {
                        $(this).attr("src", "images/default.png");
                    });
                tile.append(couchImg);
            }

            // Obstacles
            if (state.obstacles) {
                for (let obj of state.obstacles) {
                    if (obj.x === x && obj.y === y) {
                        const objImg = $("<img>")
                            .attr("src", "images/" + obj.type + ".png")
                            .css({
                                width: "100%",
                                height: "100%",
                                objectFit: "cover",
                                position: "absolute",
                                top: 0,
                                left: 0,
                                zIndex: 1
                            })
                            .on("error", function () {
                                $(this).attr("src", "images/default.png");
                            });
                        tile.append(objImg);
                    }
                }
            }

            // Mice
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
    $("#turn-indicator").text(parseInt(playerId) === turnId ? "Your turn!" : "Waiting for opponent...");
}

function updateInventory(state) {
    const playerId = localStorage.getItem("playerId");
    const items = state.players[playerId].inventory || [];
    $("#inventory").text("Inventory: " + items.join(", "));
}

function updateScoreboard(state) {
    const p1 = state.couch_counter?.["1"] || 0;
    const p2 = state.couch_counter?.["2"] || 0;
    const n1 = state.players?.["1"]?.name || "Player 1";
    const n2 = state.players?.["2"]?.name || "Player 2";
    $("#player1-score").text(n1 + ": " + p1);
    $("#player2-score").text(n2 + ": " + p2);
}

let moveBuffer = [];

function sendMove(dx, dy) {
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");

    $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
        if (parseInt(playerId) !== state.turn) {
            alert("It's not your turn!");
            return;
        }

        const movesThisTurn = state.players[playerId].movesThisTurn || 0;
        if (movesThisTurn >= 2) {
            alert("You have already made 2 moves this turn.");
            return;
        }

        moveBuffer.push({ x: dx, y: dy });

        $.ajax({
            url: "php/save_state.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({ sessionId, playerId, move: { x: dx, y: dy } }),
            success: function () {
                if (moveBuffer.length >= 2) moveBuffer = [];
                loadGameState();
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                console.error("Response:", xhr.responseText);
            }
        });
    });
}

$(document).ready(function () {
    // Bind laserpointer usage
    $("#useLaser").on("click", function () {
        const sessionId = localStorage.getItem("sessionId");
        const playerId = localStorage.getItem("playerId");

        console.log("🔫 Laserpointer button clicked", { sessionId, playerId });

        $.ajax({
            url: "php/use_powerup.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({ sessionId, playerId, item: "laserpointer" }),
            success: function (res) {
                console.log("Laserpointer response:", res);
                alert(res.success ? "Laserpointer activated!" : "Laserpointer failed: " + res.error);
                loadGameState();
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    });

    // Movement
    const keyDown = {};
    const keyDelay = 200;

    $(document).on("keydown", function (e) {
        const key = e.key.toLowerCase();
        if (keyDown[key]) return;
        keyDown[key] = true;
        console.log("Key pressed:", key);

        let moved = false;
        if (key === "w") { sendMove(0, -1); moved = true; }
        if (key === "s") { sendMove(0, 1); moved = true; }
        if (key === "a") { sendMove(-1, 0); moved = true; }
        if (key === "d") { sendMove(1, 0); moved = true; }

        if (moved) setTimeout(() => { keyDown[key] = false; }, keyDelay);
    });

    $(document).on("keyup", function (e) {
        keyDown[e.key.toLowerCase()] = false;
    });

    setInterval(loadGameState, 2000);
    loadGameState();
});
