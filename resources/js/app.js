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
    if (window.Alpine) {
        // Immediate notification for fast components
        window.dispatchEvent(new CustomEvent('alpine-reinit'));
        
        // Multiple timed notifications for components that might load later
        // This handles race conditions with different loading times
        [50, 100, 300].forEach(delay => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('alpine-reinit'));
                
                // Force browser to recalculate layout
                document.body.classList.add('alpine-reinit-trigger');
                setTimeout(() => {
                    document.body.classList.remove('alpine-reinit-trigger');
                }, 10);
            }, delay);
        });
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