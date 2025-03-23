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

// Force scroll to top on page refresh/reload
// Using both the load event and readystatechange for broader browser support
if (window.performance) {
    // Add the event outside of a DOMContentLoaded listener to ensure it runs first
    window.addEventListener('load', () => {
        try {
            // Try to detect if this is a refresh/reload
            let isReload = false;
            
            // Check the older navigation API
            if (window.performance.navigation && 
                window.performance.navigation.type === window.performance.navigation.TYPE_RELOAD) {
                isReload = true;
            }
            // Check the newer Navigation API
            else if (performance.getEntriesByType && performance.getEntriesByType('navigation').length) {
                const navEntries = performance.getEntriesByType('navigation');
                if (navEntries.length && navEntries[0].type === 'reload') {
                    isReload = true;
                }
            }
            
            if (isReload) {
                console.log('Page refresh detected, scrolling to top smoothly');
                
                // Initial smooth scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Prevent sudden jumps while smooth scrolling is in progress
                const preventJumps = () => {
                    // Only allow smooth scrolling toward the top
                    if (window.scrollY > 10) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                };
                
                window.addEventListener('scroll', preventJumps);
                
                // Allow normal scrolling after a delay that gives smooth scrolling time to complete
                setTimeout(() => {
                    window.removeEventListener('scroll', preventJumps);
                }, 1000);
            }
        } catch (e) {
            console.error('Error in scroll-to-top handler:', e);
        }
    }, { once: true }); // Only run once per page load
}