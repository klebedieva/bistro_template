<?php

namespace App\Service;

use App\Repository\MenuItemRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Shopping Cart Service
 *
 * Manages the user's shopping cart state using Symfony session storage.
 * Provides methods to add, remove, update quantities, and retrieve cart contents.
 * All cart operations are session-based and do not persist to database
 * until an order is created.
 *
 * Responsibilities:
 * - Session-based cart storage and retrieval
 * - Adding/removing/updating cart items
 * - Calculating cart totals and item counts
 * - Fetching menu item details from database when adding new items
 * - Formatting cart data for API responses
 *
 * Cart structure:
 * - Stored in session under 'cart' key
 * - Format: [menuItemId => ['id', 'name', 'price', 'image', 'category', 'quantity']]
 * - Automatically calculates totals and item counts
 *
 * Design principles:
 * - Single Responsibility: Only handles cart operations, not image path resolution
 * - Session-based: Cart data is stored in user session, not database
 * - Stateless operations: Each method call is independent (except for session state)
 * - Clear separation: Image path resolution delegated to MenuItemImageResolver
 *
 * Side effects:
 * - Modifies session data (cart storage)
 * - Reads from database (MenuItemRepository) when adding new items
 * - Does NOT persist to database (cart is temporary until order creation)
 */
class CartService
{
    /**
     * Session key used to store cart data
     *
     * All cart operations use this key to read/write cart data from session.
     * The cart is stored as an associative array where keys are menu item IDs.
     */
    private const CART_SESSION_KEY = 'cart';

    /**
     * Constructor
     *
     * Injects required dependencies:
     * - RequestStack: For accessing Symfony session to store/retrieve cart data
     * - MenuItemRepository: For fetching menu item details from database when adding items
     * - MenuItemImageResolver: For resolving image paths to consistent format
     *
     * @param RequestStack $requestStack Symfony request stack for session access
     * @param MenuItemRepository $menuItemRepository Repository for menu item database queries
     * @param MenuItemImageResolver $imageResolver Service for resolving image paths
     */
    public function __construct(
        private RequestStack $requestStack,
        private MenuItemRepository $menuItemRepository,
        private MenuItemImageResolver $imageResolver
    ) {}

    /**
     * Add item to cart or increase quantity if item already exists
     *
     * This method handles two scenarios:
     * 1. Item already in cart: Increments the existing quantity by the specified amount
     * 2. New item: Fetches menu item details from database and creates a new cart entry
     *
     * When adding a new item, the method:
     * - Fetches menu item entity from database using MenuItemRepository
     * - Extracts item details (id, name, price, image, category)
     * - Resolves image path using MenuItemImageResolver (handles various path formats)
     * - Creates cart entry with initial quantity
     *
     * Side effects:
     * - Modifies session data (updates cart in session)
     * - Reads from database (MenuItemRepository::find) when adding new items
     *
     * @param int $menuItemId Menu item ID from database (must exist in database)
     * @param int $quantity Quantity to add (default: 1, must be positive)
     * @return array Updated cart details with items, total, and itemCount
     * @throws \InvalidArgumentException If menu item not found in database
     */
    public function add(int $menuItemId, int $quantity = 1): array
    {
        // Get current session and retrieve existing cart (or empty array if cart doesn't exist)
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);

        // Check if item already exists in cart
        // If yes, increment quantity (don't fetch from database again)
        if (isset($cart[$menuItemId])) {
            $cart[$menuItemId]['quantity'] += $quantity;
        } else {
            // Item doesn't exist in cart, fetch full details from database
            // This is necessary to get item name, price, image, and category
            $menuItem = $this->menuItemRepository->find($menuItemId);
            
            // Validate that menu item exists in database
            // Throw exception if item not found (prevents adding invalid items to cart)
            if (!$menuItem) {
                throw new \InvalidArgumentException("Menu item not found: $menuItemId");
            }

            // Create new cart entry with all item details
            // Image path is resolved using MenuItemImageResolver to ensure consistent format
            // Pass category to resolver so it can use the correct folder (entrees, plats, desserts)
            $category = $menuItem->getCategory();
            $cart[$menuItemId] = [
                'id' => $menuItem->getId(),
                'name' => $menuItem->getName(),
                'price' => (float) $menuItem->getPrice(),
                'image' => $this->imageResolver->resolve($menuItem->getImage()),
                'category' => $category,
                'quantity' => $quantity,
            ];
        }

