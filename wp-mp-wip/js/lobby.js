
document.addEventListener("DOMContentLoaded", function () {
    const playerListDiv = document.getElementById("playerList");
    const startButton = document.getElementById("startGameBtn");

    function fetchLobbyStatus() {
        fetch(`api/get_lobby_status.php?game_name=${gameName}`)
            .then(response => response.json())
            .then(data => {
                const players = data.players || {};
                const started = data.started || false;

                playerListDiv.innerHTML = `
                    <p>Host: <strong>${players.host || 'Waiting...'}</strong></p>
                    <p>Guest: <strong>${players.guest || 'Waiting...'}</strong></p>
                `;

                if (playerRole === 'host' && players.host && players.guest && !started) {
                    startButton.style.display = "block";
                }

                if (started) {
                    window.location.href = `game.php?mode=play&game_name=${gameName}`;
                }
            });
    }

    fetchLobbyStatus();
    setInterval(fetchLobbyStatus, 2000);

    if (startButton) {
        startButton.addEventListener("click", function () {
            fetch("api/start_game.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ game_name: gameName })
            });
        });
    }
});
