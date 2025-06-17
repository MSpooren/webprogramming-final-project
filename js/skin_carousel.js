$(document).ready(function () {
    // Get all elements with the class 'skin-option' and option convert NodeList to an array
    const skins = Array.from(document.querySelectorAll('.skin-option'));
    // Index of the currently selected skin in the carousel
    let selectedIndex = 0;

    // Function to update the carousel display based on the selectedIndex
    function updateCarousel() {
        // Hide and reset classes for all skin images and their parent containers
        skins.forEach(img => {
            img.classList.remove('active', 'side');
            img.style.display = 'none';
            img.parentElement.style.display = 'none';
        });

        // Calculates indices of previous and next skins, using module for wrap-around
        const prev = (selectedIndex - 1 + skins.length) % skins.length;
        const next = (selectedIndex + 1) % skins.length;

        // Show and style the previous skin is a "side" item
        skins[prev].classList.add('side');
        skins[prev].style.display = 'inline-block';
        skins[prev].parentElement.style.display = 'inline-block';

        // Show and style the currently selected skin as "active"
        skins[selectedIndex].classList.add('active');
        skins[selectedIndex].style.display = 'inline-block';
        skins[selectedIndex].parentElement.style.display = 'inline-block';

        // Show and style the next skin as a "side" item
        skins[next].classList.add('side');
        skins[next].style.display = 'inline-block';
        skins[next].parentElement.style.display = 'inline-block';

        // Get the data attributes of the selected skin: skin identifier and cat name
        const selectedSkin = skins[selectedIndex].dataset.skin;
        const selectedCatName = skins[selectedIndex].dataset.catname;

        // Update the hidden input field and display text with selected skin info
        $('#skin').val(selectedSkin);
        $('#selected-skin-name').text(selectedCatName);
    }

    // Click handler for the "previous skin" button
    $('#prev-skin').click(() => {
        // Move selected index backward with wrap-around
        selectedIndex = (selectedIndex - 1 + skins.length) % skins.length;
        updateCarousel();
    });

    // Click handler for the "next skin" button
    $('#next-skin').click(() => {
        // Move selected index forward with wrap-around
        selectedIndex = (selectedIndex + 1) % skins.length;
        updateCarousel();
    });

    // Initial carousel setup on page load
    updateCarousel();
});