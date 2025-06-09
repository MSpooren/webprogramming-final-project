<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grid Move Game</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="js/ajax.js"></script>
    <script src="js/game.js"></script>
    <script>
        $(document).ready(function() {
            // Check if names are already set and game has started.
            $getJSON('api/load_status.php', function (state) {
                const p1 = state.players.player1.name;
                const p2 = state.players.player2.name;
                const started = state.game_started;

                if (!p1 || !p2 || !started) {
                    $('#nameSetup').show();
                    $('#gameContainer').hide();
                } else {
                    $('#nameSetup').hide();
                    $('#gameContainer').show();
                }
            });

            $('#nameForm').submit(function (e) {
                e.preventDefault();
                const player = $('player').val();
                const name = $('#catName').val().trim();

                if (name.length > 0) {
                    savePlayerName(player, name);
                } else {
                    alert("Please enter a valid name.");
                }
            });

            $('#startGameBtn').click(function () {
                $.ajax({
                    url: 'api/save_status.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ startGame: true}),
                    success: function (res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            alert(res.error || "Unable to start game.");
                        }
                    }
                });
            });
        });
    </script>
</head>
<body>
    <!-- Name setup form -->
    <div id="nameSetup" style="display:none;">
        <h2>Enter your cat name!</h2>
        <form id="nameForm">
            <label>Player:</label>
            <select id="player">
                <option value="player1">Player 1</option>
                <option value="player2">Player 2</option>
            </select>
            <br><br>
            <label>Cat Name:</label>
            <label type="text" id="catName" required>
            <br><br>
            <button type="submit">Save Name</button>
        </form>
        <button id="startGameBtn">Start Game</button>
    </div>

    <!-- Game grid -->
    <div class="container" id="gameContainer" style="display:none;">
        <h1>Grid Move Game</h1>
        <p>Move your player on the 5x5 grid. Use the <strong>WASD keys</strong> to move. Turns left: <span id="turnsLeft">20</span></p>
        <div id="grid"></div>
        <p id="result"></p>
        <button id="restartBtn" style="display:none;">Restart Game</button>
    </div>
</body>
</html>
