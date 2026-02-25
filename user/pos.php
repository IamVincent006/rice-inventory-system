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
    <title>POS | Rice ni Mang Kanor Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .product-card { cursor: pointer; transition: 0.2s; border: none; }
        .product-card:hover { border: 2px solid #212529; transform: scale(1.02); }
        .cart-container { background: white; height: 100vh; position: sticky; top: 0; padding: 20px; border-left: 1px solid #dee2e6; }
        .cash-input:focus { border-color: #198754; box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
    </style>
</head>
<body class="d-flex">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-grow-1 container-fluid p-0">
        <div class="row m-0">
            <div class="col-md-8 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Point of Sale</h2>
                    <span class="badge bg-dark p-2">Cashier: <?php echo $_SESSION['full_name']; ?></span>
                </div>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger shadow-sm"><i class="bi bi-shield-lock me-2"></i><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i>Sale Completed! Inventory updated.</div>
                <?php endif; ?>

                <div class="row g-3">
                    <?php foreach ($products as $item): ?>
                    <div class="col-md-4">
                        <div class="card product-card p-3 shadow-sm" onclick="addToCart(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($item['rice_type']); ?></h5>
                            <small class="text-muted">Stock: <?php echo $item['current_stock_kg']; ?>kg</small>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <small>Sack: ₱<?php echo number_format($item['price_sack'], 2); ?></small>
                                <small>Kilo: ₱<?php echo number_format($item['price_kilo'], 2); ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-md-4 cart-container shadow">
                <h4 class="fw-bold mb-4"><i class="bi bi-cart3"></i> Current Order</h4>
                <form action="process_sale.php" method="POST">
                    <div id="cart-items" class="mb-3">
                        <p class="text-muted">Tap a product to start...</p>
                    </div>
                    
                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="text-muted mb-0 pt-1">Total Amount:</h5>
                            <h3 class="fw-bold text-primary mb-0" id="grand-total">₱ 0.00</h3>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold small">Cash Tendered (Customer's Money):</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light fw-bold">₱</span>
                                <input type="number" step="0.01" class="form-control fw-bold text-end cash-input" id="cash-tendered" placeholder="0.00" onkeyup="calculateChange()" onchange="calculateChange()">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-4 bg-light p-3 rounded border">
                            <h5 class="mb-0 pt-1 text-muted">Change Due:</h5>
                            <h3 class="fw-bold text-secondary mb-0" id="change-due">₱ 0.00</h3>
                        </div>

                        <button type="submit" id="checkout-btn" class="btn btn-dark w-100 py-3 fw-bold fs-5" disabled>COMPLETE SALE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    let currentTotalAmount = 0; // Global variable to track the exact total cost

    function addToCart(item) {
        const container = document.getElementById('cart-items');
        if(container.innerHTML.includes('Tap a product')) container.innerHTML = '';

        const cartHtml = `
            <div class="card p-3 mb-2 shadow-sm border-0 bg-light">
                <h6 class="fw-bold">${item.rice_type}</h6>
                <input type="hidden" name="product_id" value="${item.product_id}">
                <div class="mb-2">
                    <label class="small text-muted">Unit Type:</label>
                    <select name="unit_type" class="form-select form-select-sm" onchange="updatePrice(this, ${item.price_kilo}, ${item.price_half_sack}, ${item.price_sack})">
                        <option value="Kilo">Per Kilo (1kg)</option>
                        <option value="Half-Sack">Half-Sack (25kg)</option>
                        <option value="Sack">Full Sack (50kg)</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <input type="number" name="qty" value="1" class="form-control form-control-sm w-25" min="1" onchange="updateQty(this)">
                    <span class="fw-bold text-primary item-total" data-base-price="${item.price_kilo}">₱ ${item.price_kilo}</span>
                </div>
            </div>
        `;
        container.innerHTML = cartHtml;
        
        currentTotalAmount = parseFloat(item.price_kilo);
        document.getElementById('grand-total').innerText = '₱ ' + currentTotalAmount.toFixed(2);
        
        // Reset cash and recalculate change whenever a new item is tapped
        document.getElementById('cash-tendered').value = '';
        calculateChange();
    }

    function updatePrice(select, pk, ph, ps) {
        let basePrice = parseFloat(pk);
        if(select.value === 'Half-Sack') basePrice = parseFloat(ph);
        if(select.value === 'Sack') basePrice = parseFloat(ps);
        
        const priceSpan = document.querySelector('.item-total');
        priceSpan.setAttribute('data-base-price', basePrice);
        
        const qty = parseFloat(document.querySelector('input[name="qty"]').value);
        currentTotalAmount = basePrice * qty;
        
        priceSpan.innerText = '₱ ' + currentTotalAmount.toFixed(2);
        document.getElementById('grand-total').innerText = '₱ ' + currentTotalAmount.toFixed(2);
        
        calculateChange();
    }

    function updateQty(input) {
        const qty = parseFloat(input.value);
        const priceSpan = document.querySelector('.item-total');
        const basePrice = parseFloat(priceSpan.getAttribute('data-base-price'));
        
        currentTotalAmount = basePrice * qty;
        
        priceSpan.innerText = '₱ ' + currentTotalAmount.toFixed(2);
        document.getElementById('grand-total').innerText = '₱ ' + currentTotalAmount.toFixed(2);
        
        calculateChange();
    }

    function calculateChange() {
        const cashInput = document.getElementById('cash-tendered').value;
        const cashTendered = parseFloat(cashInput) || 0;
        const changeDueDisplay = document.getElementById('change-due');
        const checkoutBtn = document.getElementById('checkout-btn');

        // Check if a product is actually selected and if cash is enough
        if (currentTotalAmount > 0 && cashTendered >= currentTotalAmount) {
            const change = cashTendered - currentTotalAmount;
            changeDueDisplay.innerText = '₱ ' + change.toFixed(2);
            
            // Visual feedback: Green text and unlock button
            changeDueDisplay.classList.remove('text-secondary', 'text-danger');
            changeDueDisplay.classList.add('text-success');
            checkoutBtn.disabled = false;
        } else {
            changeDueDisplay.innerText = '₱ 0.00';
            
            // Visual feedback: Red text and lock button if money is lacking
            changeDueDisplay.classList.remove('text-secondary', 'text-success');
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