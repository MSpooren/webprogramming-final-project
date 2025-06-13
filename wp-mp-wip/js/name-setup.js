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
            // Show only selectedIndex-1, selectedIndex, selectedIndex+1 (circular)
            let prev = (selectedIndex - 1 + cats.length) % cats.length;
            let center = selectedIndex;
            let next = (selectedIndex + 1) % cats.length;
            if (i === prev || i === center || i === next) {
                cat.style.display = '';
            } else {
                cat.style.display = 'none';
            }
            cat.classList.toggle('active', i === center);
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

    selectSkinBtn.addEventListener('click', function() {
        // Save selected skin for the selected player
        const player = document.getElementById('player').value;
        const skin = cats[selectedIndex].dataset.skin;
        $.ajax({
            url: 'api/save_status.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'set_skin',
                player: player,
                skin: skin
            }),
            success: function(response) {
                // Optionally show a message or visual feedback
                $('#skinSelectedMsg').text("Skin selected!");
            }
        });
    });  
    
    updateSelection();
});
