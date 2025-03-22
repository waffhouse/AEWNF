import './bootstrap';

// Fix for Alpine.js components during navigation
document.addEventListener('livewire:navigating', () => {
    // Find and close any open mobile menus
    const navElement = document.querySelector('nav[x-data]');
    if (navElement && navElement.__x) {
        navElement.__x.getUnobservedData().open = false;
    }
});