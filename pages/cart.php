<?php
ob_start();
session_start();
include 'includes/db.php';
require_once 'includes/auth.php';
global $pdo;
include 'includes/header.php';

$auth = new Auth($pdo);
if (!$auth->checkAuth()) {
    header('Location: /login');
    exit;
}

class CartPage {
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getUserInfo()
    {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCartItems() {
        $query = "
            SELECT 
                ci.cart_item_id,
                ci.quantity,
                ci.color_name,
                p.product_id,
                p.name,
                p.price,
                p.discount_percentage,
                MIN(pi.image_path) as image_path  -- Using MIN to get a single image path
            FROM carts c
            JOIN cart_items ci ON c.cart_id = ci.cart_id
            JOIN products p ON ci.product_id = p.product_id
            LEFT JOIN (
                SELECT product_id, image_path
                FROM product_images
                WHERE is_main_image = 1
            ) pi ON p.product_id = pi.product_id
            WHERE c.user_id = ? AND c.status = 'active'
            GROUP BY 
                ci.cart_item_id,
                ci.quantity,
                ci.color_name,
                p.product_id,
                p.name,
                p.price,
                p.discount_percentage
            ORDER BY ci.created_at DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateTotals($items) {
        $subtotal = 0;
        $totalDiscount = 0;

        foreach ($items as $item) {
            $originalPrice = $item['price'] * $item['quantity'];
            $discountAmount = $originalPrice * ($item['discount_percentage'] / 100);

            $subtotal += $originalPrice;
            $totalDiscount += $discountAmount;
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $totalDiscount,
            'total' => $subtotal - $totalDiscount
        ];
    }
}

if (!isset($_SESSION['user_id'])) {
    ob_clean(); // Очищаем буфер вывода
    echo '<script>window.location.href = "/login";</script>';
    exit;
}

$cartPage = new CartPage($pdo, $_SESSION['user_id']);
$cartItems = $cartPage->getCartItems();
$userInfo = $cartPage->getUserInfo($_SESSION['user_id']);
$totals = $cartPage->calculateTotals($cartItems);
?>
    <div class="main-content container cart-page">
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <p>Ваша корзина пуста</p>
                <img src="/assets/images/emty-cart.svg" alt="">
                <a href="/catalog" class="btn">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-item-id="<?= $item['cart_item_id'] ?>">
                            <div class="item-image">
                                <a href="/product?id=<?= $item['product_id']; ?>">
                                    <img src="assets<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                </a>
                            </div>
                            <div class="item-details">
                                <h3><a href="/product?id=<?= $item['product_id']; ?>"><?= htmlspecialchars($item['name']) ?></a></h3>
                                <p class="item-color">Цвет: <?= htmlspecialchars($item['color_name']) ?></p>
                                <div class="item-price">
                                    <?php
                                    $originalPrice = $item['price'];
                                    $finalPrice = $originalPrice * (1 - $item['discount_percentage']/100);
                                    ?>
                                    <span class="current-price"><?= number_format($finalPrice, 2, ',', ' ') ?> ₽</span>
                                    <?php if ($item['discount_percentage'] > 0): ?>
                                        <span class="original-price"><?= number_format($originalPrice, 2, ',', ' ') ?> ₽</span>
                                        <span class="discount">-<?= $item['discount_percentage'] ?>%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="item-actions col align-items-end">
                                <div class="quantity-controls">
                                    <button class="quantity-btn minus">-</button>
                                    <input type="number"
                                           class="quantity-input"
                                           value="<?= $item['quantity'] ?>"
                                           min="1"
                                           max="99">
                                    <button class="quantity-btn plus">+</button>
                                </div>
                                <button class="remove-item row justify-content-end">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary">
                    <h2>Итого</h2>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Сумма</span>
                            <span><?= number_format($totals['subtotal'], 2, ',', ' ') ?> ₽</span>
                        </div>
                        <?php if ($totals['discount'] > 0): ?>
                            <div class="summary-row discount">
                                <span>Скидка</span>
                                <span>-<?= number_format($totals['discount'], 2, ',', ' ') ?> ₽</span>
                            </div>
                        <?php endif; ?>
                        <div class="summary-row total">
                            <span>К оплате</span>
                            <span><?= number_format($totals['total'], 2, ',', ' ') ?> ₽</span>
                        </div>
                    </div>
                    <p class="">
                        * оплата только при получении
                    </p>
                    <button class="checkout-btn">Оформить заказ</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- Add this right before the closing body tag -->
    <div id="checkoutModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Оформление заказа</h2>
            <form id="checkoutForm">
                <div class="form-group">
                    <label for="phone">Номер телефона верный?</label>
                    <input type="text" id="phone" name="phone" value="<?= $userInfo['phone_number']?>" placeholder="<?= $userInfo['phone_number']?>" required>
                </div>
                <button type="submit" class="checkout-btn">Подтвердить заказ</button>
            </form>
        </div>
    </div>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        #checkoutForm input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5ee;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: #333;
        }

