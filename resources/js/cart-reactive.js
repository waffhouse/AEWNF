/**
 * Cart Reactivity Enhancement
 * 
 * This script adds client-side reactivity to the cart system:
 * - Provides immediate UI feedback for cart actions
 * - Uses localStorage to maintain cart state between page refreshes
 * - Handles synchronization with server-side cart
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart reactivity
    initCartReactivity();
    
    // Listen for Livewire cart-updated events
    window.Livewire.on('cart-updated', () => {
        updateCartUI();
    });
    
    // Listen for product-cart-updated custom events
    document.addEventListener('product-cart-updated', (event) => {
        updateProductCartStatus(event.detail.inventoryId, event.detail.isInCart, event.detail.quantity);
    });
});

/**
 * Initialize cart reactivity
 */
function initCartReactivity() {
    // Apply local storage cart state to the UI
    updateCartUI();
    
    // Add listener for quantity input changes
    document.querySelectorAll('input[id^="quantity-"]').forEach(input => {
        input.addEventListener('change', debounce(function(e) {
            // Extract inventory ID from input ID
            const inventoryId = input.id.replace('quantity-', '');
            const quantity = parseInt(input.value) || 0;
            
            // Don't update if value is the same
            if (getLocalCartItemQuantity(inventoryId) === quantity) {
                return;
            }
            
            // Update local storage immediately for responsive UI
            updateLocalCartItem(inventoryId, quantity);
            
            // Update the UI immediately without waiting for server
            updateCartUI();
        }, 300));
    });
    
    // Add listeners for increment/decrement buttons
    document.querySelectorAll('button[wire\\:click\\.stop^="incrementQuantity"], button[wire\\:click\\.stop^="decrementQuantity"]').forEach(button => {
        button.addEventListener('click', function(e) {
            // Find the closest input element
            const input = button.closest('.flex').querySelector('input[type="number"]');
            const inventoryId = input.id.replace('quantity-', '');
            
            // Get current quantity
            let quantity = parseInt(input.value) || 0;
            
            // Increment or decrement based on button type
            if (button.getAttribute('wire:click.stop').includes('increment')) {
                quantity++;
            } else {
                quantity--;
            }
            
            // Ensure quantity is within valid range (0-99)
            quantity = Math.max(0, Math.min(99, quantity));
            
            // Update local storage immediately for responsive UI
            updateLocalCartItem(inventoryId, quantity);
            
            // Update the UI immediately without waiting for server
            updateCartUI();
        });
    });
}

/**
 * Update cart UI based on local storage
 */
function updateCartUI() {
    const cartItems = getLocalCart();
    let totalItems = 0;
    
    // Calculate total items
    Object.values(cartItems).forEach(item => {
        if (item && item.quantity) {
            totalItems += parseInt(item.quantity);
        }
    });
    
    // Update cart counter badge
    const cartCounters = document.querySelectorAll('.cart-counter');
    cartCounters.forEach(counter => {
        const countElement = counter.querySelector('.count');
        if (countElement) {
            countElement.textContent = totalItems.toString();
        }
        
        // Toggle visibility based on count
        counter.classList.toggle('hidden', totalItems === 0);
    });
    
    // Update product cart status indicators
    document.querySelectorAll('[data-inventory-id]').forEach(el => {
        const inventoryId = el.getAttribute('data-inventory-id');
        const isInCart = !!cartItems[inventoryId] && cartItems[inventoryId].quantity > 0;
        
        // Update "In Cart" badge if it exists
        const inCartBadge = document.querySelector(`[data-in-cart-badge="${inventoryId}"]`);
        if (inCartBadge) {
            inCartBadge.classList.toggle('hidden', !isInCart);
        }
    });
}

/**
 * Update product card to reflect cart status
 */
function updateProductCartStatus(inventoryId, isInCart, quantity) {
    // Update "In Cart" badge if it exists
    const inCartBadge = document.querySelector(`[data-in-cart-badge="${inventoryId}"]`);
    if (inCartBadge) {
        inCartBadge.classList.toggle('hidden', !isInCart);
    }
    
    // Update quantity input if it exists
    const quantityInput = document.getElementById(`quantity-${inventoryId}`);
    if (quantityInput && quantity !== undefined) {
        quantityInput.value = quantity;
    }
}

/**
 * Get cart data from local storage
 */
function getLocalCart() {
    const cart = localStorage.getItem('cart');
    return cart ? JSON.parse(cart) : {};
}

/**
 * Get cart item quantity from local storage
 */
function getLocalCartItemQuantity(inventoryId) {
    const cart = getLocalCart();
    return cart[inventoryId] ? parseInt(cart[inventoryId].quantity) || 0 : 0;
}

/**
 * Update cart item in local storage
 */
function updateLocalCartItem(inventoryId, quantity) {
    const cart = getLocalCart();
    
    if (quantity <= 0) {
        // Remove item if quantity is 0 or less
        delete cart[inventoryId];
    } else {
        // Add or update item
        cart[inventoryId] = {
            inventory_id: inventoryId,
            quantity: quantity
        };
    }
    
    // Save updated cart to local storage
    localStorage.setItem('cart', JSON.stringify(cart));
}

/**
 * Debounce function to limit how often a function can be called
 */
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}