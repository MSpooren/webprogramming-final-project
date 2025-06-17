<?php 
$bodyClass = 'index-background';
include 'templates/header.php'; 
?>

<!-- Step 1: Title -->
<div id="title-screen">
    <img src="images/CCC_logo.png" alt="Logo" class="logo">
    <br>
    <button class="pixel-button" id="start-btn">Start</button>
    <br><br>
    <button class="pixel-button" id="help-btn">Help</button>
    <br><br>
    <button class="pixel-button" id="credits-btn">Credits</button>
</div>

<!-- Step 2: Player Setup -->
<div id="setup-screen">
    <h2>Enter your cat's name!</h2>
    <input type="text" id="name" placeholder="Cat Name :3" required><br><br>
    <div id="skin-selector-wrapper">
        <button class="pixel-button" id="prev-skin">←</button>
        <div id="skin-strip">
            <?php
            $catTypes = [
                "tile01_white" => "White Cat",
                "tile02_tuxedo" => "Tuxedo Cat",
                "tile03_ginger" => "Ginger Cat",
                "tile04_tabby" => "Tabby Cat",
                "tile05_siamese" => "Siamese Cat",
            ];
            $images = glob("images/tile*.png");
            foreach ($images as $img) {
                $basename = basename($img, ".png");
                $catName = isset($catTypes[$basename]) ? $catTypes[$basename] : "Unknown Cat";
                echo "<div class='skin-container'>";
                echo "<img src='$img' class='skin-option' data-skin='$basename' data-catname='$catName'>";
                echo "</div>";
            }
            ?>
        </div>
        <button class="pixel-button" id="next-skin">→</button>
    </div>
    <div id="selected-skin-label">
        Selected skin: <span id="selected-skin-name">None</span>
        <input type="hidden" id="skin" name="skin" value="">
    </div>
    <button class="pixel-button" id="ready-btn">Ready!</button>
    <br>
    <br>
    <button class="pixel-button" id="menu-s-btn">Back</button>
</div>

<!-- Step 3: Waiting -->
<div id="waiting-screen">
    <h3>Waiting for your opponent...</h3>
    <p id="status-msg">Checking status...</p>
</div>

<!-- Help Screen -->
<div id="help-screen" style="display:none">
    <h2>How to Play</h2>
    <p>Use W/A/S/D to move your character.</p>
    <p>Collect special items by catching mice.<br>
    Special items include: Laserpointer, Wool (cat roll), Milk (diagonal).</p>
    <p>Avoid the plant and lamp obstacles.</p>
    <p>Get to the couch to score points.<br>
    The first player to reach five points wins!</p>
    <button class="pixel-button" id="menu-h-btn">Back</button>
</div>

<!-- Credits Screen -->
<div id="credits-screen" style="display:none">
    <h2>Credits</h2>
    <br>
    <br>
    <p>
        Game by:<br><br>
        Iwan Hofstra – s1234567<br><br>
        Marilie Spooren – s5916356<br><br>
        Wolter Bos – s1234567<br><br>
        Anko van Dijk – s4074661
    </p>
    <button class="pixel-button" id="menu-c-btn">Back</button>
</div>
<script src="js/skin_carousel.js"></script>
<script src="js/index.js"></script>

<?php include 'templates/footer.php'; ?>