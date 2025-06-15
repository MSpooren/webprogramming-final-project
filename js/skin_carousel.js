$(document).ready(function () {
    const skins = Array.from(document.querySelectorAll('.skin-option'));
    let selectedIndex = 0;

    function updateCarousel() {
        skins.forEach(img => {
            img.classList.remove('active', 'side');
            img.style.display = 'none';
            img.parentElement.style.display = 'none';
        });

        const prev = (selectedIndex - 1 + skins.length) % skins.length;
        const next = (selectedIndex + 1) % skins.length;

        skins[prev].classList.add('side');
        skins[prev].style.display = 'inline-block';
        skins[prev].parentElement.style.display = 'inline-block';

        skins[selectedIndex].classList.add('active');
        skins[selectedIndex].style.display = 'inline-block';
        skins[selectedIndex].parentElement.style.display = 'inline-block';

        skins[next].classList.add('side');
        skins[next].style.display = 'inline-block';
        skins[next].parentElement.style.display = 'inline-block';

        const selectedSkin = skins[selectedIndex].dataset.skin;
        const selectedCatName = skins[selectedIndex].dataset.catname;

        $('#skin').val(selectedSkin);
        $('#selected-skin-name').text(selectedCatName);
    }

    $('#prev-skin').click(() => {
        selectedIndex = (selectedIndex - 1 + skins.length) % skins.length;
        updateCarousel();
    });

    $('#next-skin').click(() => {
        selectedIndex = (selectedIndex + 1) % skins.length;
        updateCarousel();
    });

    updateCarousel();
});