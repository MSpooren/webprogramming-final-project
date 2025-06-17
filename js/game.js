// game.js

console.log("✅ game.js loaded");

// Load game state from server and update UI
function loadGameState() {
    const sessionId = localStorage.getItem("sessionId");
    // Fetch current game state from server
    $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
        const lastTurn = localStorage.getItem("lastTurn");
        // Reset move buffer if a new turn has started
        if (lastTurn === null || parseInt(lastTurn) !== state.turn) {
            moveBuffer = [];
            localStorage.setItem("lastTurn", state.turn);
        }
        // Update UI components based on the game state
        renderGrid(state);
        updateTurnIndicator(state.turn, state);
        updateTurnCounter(state.turnCounter); 
        updateInventory(state);
        updateScoreboard(state);
    });
}

// Renders the game grid with players, obstacles, couch, and mice
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

                    // Show indicator for current player
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

            // Render couch
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

            // Render obstacles
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

// Updates the turn indicator with either a win message or whose turn it is
function updateTurnIndicator(turnId, state) {
    if (state.winner) {
        let msg = "";
        if (state.winner === "draw") {
            msg = "It's a draw!";
        } else {
            const winnerName = state.players?.[state.winner]?.name || `Player ${state.winner}`;
            msg = winnerName + " wins!";
        }
        $("#turn-indicator").text(msg);
        return;
    }
    const playerId = localStorage.getItem("playerId");
    $("#turn-indicator").text(parseInt(playerId) === turnId ? "Your turn!" : "Waiting for opponent...");
}

// Displays the current turn number
function updateTurnCounter(turnCounter) {
    $("#turn-counter").text("Turn: " + (turnCounter || 1));
}

// Updates the inventory UI for the current player
function updateInventory(state) {
    const playerId = localStorage.getItem("playerId");
    const items = state.players[playerId].inventory || [];
    $("#inventory").text("Inventory: " + items.join(", "));
}

// Displays the score of each player based on the couch_counter
function updateScoreboard(state) {
    const p1 = state.couch_counter?.["1"] || 0;
    const p2 = state.couch_counter?.["2"] || 0;
    const n1 = state.players?.["1"]?.name || "Player 1";
    const n2 = state.players?.["2"]?.name || "Player 2";
    $("#player1-score").text(n1 + ": " + p1);
    $("#player2-score").text(n2 + ": " + p2);
}

let moveBuffer = []; // Tracks moves made in the current turn

// Sends a move to the server if it's the player's turn and they haven't exceeded move limit
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
    const keyDown = {};
    const keyDelay = 200;

    // Laserpointer power-up button
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

    // Wool power-up button with directional input
    $("#useWool").off("click").on("click", function () {
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");
    const direction = prompt("Welke richting? (up/down/left/right)");

    let dx = 0, dy = 0;
    switch (direction) {
        case "up": dy = -3; break;
        case "down": dy = 3; break;
        case "left": dx = -3; break;
        case "right": dx = 3; break;
        default:
            alert("Ongeldige richting");
            return;
    }

    $.ajax({
        url: "php/use_powerup.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            sessionId,
            playerId,
            item: "wool",
            direction: { x: dx, y: dy }
        }),
        success: function (res) {
            console.log("Wool response:", res);
            loadGameState();
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", status, error);
        }
    });
});

// Milk power-up button with diagonal direction input
$("#useMilk").on("click", function () {
    const sessionId = localStorage.getItem("sessionId");
    const playerId = localStorage.getItem("playerId");

    $.getJSON("php/load_state.php?sessionId=" + sessionId, function (state) {
        if (state.turn.toString() !== playerId.toString()) {
            alert("It is not your turn!");
            return;
        }

        const dir = prompt("Welke diagonaal? (↘, ↙, ↖, ↗)");
        let dx = 0, dy = 0;

        if (dir === "↘") {
            dx = 1; dy = 1;
        } else if (dir === "↙") {
            dx = -1; dy = 1;
        } else if (dir === "↖") {
            dx = -1; dy = -1;
        } else if (dir === "↗") {
            dx = 1; dy = -1;
        } else {
            alert("Ongeldige richting");
            return;
        }

        $.ajax({
            url: "php/use_powerup.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                sessionId,
                playerId,
                item: "milk",
                direction: { x: dx, y: dy }
            }),
            success: function (res) {
                if (!res.success) {
                    alert(res.error || "Fout bij gebruik van melk.");
                    return;
                }
                alert("Melk gebruikt!");
                loadGameState();
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    });
});



    // Player movement controls (WASD)
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

    // Reset key state on keyup
    $(document).on("keyup", function (e) {
        keyDown[e.key.toLowerCase()] = false;
    });

    // Game reset button logic
    $('#resetGame').click(function() {
        const sessionId = localStorage.getItem("sessionId");
        if (!sessionId) return;
        if (!confirm('Are you sure you want to reset the game?')) return;
        $.get('php/reset_game.php', { sessionId: sessionId }, function(response) {
            let res;
            try { res = JSON.parse(response); } catch (e) { res = {success:false,message:response}; }
            alert(res.message);
            if (res.success) {
                // Clear local data and return to index
                localStorage.removeItem("sessionId");
                localStorage.removeItem("playerId");
                
            }
            window.location.href = "index.php";
        });
    });

    // Periodically refresh game state
    setInterval(loadGameState, 2000);
    loadGameState(); // Initial load
});
