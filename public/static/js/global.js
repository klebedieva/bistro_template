// ============================================================================
// GLOBAL.JS - Global Utility Functions
// ============================================================================
// This file contains utility functions that need to be available across the site:
// - Cart count display management
// - Confirmation dialog (fallback if main.js not loaded)
//
// Note: Some functions here may duplicate functionality in main.js to ensure
// they work even if main.js loads late or fails.

// ============================================================================
// CART COUNT DISPLAY
// ============================================================================

/**
 * Cart count visibility management
 *
 * This IIFE (Immediately Invoked Function Expression) ensures the cart count
 * badge in the navbar becomes visible after the page loads.
 *
 * Why this exists:
 * - Cart count starts hidden (CSS: .hidden) to prevent layout shift
 * - Needs to be shown after cart data loads from localStorage
 * - Should react to cart updates in real-time
 *
 * How it works:
 * 1. Listens for 'cartUpdated' event (fires when cart changes)
 * 2. Shows cart count on DOMContentLoaded (fast fallback)
 * 3. Shows cart count on window.load (slower fallback)
 * 4. One-time check after 500ms (catches late-loading scripts)
 *
 * This approach is more efficient than polling because:
 * - Only reacts when cart actually changes (event-driven)
 * - No unnecessary checks every 150ms
 * - Immediate response to cart updates
 */
(function () {
    /**
     * Remove 'hidden' class from cart count element to make it visible
     * This function is idempotent (safe to call multiple times)
     */
    function unhideCartCount() {
        // Find the cart count badge in the navbar
        const el = document.getElementById('cartNavCount');
        // If element exists, remove 'hidden' class to make it visible
        if (el) {
            el.classList.remove('hidden');
        }
    }

    /**
     * Primary method: Listen for cart update events
     * When cart-api.js (or other scripts) updates the cart, they dispatch
     * a 'cartUpdated' event. This listener reacts immediately.
     *
     * This is the most efficient approach - no polling, instant response.
     */
    window.addEventListener('cartUpdated', unhideCartCount);

    /**
     * Fallback 1: Try to show cart count as soon as DOM is ready
     * DOMContentLoaded fires when HTML is parsed (before images load)
     * This handles cases where cart count is already visible in HTML
     */
    document.addEventListener('DOMContentLoaded', unhideCartCount);

    /**
     * Fallback 2: Try again when all resources (images, CSS) are loaded
     * window.load fires after everything is loaded
     * More reliable but slower than DOMContentLoaded
     */
    window.addEventListener('load', unhideCartCount);

    /**
     * Fallback 3: One-time check after 500ms
     * Catches edge cases where:
     * - Late-loading scripts manipulate cart count
     * - cartUpdated event doesn't fire for some reason
     * - Page loads with existing cart data
     *
     * This is a single check (not polling), so it's very lightweight.
     */
    setTimeout(unhideCartCount, 500);
})();

// (No confirmation dialog fallback here anymore.)
// Confirmation dialogs are handled by main.js (showConfirmDialog),
// and cart-api.js already falls back to native window.confirm()
// when showConfirmDialog is not available.
