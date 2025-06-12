$(document).ready(function() {
    // Automatically clear the game state file upon load
    function clearGameState() {
        $.ajax({
            url: 'api/reset_game.php',
            method: 'POST'
        });
    }
    clearGameState();

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
        $.getJSON('data/game_state.json', function (state) {
            const p1 = state.players.player1.name && state.players.player1.name.trim();
            const p2 = state.players.player2.name && state.players.player2.name.trim();
            if (p1 && p2) {
                window.location.href = 'game.php';
            } else {
                $('#nameSavedMsg').text("Both player names must be entered before starting the game.");
            }
        }).fail(function() {
            $('#nameSavedMsg').text("Unable to load game state.");
        });
    });

    const cats = document.querySelectorAll('.cat');
    const prevBtn = document.getElementById('prev');
    const nextBtn = document.getElementById('next');
    const skinName = document.getElementById('skinName');
    let selectedIndex = 0;

    function updateSelection() {
        cats.forEach((cat, i) => {
            cat.classList.toggle('active', i === selectedIndex);
        });
        skinName.textContent = cats[selectedIndex].dataset.skin;
    }

    prevBtn.addEventListener('click', () => {
        selectedIndex = (selectedIndex - 1 + cats.length) % cats.length;
        updateSelection();
    });

    nextBtn.addEventListener('click', () => {
        selectedIndex = (selectedIndex + 1) % cats.length;
        updateSelection();
    });

    updateSelection();
});