        // Persist updated cart to session
        // This ensures cart state is saved and available on next request
        $session->set(self::CART_SESSION_KEY, $cart);
        
        // Return formatted cart details (items array, total, itemCount)
        return $this->getCartDetails($cart);
    }

    /**
     * Remove item completely from cart
     *
     * Removes the item with the given menu item ID from the cart entirely.
     * This operation is different from setting quantity to 0 (which also removes it
     * but goes through updateQuantity method).
     *
     * Side effects:
     * - Modifies session data (removes item from cart in session)
     *
     * @param int $menuItemId Menu item ID to remove (must exist in cart)
     * @return array Updated cart details after removal (items, total, itemCount)
     * @throws \InvalidArgumentException If item not found in cart
     */
    public function remove(int $menuItemId): array
    {
        // Get current session and retrieve existing cart
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);

        // Validate that item exists in cart before attempting removal
        // This prevents silent failures and provides clear error messages
        if (!isset($cart[$menuItemId])) {
            throw new \InvalidArgumentException("Cart item not found: $menuItemId");
        }

        // Remove item from cart array using unset
        // This completely removes the item, not just sets quantity to 0
        unset($cart[$menuItemId]);
        
        // Persist updated cart to session (without the removed item)
        $session->set(self::CART_SESSION_KEY, $cart);

        // Return formatted cart details after removal
        return $this->getCartDetails($cart);
    }

    /**
     * Update item quantity in cart
     *
     * Updates the quantity of an existing cart item. This method handles two cases:
     * 1. Quantity > 0: Updates the item's quantity to the new value
     * 2. Quantity <= 0: Removes the item from cart (equivalent to remove operation)
     *
     * Side effects:
     * - Modifies session data (updates item quantity or removes item from cart)
     *
     * @param int $menuItemId Menu item ID to update (must exist in cart)
     * @param int $quantity New quantity (0 or negative removes the item)
     * @return array Updated cart details after quantity change (items, total, itemCount)
     * @throws \InvalidArgumentException If item not found in cart
     */
    public function updateQuantity(int $menuItemId, int $quantity): array
    {
        // Get current session and retrieve existing cart
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);

        // Validate that item exists in cart before attempting update
        if (!isset($cart[$menuItemId])) {
            throw new \InvalidArgumentException("Cart item not found: $menuItemId");
        }

        // Handle quantity update or removal
        // If quantity is 0 or negative, remove item (same as remove operation)
        // Otherwise, update quantity to new value
        if ($quantity <= 0) {
            unset($cart[$menuItemId]);
        } else {
            $cart[$menuItemId]['quantity'] = $quantity;
        }
        
        // Persist updated cart to session
        $session->set(self::CART_SESSION_KEY, $cart);

        // Return formatted cart details after quantity change
        return $this->getCartDetails($cart);
    }

    /**
     * Get current cart contents with calculated totals
     *
     * Returns complete cart state including all items, total price, and total item count.
     * This is a read-only operation that does not modify cart state.
     *
     * Used by:
     * - API endpoints to return cart data to frontend
     * - OrderService to retrieve cart contents before creating order
     *
     * Side effects:
     * - None (read-only operation, does not modify session or database)
     *
     * @return array Cart details with:
     *   - 'items': Array of cart items (each with id, name, price, image, category, quantity)
     *   - 'total': Total price of all items (sum of price * quantity for each item)
     *   - 'itemCount': Total quantity of all items (sum of all quantities)
     */
    public function getCart(): array
    {
        // Get current session and retrieve cart (or empty array if cart doesn't exist)
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);
        
        // Format and return cart details (calculates totals and item count)
        return $this->getCartDetails($cart);
    }

    /**
     * Clear entire cart (remove all items)
     *
     * Removes all items from the cart, effectively emptying it. This operation
     * completely removes the cart from session storage.
     *
     * Typical use cases:
     * - After successful order creation (cart is cleared automatically)
     * - User-initiated cart reset
     * - Session cleanup
     *
     * Side effects:
     * - Modifies session data (removes cart key from session)
     *
     * @return array Empty cart structure with:
     *   - 'items': Empty array
     *   - 'total': 0.0
     *   - 'itemCount': 0
     */
    public function clear(): array
    {
        // Get current session
        $session = $this->requestStack->getSession();
        
        // Remove cart key from session (completely clears cart)
        $session->remove(self::CART_SESSION_KEY);
        
        // Return empty cart structure (items=[], total=0, itemCount=0)
        return $this->getCartDetails([]);
    }

    /**
     * Get total item count in cart
     *
     * Returns the sum of all item quantities (not the number of unique items).
     * This counts the total quantity of all items, not the number of different items.
     *
     * Examples:
     * - Cart with 2x Item A and 3x Item B → returns 5 (not 2)
     * - Cart with 1x Item A → returns 1
     * - Empty cart → returns 0
     *
     * Side effects:
     * - None (read-only operation)
     *
     * @return int Total quantity of all items in cart (sum of all item quantities)
     */
    public function getItemCount(): int
    {
        // Get current session and retrieve cart
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);
        
        // Calculate total quantity by summing all item quantities
        // Loop through each item in cart and add its quantity to total count
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }

    /**
     * Calculate cart total price
     *
     * Sums up all item prices multiplied by their quantities.
     * Result is rounded to 2 decimal places for currency precision (Euro format).
     *
     * Calculation formula:
     * total = sum(item['price'] * item['quantity']) for all items in cart
     *
     * Side effects:
     * - None (read-only operation, does not modify cart or session)
     *
     * @return float Total cart price rounded to 2 decimal places (e.g., 25.99)
     */
    public function getTotal(): float
    {
        // Get current session and retrieve cart
        $session = $this->requestStack->getSession();
        $cart = $session->get(self::CART_SESSION_KEY, []);
        
        // Calculate total by summing price * quantity for each item
        // Start with 0 and accumulate total for each cart item
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Round to 2 decimal places for currency precision (Euro format)
        return round($total, 2);
    }

    /**
     * Format cart details for API response
     *
     * This private helper method formats the raw cart array from session into
     * a structured response format suitable for API endpoints. It performs:
     * 1. Converts associative array (keyed by menuItemId) to indexed array
     * 2. Calculates total price (sum of price * quantity for all items)
     * 3. Calculates total item count (sum of all quantities)
     * 4. Rounds total to 2 decimal places for currency precision
     *
     * This method is used internally by all public methods that return cart data
     * to ensure consistent response format across all cart operations.
     *
     * @param array $cart Cart array from session (associative array keyed by menuItemId)
     * @return array Formatted cart with:
     *   - 'items': Indexed array of cart items (converted from associative array)
     *   - 'total': Total price rounded to 2 decimals
     *   - 'itemCount': Total quantity of all items
     */
    private function getCartDetails(array $cart): array
    {
        // Convert associative array (keyed by menuItemId) to indexed array
        // This makes the response format consistent and easier to work with in frontend
        $items = array_values($cart);
        
        // Initialize counters for calculations
        $total = 0;
        $itemCount = 0;

        // Calculate total price and item count by iterating through all items
        // Total = sum of (price * quantity) for each item
        // ItemCount = sum of all quantities
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
            $itemCount += $item['quantity'];
        }

        // Return formatted cart structure
        // This format is used consistently across all cart API responses
        return [
            'items' => $items,
            'total' => round($total, 2),  // Round to 2 decimals for currency precision
            'itemCount' => $itemCount,
        ];
    }
}

