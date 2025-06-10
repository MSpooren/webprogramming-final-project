$(document).ready(function() {
    // Check if names are already set and game has started.
    $.getJSON('api/load_status.php', function (state) {
        const p1 = state.players.player1.name;
        const p2 = state.players.player2.name;
        const started = state.game_started;

        if (p1 && p2 && started) {
            window.location.href = 'index.php';
        }
    });

    $('#nameForm').submit(function (e) {
        e.preventDefault();
        const player = $('#player').val();
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
                // Defensive: handle both string and object
                if (typeof res === 'string') {
                    try { res = JSON.parse(res); } catch (e) { res = {}; }
                }
                if (res.success) {
                    window.location.href = 'index.php';
                } else {
                    alert((res && res.error) || "Unable to start game.");
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error: ' + error);
            }
        });
    });
});
