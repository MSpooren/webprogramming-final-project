document.addEventListener("DOMContentLoaded", function () {
    const startBtn = document.getElementById("start-button");
    const mainMenu = document.getElementById("main-menu");
    const gameSelection = document.getElementById("game-selection");

    startBtn.addEventListener("click", function () {
        mainMenu.style.display = "none";
        gameSelection.style.display = "block";
    });

    document.getElementById("create-button").addEventListener("click", function () {
        window.location.href = "game.php?mode=create";
    });

    document.getElementById("join-button").addEventListener("click", function () {
        window.location.href = "game.php?mode=join";
    });
});