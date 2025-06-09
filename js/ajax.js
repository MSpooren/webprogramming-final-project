function savePlayerName(player, name) {
    $.ajax({
        url: 'api/save_status.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: "set_name", player: player, name: name }),
        success: function(response) {
            alert("Name saved successfully!");
        }
    });
}

function movePlayer(player, direction) {
    $.ajax({
        url: 'api/save_status.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: "move", player: player, direction: direction }),
        success: function (response) {
            if (response.success) {
                // Refresh grid or UI
            } else {
                alert(response.error);
            }
        }
    });
}

function attack(player, item) {
    $.ajax({
        url: 'api/save_status.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: "attack", player: player, item: item }),
        success: function (response) {
            if (!response.success) alert(response.error);
        }
    });
}
