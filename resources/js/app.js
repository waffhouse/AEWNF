import './bootstrap';

// Import Tippy.js for tooltips
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';
import 'tippy.js/themes/light-border.css';

// Make tippy available globally
window.tippy = tippy;

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
    // Force reinitialize Alpine components only once with a small delay
    if (window.Alpine) {
        // Single delayed initialization is more stable than multiple attempts
        setTimeout(() => {
            window.dispatchEvent(new CustomEvent('alpine-reinit'));
            
            // Removed forced layout recalculation that can cause glitches
        }, 100);
    }
});

// Also ensure Alpine.js components are initialized on direct page loads
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('/customer/orders')) {
        console.log('Orders page detected on direct load');
        // Apply special handling for orders page
        setTimeout(() => {
            if (window.Alpine) {
                window.dispatchEvent(new CustomEvent('alpine-reinit'));
            }
        }, 100);
    }
});

// Scroll-to-top functionality removed to prevent issues with charts and other interactive elements