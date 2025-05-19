<?php
session_start();

// Define product categories
$categories = [
    1 => "Electronics",
    2 => "Accessories",
    3 => "Computers"
];

// Define available products with more details
$products = [
    1 => [
        "name" => "Smartphone", 
        "price" => 15000, 
        "category" => 1,
        "description" => "Latest model with 6GB RAM and 128GB storage"
    ],
    2 => [
        "name" => "Headphones", 
        "price" => 2000, 
        "category" => 2,
        "description" => "Wireless noise-cancelling headphones"
    ],
    3 => [
        "name" => "Smartwatch", 
        "price" => 5000, 
        "category" => 2,
        "description" => "Fitness tracker with heart rate monitor"
    ],
    4 => [
        "name" => "Laptop", 
        "price" => 55000, 
        "category" => 3,
        "description" => "Core i5, 16GB RAM, 512GB SSD"
    ],
    5 => [
        "name" => "Tablet", 
        "price" => 25000, 
        "category" => 1,
        "description" => "10-inch display with 64GB storage"
    ],
    6 => [
        "name" => "Bluetooth Speaker", 
        "price" => 3500, 
        "category" => 2,
        "description" => "Portable waterproof speaker with 10-hour battery life"
    ],
];

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle actions: add, remove, update, clear
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

    switch ($action) {
        case 'add':
            if (isset($products[$id])) {
                if (!isset($_SESSION['cart'][$id])) {
                    $_SESSION['cart'][$id] = 0;
                }
                $_SESSION['cart'][$id] += $quantity;
            }
            break;

        case 'update':
            if (isset($_SESSION['cart'][$id]) && $quantity > 0) {
                $_SESSION['cart'][$id] = $quantity;
            }
            break;

        case 'remove':
            if (isset($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
            }
            break;

        case 'clear':
            $_SESSION['cart'] = [];
            break;
    }
    
    // Use AJAX if available, otherwise redirect
    if (!isset($_GET['ajax'])) {
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
        header('Location: ' . (empty($redirect) ? strtok($_SERVER['REQUEST_URI'], '?') : $redirect));
        exit;
    } else {
        // For AJAX requests, just calculate the new total
        $total = 0;
        $items = 0;
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $total += $products[$pid]['price'] * $qty;
            $items += $qty;
        }
        echo json_encode(['total' => $total, 'items' => $items]);
        exit;
    }
}

// Get current page
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Calculate cart total
$cart_total = 0;
$cart_items = 0;
foreach ($_SESSION['cart'] as $pid => $qty) {
    $cart_total += $products[$pid]['price'] * $qty;
    $cart_items += $qty;
}

