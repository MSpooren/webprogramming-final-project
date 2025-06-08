// Turn logic, animations, jQuery and AJAX
// basic grid logic with player movement and item collection
// upon page load, initialize the game grid and player
document.addEventListener('DOMContentLoaded', function() {
    const gridSize = 7;
    let turns = 20;
    let player = { x: 3, y: 3 };
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
                itemsArr.push({ x, y, picked: false });
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
    const controls = document.getElementById('controls');

    // render the game grid as a table every turn
    function renderGrid() {
        let html = '<table class="game-grid">';
        for (let y = 0; y < gridSize; y++) {
            html += '<tr>';
            for (let x = 0; x < gridSize; x++) {
                let cellContent = '';
                if (player.x === x && player.y === y) {
                    cellContent = 'P';
                }
                let itemHere = items.find(item => item.x === x && item.y === y && !item.picked);
                if (itemHere) {
                    cellContent += '🎁';
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
            renderGrid();
            updateTurns();
            if (itemsCollected === items.length) {
                result.textContent = 'You collected all items! You win!';
                if (restartBtn) restartBtn.style.display = 'inline';
                if (controls) controls.style.display = 'none';
            } else if (turns === 0) {
                result.textContent = 'Game over! Out of turns.';
                if (restartBtn) restartBtn.style.display = 'inline';
                if (controls) controls.style.display = 'none';
            }
        }
    }

    // WASD and arrow key controls
    document.addEventListener('keydown', function(e) {
        if (typeof controls !== 'undefined' && controls && controls.style.display === 'none') return;
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
        controls.style.display = 'block';
        restartBtn.style.display = 'none';
    });

    renderGrid();
    updateTurns();
});