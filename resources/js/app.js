import './bootstrap';

// Fix for Alpine.js components during navigation
document.addEventListener('livewire:navigating', () => {
    // Find and close any open mobile menus
    const navElement = document.querySelector('nav[x-data]');
    if (navElement && navElement.__x) {
        navElement.__x.getUnobservedData().open = false;
    }
});

// Ensure Alpine.js components are properly initialized after navigation
document.addEventListener('livewire:navigated', () => {
    // Force reinitialize any Alpine components that might not be properly initialized
    setTimeout(() => {
        if (window.Alpine) {
            // Send a custom event that our components can listen to
            window.dispatchEvent(new CustomEvent('alpine-reinit'));
        }
    }, 50);
});