// Filter products by category if set
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filtered_products = $products;
if ($category_filter > 0) {
    $filtered_products = array_filter($products, function($product) use ($category_filter) {
        return $product['category'] == $category_filter;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopCart - Online Shopping</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --dark: #343a40;
            --light: #f8f9fa;
            --danger: #dc3545;
            --success: #28a745;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
            color: var(--dark);
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        header {
            background: var(--dark);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        
        .cart-icon {
            position: relative;
            color: white;
            text-decoration: none;
        }
        
        .cart-count {
            display: inline-block;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 5px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            margin-top: 10px;
        }
        
        nav ul li {
            margin-right: 20px;
        }
        
        nav ul li a {
            color: #ddd;
            text-decoration: none;
        }
        
        nav ul li a:hover {
            color: white;
        }
        
        .main {
            padding: 20px 0;
        }
        
        .page-title {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        /* Category filters */
        .category-filters {
            display: flex;
            margin-bottom: 20px;
        }
        
        .category-btn {
            padding: 8px 16px;
            margin-right: 10px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: var(--dark);
        }
        
        .category-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Product grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 15px;
        }
        
        .product-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .product-price {
            color: var(--primary);
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .add-to-cart {
            display: flex;
            align-items: center;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-right: 10px;
        }
        
        .qty-btn {
            width: 25px;
            height: 25px;
            background: #eee;
            border: none;
            cursor: pointer;
        }
        
        .qty-input {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            height: 25px;
            margin: 0 5px;
        }
        
        .add-btn {
            flex-grow: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        /* Cart page */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .cart-table th, .cart-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .cart-quantity {
            display: flex;
            align-items: center;
        }
        
        .remove-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .cart-summary {
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .cart-total {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .checkout-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        
        .clear-cart {
            display: inline-block;
            margin-top: 10px;
            color: var(--danger);
            text-decoration: none;
        }
        
        /* Checkout page */
        .checkout-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .checkout-form {
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        /* Order confirmation */
        .order-confirmation {
            text-align: center;
            padding: 30px 0;
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Empty states */
        .empty-state {
            text-align: center;
            padding: 30px 0;
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            
            nav ul {
                flex-wrap: wrap;
            }
            
            nav ul li {
                margin-bottom: 5px;
            }
        }
        
        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-top">
                <a href="index.php" class="logo">ShopCart</a>
                <a href="index.php?page=cart" class="cart-icon">
                    Cart <?php if ($cart_items > 0): ?>
                    <span class="cart-count"><?php echo $cart_items; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <?php foreach ($categories as $id => $name): ?>
                    <li><a href="index.php?category=<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="index.php?page=cart">Cart</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="main">
        <div class="container">
            <?php if ($page == 'home'): ?>
                <h1 class="page-title">Our Products</h1>
                
                <!-- Category filters -->
                <div class="category-filters">
                    <a href="index.php" class="category-btn <?php echo $category_filter == 0 ? 'active' : ''; ?>">All Products</a>
                    <?php foreach ($categories as $id => $name): ?>
                    <a href="index.php?category=<?php echo $id; ?>" class="category-btn <?php echo $category_filter == $id ? 'active' : ''; ?>"><?php echo htmlspecialchars($name); ?></a>
                    <?php endforeach; ?>
                </div>
                
                <!-- Product grid -->
                <div class="product-grid">
                    <?php foreach ($filtered_products as $id => $product): ?>
                    <div class="product-card">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-price">PKR <?php echo number_format($product['price']); ?></div>
                        <div class="product-description"><?php echo htmlspecialchars($product['description']); ?></div>
                        <div class="add-to-cart">
                            <div class="quantity-selector">
                                <button class="qty-btn" onclick="updateQty('qty-<?php echo $id; ?>', -1)">-</button>
                                <input type="number" id="qty-<?php echo $id; ?>" class="qty-input" value="1" min="1" max="10">
                                <button class="qty-btn" onclick="updateQty('qty-<?php echo $id; ?>', 1)">+</button>
                            </div>
                            <button class="add-btn" onclick="addToCart(<?php echo $id; ?>)">Add to Cart</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($filtered_products)): ?>
                    <div class="empty-state">
                        <h2>No products found</h2>
                        <p>Try selecting a different category or check back later</p>
                    </div>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($page == 'cart'): ?>
                <h1 class="page-title">Your Shopping Cart</h1>
                
                <?php if (empty($_SESSION['cart'])): ?>
                <div class="empty-state">
                    <h2>Your cart is empty</h2>
                    <p>Start shopping to add items to your cart</p>
                    <a href="index.php" class="add-btn" style="display: inline-block; margin-top: 15px; padding: 10px 20px;">Shop Now</a>
                </div>
                <?php else: ?>
                <div class="cart-grid">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $id => $qty): ?>
                            <?php $product = $products[$id]; ?>
                            <tr>
                                <td data-label="Product">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </td>    </div>
                                </td>
                                <td data-label="Price">PKR <?php echo number_format($product['price']); ?></td>
                                <td data-label="Quantity">
                                    <div class="cart-quantity">
                                        <button class="qty-btn" onclick="updateCartQty(<?php echo $id; ?>, <?php echo $qty - 1; ?>)" <?php echo $qty <= 1 ? 'disabled' : ''; ?>>-</button>
                                        <input type="number" class="qty-input cart-qty-input" id="cart-qty-<?php echo $id; ?>" value="<?php echo $qty; ?>" min="1" max="10" onchange="updateCartQty(<?php echo $id; ?>, this.value)">
                                        <button class="qty-btn" onclick="updateCartQty(<?php echo $id; ?>, <?php echo $qty + 1; ?>)">+</button>
                                    </div>
                                </td>
                                <td data-label="Subtotal">PKR <?php echo number_format($product['price'] * $qty); ?></td>
                                <td data-label="Action">
                                    <button class="remove-btn" onclick="removeFromCart(<?php echo $id; ?>)">Remove</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-summary">
                        <div class="cart-total">Total: PKR <?php echo number_format($cart_total); ?></div>
                        <a href="index.php?page=checkout" class="checkout-btn">Proceed to Checkout</a>
                        <a href="index.php?action=clear&redirect=index.php?page=cart" class="clear-cart">Clear Cart</a>
                    </div>
                </div>
                <?php endif; ?>
                
            <?php elseif ($page == 'checkout'): ?>
                <?php if (empty($_SESSION['cart'])): ?>
                <script>window.location.href = 'index.php';</script>
                <?php else: ?>
                <h1 class="page-title">Checkout</h1>
                
                <div class="checkout-grid">
                    <div class="checkout-form">
                        <h2>Shipping Details</h2>
                        <form action="index.php?page=confirmation" method="post">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="payment">Payment Method</label>
                                <select id="payment" name="payment" class="form-control" required>
                                    <option value="">Select payment method</option>
                                    <option value="cod">Cash on Delivery</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="card">Credit/Debit Card</option>
                                </select>
                            </div>
                            <button type="submit" class="checkout-btn">Place Order</button>
                        </form>
                    </div>
                    
                    <div class="cart-summary">
                        <h2>Order Summary</h2>
                        <?php foreach ($_SESSION['cart'] as $id => $qty): ?>
                        <?php $product = $products[$id]; ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <div><?php echo htmlspecialchars($product['name']); ?> x <?php echo $qty; ?></div>
                            <div>PKR <?php echo number_format($product['price'] * $qty); ?></div>
                        </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="cart-total">Total: PKR <?php echo number_format($cart_total); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
            <?php elseif ($page == 'confirmation'): ?>
                <?php 
                // Clear cart after order is placed
                $_SESSION['cart'] = [];
                ?>
                <div class="order-confirmation">
                    <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                    <h1>Order Placed Successfully!</h1>
                    <p>Thank you for your purchase. Your order has been placed successfully.</p>
                    <p>We'll send you an email with order details shortly.</p>
                    <a href="index.php" class="add-btn" style="display: inline-block; margin-top: 20px; padding: 10px 20px;">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Function to update quantity selector
        function updateQty(id, change) {
            const input = document.getElementById(id);
            let value = parseInt(input.value) + change;
            if (value < 1) value = 1;
            if (value > 10) value = 10;
            input.value = value;
        }
        
        // Function to add to cart
        function addToCart(productId) {
            const qty = document.getElementById('qty-' + productId).value;
            window.location.href = 'index.php?action=add&id=' + productId + '&quantity=' + qty;
        }
        
        // Function to update cart quantity
        function updateCartQty(productId, qty) {
            if (qty < 1) return;
            window.location.href = 'index.php?action=update&id=' + productId + '&quantity=' + qty + '&redirect=index.php?page=cart';
        }
        
        // Function to remove from cart
        function removeFromCart(productId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                window.location.href = 'index.php?action=remove&id=' + productId + '&redirect=index.php?page=cart';
            }
        }
    </script>
</body>
</html>