import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    const fullView = document.querySelector('.fullView');
    window.addEventListener('scroll', function() {
        // If user has scrolled down, change fullView's style to allow for all tiles to be shown
        if (window.scrollY > 50) {
            fullView.classList.add('auto-height');
        } else {
            fullView.classList.remove('auto-height');
        }
    });
});
