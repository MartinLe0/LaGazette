document.addEventListener('DOMContentLoaded', function () {
    console.log('Header JS loaded');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mainMenu = document.getElementById('main-menu');
    console.log('Mobile btn:', mobileMenuBtn);
    console.log('Main menu:', mainMenu);

    if (mobileMenuBtn && mainMenu) {
        mobileMenuBtn.addEventListener('click', function (e) {
            console.log('Mobile menu clicked');
            e.preventDefault(); // Prevent default behavior just in case
            mainMenu.classList.toggle('menu-open');
            console.log('Menu classes:', mainMenu.classList);
        });
    } else {
        console.error('Menu elements not found');
    }
});