        #checkoutForm input::placeholder {
            color: #aab;
        }
    </style>
    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('checkoutModal');
            const checkoutBtn = document.querySelector('.checkout-btn');
            const closeBtn = document.querySelector('.close');
            const phoneInput = document.getElementById('phone');
            const checkoutForm = document.getElementById('checkoutForm');

            // Initialize phone mask
            const phoneMask = IMask(phoneInput, {
                mask: '+7(000)000-00-00'
            });

            // Get user's phone if available
            fetch('includes/autoUpdate/getUserPhone.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.phone) {
                        phoneMask.value = data.phone;
                    }
                })
                .catch(error => console.error('Error fetching phone:', error));

            checkoutBtn.addEventListener('click', (e) => {
                if (!e.target.closest('form')) { // Only open modal if not inside form
                    modal.style.display = 'block';
                }
            });

            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });

            checkoutForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                try {
                    const response = await fetch('includes/autoUpdate/orderHandler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `phone=${encodeURIComponent(phoneInput.value)}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('Заказ успешно создан!');
                        window.location.href = '/profile'; // Redirect to orders page
                    } else {
                        alert(result.message || 'Произошла ошибка при создании заказа');
                    }
                } catch (error) {
                    console.error('Error creating order:', error);
                    alert('Произошла ошибка при создании заказа');
                }
            });
        });
    </script>
    <style>
        .cart-page {
            padding: 2rem 0;
        }

        .empty-cart {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
            align-items: center;
            text-align: center;
            padding: 3rem 0;
        }

        .empty-cart img {
            height: 300px;
        }

        .cart-container {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
        }

        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 1rem;
            padding: 1rem;
            background: #fff;
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .item-image img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-details h3 {
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
        }

        .item-color {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .item-price {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .current-price {
            font-weight: bold;
        }

        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9rem;
        }

        .discount {
            color: #e53e3e;
            font-size: 0.9rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
            cursor: pointer;
        }

        .quantity-input {
            width: 50px;
            height: 30px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .remove-item {
            width: fit-content;
            padding: 5px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .remove-item i {
            color: #ff0033;
            font-size: 18px;
        }

        .cart-summary {
            display: flex;
            flex-direction: column;
            gap: 5px;
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.1);
            height: fit-content;
        }

        .summary-details {
            margin: 1rem 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .summary-row.total {
            font-weight: bold;
            font-size: 1.1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #ddd;
        }

        .checkout-btn {
            width: 100%;
            padding: 0.75rem;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .checkout-btn:hover {
            background: #45a049;
        }

        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }

            .cart-item {
                grid-template-columns: 100px 1fr;
            }

            .item-actions {
                grid-column: 1 / -1;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cartItems = document.querySelectorAll('.cart-item');

            cartItems.forEach(item => {
                const quantityInput = item.querySelector('.quantity-input');
                const minusBtn = item.querySelector('.minus');
                const plusBtn = item.querySelector('.plus');
                const removeBtn = item.querySelector('.remove-item');
                const itemId = item.dataset.itemId;

                minusBtn.addEventListener('click', () => {
                    let value = parseInt(quantityInput.value);
                    if (value > 1) {
                        quantityInput.value = value - 1;
                        updateCartItem(itemId, value - 1);
                    }
                });

                plusBtn.addEventListener('click', () => {
                    let value = parseInt(quantityInput.value);
                    if (value < 99) {
                        quantityInput.value = value + 1;
                        updateCartItem(itemId, value + 1);
                    }
                });

                quantityInput.addEventListener('change', () => {
                    let value = parseInt(quantityInput.value);
                    if (value < 1) value = 1;
                    if (value > 99) value = 99;
                    quantityInput.value = value;
                    updateCartItem(itemId, value);
                });

                removeBtn.addEventListener('click', () => {
                    removeCartItem(itemId);
                });
            });

            async function updateCartItem(itemId, quantity) {
                try {
                    const response = await fetch('includes/autoUpdate/cartHandler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_quantity&cart_item_id=${itemId}&quantity=${quantity}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        updateCartTotals(result.totals);
                        if (result.cart_count !== undefined) {
                            updateCartCounter(result.cart_count);
                        }
                    } else {
                        alert(result.message || 'Произошла ошибка при обновлении количества');
                    }
                } catch (error) {
                    console.error('Ошибка при обновлении корзины:', error);
                    alert('Произошла ошибка при обновлении корзины');
                }
            }

            async function removeCartItem(itemId) {
                if (!confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
                    return;
                }

                try {
                    const response = await fetch('includes/autoUpdate/cartHandler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=remove_item&cart_item_id=${itemId}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                        itemElement.remove();

                        updateCartTotals(result.totals);
                        if (result.cart_count !== undefined) {
                            updateCartCounter(result.cart_count);
                        }

                        // Если корзина пуста, перезагрузим страницу
                        if (result.cart_count === 0) {
                            location.reload();
                        }
                    } else {
                        alert(result.message || 'Произошла ошибка при удалении товара');
                    }
                } catch (error) {
                    console.error('Ошибка при удалении товара:', error);
                    alert('Произошла ошибка при удалении товара');
                }
            }

            function updateCartTotals(totals) {
                const summaryDetails = document.querySelector('.summary-details');
                if (summaryDetails && totals) {
                    summaryDetails.innerHTML = `
                <div class="summary-row">
                    <span>Сумма</span>
                    <span>${formatPrice(totals.subtotal)} ₽</span>
                </div>
                ${totals.discount > 0 ? `
                    <div class="summary-row discount">
                        <span>Скидка</span>
                        <span>-${formatPrice(totals.discount)} ₽</span>
                    </div>
                ` : ''}
                <div class="summary-row total">
                    <span>К оплате</span>
                    <span>${formatPrice(totals.total)} ₽</span>
                </div>
            `;
                }
            }

            function updateCartCounter(count) {
                const counter = document.querySelector('.cart-counter');
                if (counter) {
                    counter.textContent = count;
                }
            }

            function formatPrice(price) {
                return new Intl.NumberFormat('ru-RU', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(price);
            }
        });
    </script>

<?php include 'includes/footer.php'; ?>