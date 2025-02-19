<?php

include 'includes/db.php';
global $pdo;
include 'includes/header.php';

class Product
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo; // Передача $pdo через конструктор
    }

    public function getProductById($productId)
    {
        $query = "SELECT p.*, 
                         c.name as category_name, 
                         m.name as manufacturer_name,
                         GROUP_CONCAT(DISTINCT pc.color_name SEPARATOR ', ') as available_colors
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id
                  LEFT JOIN product_colors pc ON p.product_id = pc.product_id
                  WHERE p.product_id = :id
                  GROUP BY p.product_id";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $productId]);
        return $stmt->fetch();
    }

    public function getProductImages($productId)
    {
        $query = "SELECT image_path, is_main_image 
                  FROM product_images 
                  WHERE product_id = :id 
                  ORDER BY is_main_image DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $productId]);
        return $stmt->fetchAll();
    }
}

$productId = $_GET['id'] ?? null;

if (!$productId || !is_numeric($productId)) {
    http_response_code(404);
    die('Товар не найден');
}

$productObj = new Product($pdo);
$product = $productObj->getProductById($productId);
$images = $productObj->getProductImages($productId);

?>
<div class="container product-page">
    <div class="product-gallery">
        <img src="assets<?= $images[0]['image_path'] ?>"
             alt="<?= htmlspecialchars($product['name']) ?>"
             class="product-main-image">

        <div class="product-thumbnails">
            <?php foreach($images as $index => $image): ?>
                <img src="assets<?= $image['image_path'] ?>"
                     data-full-image="<?= $image['image_path'] ?>"
                     alt="Миниатюра <?= $index + 1 ?>"
                     class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>">
            <?php endforeach; ?>
        </div>
    </div>

    <div class="product-details">
        <h2><?= htmlspecialchars($product['name']) ?></h2>
        <p class="description"><?= htmlspecialchars($product['description']) ?></p>

        <div class="product-meta">
            <p>Категория: <?= htmlspecialchars($product['category_name']) ?></p>
            <p>Производитель: <?= htmlspecialchars($product['manufacturer_name']) ?></p>
            <p>Материал: <?= htmlspecialchars($product['material']) ?></p>
            <p>Цвет: <?= htmlspecialchars($product['available_colors']) ?></p>
            <p>Размеры: <?= htmlspecialchars($product['dimensions']) ?></p>
        </div>

        <div class="pricing">
            <p class="price">
                <?php
                $finalPrice = $product['price'] * (1 - $product['discount_percentage']/100);
                echo number_format($finalPrice, 2, ',', ' ') . ' ₽';
                ?>
            </p>
            <?php if($product['discount_percentage'] > 0): ?>
                <p class="discount">Скидка: <?= $product['discount_percentage'] ?>%</p>
            <?php endif; ?>
        </div>
        <form class="product-actions" id="add-to-cart-form">
            <select name="color" required class="color-select">
                <?php
                $colors = array_map('trim', explode(',', $product['available_colors']));
                foreach($colors as $color):
                    ?>
                    <option value="<?= htmlspecialchars($color) ?>">
                        <?= htmlspecialchars($color) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="product_id" value="<?= $productId ?>">
            <input type="number" name="quantity" value="1" min="1" max="99" class="quantity-input">
            <button type="submit" class="catalog-btn border">
                <img src="assets/images/shopping-bag.svg" alt="Добавить в корзину">
            </button>
        </form>
        <div id="cartMessage" class="message" style="display: none;"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mainImage = document.querySelector('.product-main-image');
        const thumbnails = document.querySelectorAll('.product-thumbnail');

        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', () => {
                const newImageSrc = thumbnail.getAttribute('data-full-image');
                mainImage.src = `assets${newImageSrc}`;

                thumbnails.forEach(t => t.classList.remove('active'));
                thumbnail.classList.add('active');
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const addToCartForm = document.getElementById('add-to-cart-form');
        const cartMessage = document.getElementById('cartMessage');

        if (addToCartForm && cartMessage) {
            addToCartForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitButton = addToCartForm.querySelector('button[type="submit"]');
                const originalButtonContent = submitButton.innerHTML;
                submitButton.innerHTML = 'Добавление...';
                submitButton.disabled = true;

                try {
                    const formData = new FormData(addToCartForm);
                    formData.append('action', 'add_to_cart');

                    const response = await fetch('includes/autoUpdate/cartHandler.php', {
                        method: 'POST',
                        body: formData
                    });

                    // Считываем тело ответа один раз
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json(); // Читаем JSON только здесь
                    console.log('Ответ сервера:', result);

                    cartMessage.textContent = result.message;
                    cartMessage.style.display = 'block';
                    cartMessage.className = 'message ' + (result.success ? 'success' : 'error');

                    if (result.success) {
                        const cartCounter = document.querySelector('.cart-counter');
                        if (cartCounter) {
                            cartCounter.textContent = result.cart_count;
                        }
                        addToCartForm.querySelector('input[name="quantity"]').value = 1;
                    }

                } catch (error) {
                    console.error('Ошибка:', error);
                    cartMessage.textContent = 'Произошла ошибка при добавлении товара в корзину';
                    cartMessage.style.display = 'block';
                    cartMessage.className = 'message error';
                } finally {
                    submitButton.innerHTML = originalButtonContent;
                    submitButton.disabled = false;

                    setTimeout(() => {
                        cartMessage.style.display = 'none';
                    }, 3000);
                }
            });
        } else {
            console.error('Не найдены необходимые элементы формы');
        }
    });
</script>
<style>
        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
<?php
include 'includes/footer.php';