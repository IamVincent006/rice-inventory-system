<?php
session_start();
require_once '../config/db_config.php';

// Security Check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch available products
$stmt = $pdo->query("SELECT * FROM products WHERE current_stock_kg > 0 ORDER BY rice_type ASC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Terminal | Bigasan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --harvest-gold: #facc15;
            --terminal-dark: #0f172a;
            --surface: #ffffff;
            --bg-gray: #f1f5f9;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body { 
            background-color: var(--bg-gray); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--terminal-dark);
            overflow-x: hidden;
        }

        /* Responsive Main Content Logic */
        .main-content { 
            margin-left: 280px; 
            min-height: 100vh;
            transition: margin 0.3s ease;
        }

        /* Product Grid: Auto-scaling columns */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .product-card { 
            cursor: pointer; 
            transition: var(--transition); 
            border: 1px solid #e2e8f0; 
            border-radius: 1.25rem;
            background: var(--surface);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Mobile Adaptations */
        @media (max-width: 991.98px) {
            .main-content { margin-left: 0; }
            .cart-container { 
                position: relative; 
                height: auto !important; 
                border-left: none !important;
                border-top: 2px solid #e2e8f0;
                margin-top: 2rem;
            }
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 1rem;
            }
        }

        .product-card:hover { 
            border-color: var(--harvest-gold); 
            transform: translateY(-5px);
            box-shadow: 0 12px 20px -5px rgba(0,0,0,0.1);
        }

        .product-card .stock-badge {
            background: rgba(15, 23, 42, 0.05);
            padding: 0.25rem 0.6rem;
            border-radius: 2rem;
            font-size: 0.7rem; font-weight: 700;
        }

        .cart-container { 
            background: var(--surface); 
            height: 100vh; 
            position: sticky; 
            top: 0; 
            padding: 2rem; 
            border-left: 1px solid #e2e8f0; 
            display: flex;
            flex-direction: column;
        }

        .cash-input {
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            font-weight: 800;
            padding: 1rem;
            font-size: 1.1rem; /* Better for mobile touch */
        }

        .btn-complete {
            background: var(--terminal-dark);
            color: #fff;
            border-radius: 1rem;
            padding: 1.25rem;
            font-weight: 800;
            transition: var(--transition);
        }

        .price-tag { color: var(--terminal-dark); font-weight: 800; }
        .unit-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-grow-1 main-content">
        <div class="row m-0">
            <div class="col-lg-8 p-3 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h1 class="fw-800 mb-0">Terminal <span class="text-warning">01</span></h1>
                        <p class="text-secondary small">Inventory-Linked Rice POS</p>
                    </div>
                    <div class="text-end d-none d-sm-block">
                        <span class="badge bg-white text-dark shadow-sm p-3 rounded-4 border">
                            <i class="ph-fill ph-user-circle me-2"></i> 
                            <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>
                        </span>
                    </div>
                </div>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                        <i class="ph-bold ph-warning-circle me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="product-grid">
                    <?php foreach ($products as $item): ?>
                    <div class="product-card p-4 shadow-sm" onclick="addToCart(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                        <div class="d-flex justify-content-between mb-3">
                            <i class="ph-fill ph-package text-warning fs-3"></i>
                            <span class="stock-badge">STOCK: <?php echo $item['current_stock_kg']; ?>kg</span>
                        </div>
                        <h6 class="fw-800 mb-3 text-truncate"><?php echo htmlspecialchars($item['rice_type']); ?></h6>
                        
                        <div class="row g-0 mt-auto pt-2 border-top">
                            <div class="col-6 pe-2 border-end">
                                <span class="unit-label d-block">Kilo</span>
                                <div class="price-tag small">₱<?php echo number_format($item['price_kilo'], 2); ?></div>
                            </div>
                            <div class="col-6 ps-2">
                                <span class="unit-label d-block">Sack</span>
                                <div class="price-tag small">₱<?php echo number_format($item['price_sack'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-4 cart-container shadow-lg">
                <div class="d-flex align-items-center mb-4">
                    <i class="ph-bold ph-receipt-byte text-warning me-2" style="font-size: 1.5rem;"></i>
                    <h4 class="fw-800 mb-0">Order Summary</h4>
                </div>

                <form action="process_sale.php" method="POST" class="h-100 d-flex flex-column">
                    <div id="cart-items" class="flex-grow-1 overflow-auto pe-2">
                        <div class="text-center py-5 opacity-50" id="empty-cart-msg">
                            <i class="ph ph-shopping-cart-simple d-block mb-2" style="font-size: 3rem;"></i>
                            <p>Cart is currently empty</p>
                        </div>
                    </div>
                    
                    <div class="border-top pt-4 bg-white mt-3">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-600 text-secondary">Total Amount</span>
                            <h2 class="fw-800 text-primary" id="grand-total">₱ 0.00</h2>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-800 text-uppercase text-secondary">Cash Tendered</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-4">₱</span>
                                <input type="number" step="0.01" class="form-control cash-input border-start-0 rounded-end-4" id="cash-tendered" placeholder="0.00" onkeyup="calculateChange()">
                            </div>
                        </div>
                        
                        <div class="p-3 rounded-4 mb-4" id="change-box" style="background: var(--bg-gray); transition: background 0.3s;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-600 text-secondary">Change Due</span>
                                <h4 class="fw-800 mb-0" id="change-due">₱ 0.00</h4>
                            </div>
                        </div>

                        <button type="submit" id="checkout-btn" class="btn btn-complete w-100 shadow-sm" disabled>
                            COMPLETE TRANSACTION <i class="ph-bold ph-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    let currentTotalAmount = 0;

    function addToCart(item) {
        const container = document.getElementById('cart-items');
        const emptyMsg = document.getElementById('empty-cart-msg');
        if(emptyMsg) emptyMsg.remove();
        
        const cartHtml = `
            <div class="card bg-light p-4 mb-3 shadow-sm border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-800 mb-0">${item.rice_type}</h6>
                    <button type="button" class="btn-close" onclick="resetCart();" style="font-size: 0.8rem;"></button>
                </div>
                <input type="hidden" name="product_id" value="${item.product_id}">
                
                <div class="row g-2">
                    <div class="col-7">
                        <select name="unit_type" class="form-select form-select-sm rounded-3" onchange="updatePrice(this, ${item.price_kilo}, ${item.price_half_sack}, ${item.price_sack})">
                            <option value="Kilo">Kilo (1kg)</option>
                            <option value="Half-Sack">Half-Sack (25kg)</option>
                            <option value="Sack">Full Sack (50kg)</option>
                        </select>
                    </div>
                    <div class="col-5">
                        <input type="number" name="qty" value="1" class="form-control form-control-sm rounded-3 fw-bold" min="1" onchange="updateQty(this)">
                    </div>
                </div>
                
                <div class="mt-3 text-end">
                    <span class="fw-800 text-primary item-total" data-base-price="${item.price_kilo}">₱ ${parseFloat(item.price_kilo).toFixed(2)}</span>
                </div>
            </div>
        `;
        container.innerHTML = cartHtml; 
        
        currentTotalAmount = parseFloat(item.price_kilo);
        refreshTotals();
    }

    function updatePrice(select, pk, ph, ps) {
        let basePrice = select.value === 'Half-Sack' ? parseFloat(ph) : (select.value === 'Sack' ? parseFloat(ps) : parseFloat(pk));
        const priceSpan = document.querySelector('.item-total');
        priceSpan.setAttribute('data-base-price', basePrice);
        updateQty(document.querySelector('input[name="qty"]'));
    }

    function updateQty(input) {
        const qty = parseFloat(input.value) || 1;
        const priceSpan = document.querySelector('.item-total');
        const basePrice = parseFloat(priceSpan.getAttribute('data-base-price'));
        
        currentTotalAmount = basePrice * qty;
        priceSpan.innerText = '₱ ' + currentTotalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        refreshTotals();
    }

    function refreshTotals() {
        document.getElementById('grand-total').innerText = '₱ ' + currentTotalAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        calculateChange();
    }

    function resetCart() {
        currentTotalAmount = 0;
        document.getElementById('cart-items').innerHTML = `<div class="text-center py-5 opacity-50" id="empty-cart-msg"><i class="ph ph-shopping-cart-simple d-block mb-2" style="font-size: 3rem;"></i><p>Cart is currently empty</p></div>`;
        refreshTotals();
    }

    function calculateChange() {
        const cashTendered = parseFloat(document.getElementById('cash-tendered').value) || 0;
        const changeDueDisplay = document.getElementById('change-due');
        const checkoutBtn = document.getElementById('checkout-btn');
        const changeBox = document.getElementById('change-box');

        if (currentTotalAmount > 0 && cashTendered >= currentTotalAmount) {
            const change = cashTendered - currentTotalAmount;
            changeDueDisplay.innerText = '₱ ' + change.toLocaleString(undefined, {minimumFractionDigits: 2});
            changeDueDisplay.className = 'fw-800 mb-0 text-success';
            changeBox.style.background = '#f0fdf4';
            checkoutBtn.disabled = false;
        } else {
            changeDueDisplay.innerText = '₱ 0.00';
            changeDueDisplay.className = 'fw-800 mb-0 text-secondary';
            changeBox.style.background = 'var(--bg-gray)';
            if (cashTendered > 0 && cashTendered < currentTotalAmount) {
                changeDueDisplay.classList.add('text-danger');
            }
            checkoutBtn.disabled = true;
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>