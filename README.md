# Cats Couch Clash

A web-based game built with PHP and jQuery, about cats fighting over a couch.

<img src="images/background.png" alt="Game's background" width="500"/>

---

## Features

- **Backend:** PHP handles game logic, data processing, and state management.  
- **Frontend:** Uses jQuery for DOM manipulation, AJAX calls, and event handling.  
- **AJAX-powered:** Smooth asynchronous updates (e.g., user actions, game status) via `$.getJSON`, `$.ajax`. 


---


## How It Works

### Frontend (game.js):

Runs $(document).ready(...) on load.

Listens for user actions (clicks, input, etc.).

Sends AJAX requests via $.getJSON or $.ajax to PHP endpoints.

Updates UI dynamically (e.g. scores, game board).

### PHP Backend (game_logic.php):

Receives AJAX requests.

Processes game moves and logic.

Returns JSON responses.

Handles data persistence (optional database or session).


---


## Setup & Requirements

   - PHP 7.4+  
   - A web server (Apache, Nginx, etc.)
   - Browser

Extract repository in correct webserver folder, for XAMPP it is ../xampp/htdocs/
Connect to the following adress using your browser of choice: http://localhost/webprogramming-final-project/index.php
Or use :"your IPV4 Adress"/webprogramming-final-project/index.php to connect from another device.


---


## Project Structure

webprogramming-final-project/<br>
├── css/<br>
│ └── main.css<br>
├── data/ # For saving session or state data<br>
├── images/ # Game assets (cats, mice, couch, etc.)<br>
│ ├── background.png<br>
│ ├── CCC_logo.png<br>
│ ├── couch.png<br>
│ ├── lamp.png<br>
│ ├── laserpointer.png<br>
│ ├── Milk.png<br>
│ ├── mine.png<br>
│ ├── mouse.png<br>
│ ├── plant.png<br>
│ ├── tile01_white.png<br>
│ ├── tile02_tuxedo.png<br>
│ ├── tile03_ginger.png<br>
│ ├── tile04_tabby.png<br>
│ ├── tile05_siamese.png<br>
│ ├── wooden_plank.png<br>
│ └── wool.png<br>
├── js/<br>
│ ├── game.js # Main gameplay logic<br>
│ ├── index.js # Lobby/start screen logic<br>
│ └── skin_carousel.js # UI for choosing player skins<br>
├── php/<br>
│ ├── assign_player.php # Assigns a player to a session<br>
│ ├── load_state.php # Loads the game state<br>
│ ├── reset_game.php # Resets the current game session and deletes session data<br>
│ ├── save_players.php # Saves player details<br>
│ ├── save_state.php # Saves turn data and moves<br>
│ ├── start_game.php # Initializes game state<br>
│ └── use_powerup.php # Handles item/power-up usage<br>
├── templates/<br>
├── game.php # Main game screen<br>
└── index.php # Landing page / start menu<br>


---


## Creators

- Anko van Dijk
- Marilie Spooren
- Iwan Hofstra
- Wolter Bos
