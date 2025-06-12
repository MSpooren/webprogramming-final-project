// Turn logic, animations, jQuery and AJAX
// basic grid logic with player movement and item collection
// upon page load, initialize the game grid and player
document.addEventListener('DOMContentLoaded', function() {
    const gridSize = 7;
    let turns = 20;
    let player = { x: 3, y: 3 };
    let player_skin = 'tile000.png'; // default skin

    // Add variables for player names and skins
    let player1_name = '';
    let player2_name = '';
    let player1_skin = '';
    let player2_skin = '';

    // Fetch player info from backend (example endpoint: api/get_players.php)
    function fetchPlayerInfo() {
        return fetch('api/get_players.php')
            .then(response => response.json())
            .then(data => {
                player1_name = data.player1.name;
                player2_name = data.player2.name;
                player1_skin = data.player1.skin;
                player2_skin = data.player2.skin;
                // Set default player skin to player1's skin
                player_skin = player1_skin || player_skin;
            })
            .catch(() => {
                // fallback to defaults if fetch fails
                player1_name = 'Player 1';
                player2_name = 'Player 2';
                player1_skin = player_skin;
                player2_skin = player_skin;
            });
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    const currentPlayer = urlParams.get("player"); // bijv. "player1"

    // returns array with dictionarys with x and y coordinated and a picked status
    function generateRandomItems(count) {
        const positions = new Set();
        // Avoid player's starting position
        positions.add(`${player.x},${player.y}`);
        let itemsArr = [];
        while (itemsArr.length < count) {
            let x = Math.floor(Math.random() * gridSize);
            let y = Math.floor(Math.random() * gridSize);
            let key = `${x},${y}`;
            if (!positions.has(key)) {
                // Add random rotation (0, 90, 180, 270)
                let rotation = [0, 90, 180, 270][Math.floor(Math.random() * 4)];
                itemsArr.push({ x, y, picked: false, rotation });
                positions.add(key);
            }
        }
        return itemsArr;
    }
   
    let items = generateRandomItems(3);
    let itemsCollected = 0;
    const gridDiv = document.getElementById('grid');
    const result = document.getElementById('result');
    const turnsLeft = document.getElementById('turnsLeft');
    const restartBtn = document.getElementById('restartBtn');

    // render the game grid as a table every turn
    function renderGrid() {
        let html = '<table class="game-grid">';
        for (let y = 0; y < gridSize; y++) {
            html += '<tr>';
            for (let x = 0; x < gridSize; x++) {
                let cellContent = '';
                if (player.x === x && player.y === y) {
                    cellContent = `<img src="images/${player_skin}" alt="Player" style="width:64px;height:64px;vertical-align:middle;">`;
                }
                let itemHere = items.find(item => item.x === x && item.y === y && !item.picked);
                if (itemHere) {
                    // Add rotation style
                    cellContent += `<img src="images/mouse.png" alt="Mouse" style="width:64px;height:64px;vertical-align:middle;transform:rotate(${itemHere.rotation}deg);">`;
                }
                // Add coordinates in light gray for debugging
                cellContent += `<div style=\"font-size:0.5em;color:#bbb;\">x${x},y${y}</div>`;
                html += `<td class="${player.x === x && player.y === y ? 'player' : ''}">${cellContent}</td>`;
            }
            html += '</tr>';
        }
        html += '</table>';
        gridDiv.innerHTML = html;
    }

    //turn counter
    function updateTurns() {
        turnsLeft.textContent = turns;
    }

    // Helper: get movement delta from rotation
    function getDirection(rotation) {
        switch (rotation) {
            case 0: return { dx: 0, dy: -1 };      // North
            case 90: return { dx: 1, dy: 0 };      // East
            case 180: return { dx: 0, dy: 1 };     // South
            case 270: return { dx: -1, dy: 0 };    // West
            default: return { dx: 0, dy: 0 };
        }
    }

    // Randomize rotation for all items
    function randomizeItemsRotation() {
        items.forEach(item => {
            if (!item.picked) {
                item.rotation = [0, 90, 180, 270][Math.floor(Math.random() * 4)];
            }
        });
    }

    // Move items one tile in their rotation direction if possible
    function moveItems() {
        items.forEach(item => {
            if (item.picked) return;
            const { dx, dy } = getDirection(item.rotation);
            const nx = item.x + dx;
            const ny = item.y + dy;
            // Check bounds and avoid player position
            if (
                nx >= 0 && nx < gridSize &&
                ny >= 0 && ny < gridSize &&
                !(player.x === nx && player.y === ny)
            ) {
                // Avoid overlapping with other items
                let occupied = items.some(other =>
                    !other.picked && other !== item && other.x === nx && other.y === ny
                );
                if (!occupied) {
                    item.x = nx;
                    item.y = ny;
                }
            }
        });
    }

    function movePlayer(dir) {
        if (turns <= 0) return;
        let nx = player.x, ny = player.y;
        // Update player position based on direction according to the grid boundaries
        if (dir === 'up' && ny > 0) ny--;
        if (dir === 'down' && ny < gridSize - 1) ny++;
        if (dir === 'left' && nx > 0) nx--;
        if (dir === 'right' && nx < gridSize - 1) nx++;
        if (nx !== player.x || ny !== player.y) {
            player.x = nx; player.y = ny;
            turns--;
            // Check for item pickup
            let item = items.find(item => item.x === player.x && item.y === player.y && !item.picked);
            if (item) {
                item.picked = true;
                itemsCollected++;
                result.textContent = `You picked up an item! Total: ${itemsCollected}`;
            }
            // Move items after player moves
            moveItems();
            randomizeItemsRotation();
            renderGrid();
            updateTurns();
            if (itemsCollected === items.length) {
                result.textContent = 'You collected all items! You win!';
                if (restartBtn) restartBtn.style.display = 'inline';
            } else if (turns === 0) {
                result.textContent = 'Game over! Out of turns.';
                if (restartBtn) restartBtn.style.display = 'inline';
            }
        }
    }

    // WASD and arrow key controls
    document.addEventListener('keydown', function(e) {
        let dir = null;
        if (e.key === 'w' || e.key === 'W' || e.key === 'ArrowUp') dir = 'up';
        if (e.key === 'a' || e.key === 'A' || e.key === 'ArrowLeft') dir = 'left';
        if (e.key === 's' || e.key === 'S' || e.key === 'ArrowDown') dir = 'down';
        if (e.key === 'd' || e.key === 'D' || e.key === 'ArrowRight') dir = 'right';
        if (dir) {
            e.preventDefault();
            movePlayer(dir);
        }
    });
    // upon restart, reset the game state
    restartBtn.addEventListener('click', function() {
        player = { x: 2, y: 2 };
        turns = 20;
        items = generateRandomItems(3);
        itemsCollected = 0;
        renderGrid();
        updateTurns();
        result.textContent = '';
        restartBtn.style.display = 'none';
    });

    newGameBtn.addEventListener('click', function() {
        window.location.href = 'index.php';
        $.ajax({
            url: 'api/reset_game.php',
            method: 'POST'
        });
    });  
    fetchPlayerInfo().then(() => {
        renderGrid();
        updateTurns();
    });
});

function pollGameState() {
  $.getJSON("data/game.json", function(data) {
    const player1 = data.players.player1;
    const player2 = data.players.player2;

    // Zet beide spelers op de juiste plek op het grid
    renderPlayer("player1", player1.x, player1.y, player1.skin);
    renderPlayer("player2", player2.x, player2.y, player2.skin);

    // Toon of het jouw beurt is
    if (data.turn === currentPlayer) {
      enableControls();
    } else {
      disableControls();
    }
  });
}

setInterval(pollGameState, 1000); // elke seconde ophalen

function sendMove(direction) {
  $.post("api/move.php", {
    player: currentPlayer,
    direction: direction
  }, function(response) {
    console.log("Beweging verzonden:", response);
    pollGameState(); // ververs direct na eigen zet
  });
}

