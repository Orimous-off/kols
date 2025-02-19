<?php
include 'includes/db.php';
include 'includes/header.php';
global $pdo;

// Search and Filter Parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sortBy = $_GET['sort'] ?? 'default';

// Fetch Categories
$categoriesQuery = "SELECT * FROM categories";
$categoriesStmt = $pdo->query($categoriesQuery);
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Dynamic SQL with Filters
$sql = "
    SELECT 
        p.product_id AS product_id,
        p.name AS product_name,
        p.price AS product_price,
        p.discount_percentage AS product_discount,
        COUNT(pc.color_name) AS color_count,
        p.stock_quantity AS product_stock,
        p.description AS product_description,
        p.category_id,
        p.is_new,
        p.is_featured,
        pi.image_path AS main_image_path
    FROM 
        products p
    LEFT JOIN 
        product_colors pc ON p.product_id = pc.product_id
    LEFT JOIN 
        product_images pi ON p.product_id = pi.product_id AND pi.is_main_image = 1
    LEFT JOIN
        categories c ON p.category_id = c.category_id
    WHERE 1=1
";

# необходимо написать код для фильтрации и сортировки

// Apply Search Filter
if (!empty($search)) {
    $sql .= " AND (
        p.name LIKE :search OR 
        p.description LIKE :search OR 
        c.name LIKE :search
    )";
}

// Apply Category Filter
if (!empty($category)) {
    $sql .= " AND p.category_id = :category";
}

// Apply Price Range
if (!empty($minPrice)) {
    $sql .= " AND p.price >= :min_price";
}
if (!empty($maxPrice)) {
    $sql .= " AND p.price <= :max_price";
}

$sql .= " GROUP BY 
    p.product_id, 
    p.name, 
    p.price, 
    p.discount_percentage, 
    p.stock_quantity, 
    p.description, 
    p.category_id, 
    p.is_new, 
    p.is_featured, 
    pi.image_path";

// Sorting
switch($sortBy) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    default:
        $sql .= " ORDER BY p.product_id DESC";
}

// Proper GROUP BY to avoid conflicts with ONLY_FULL_GROUP_BY

$sql .= " LIMIT 12";

$stmt = $pdo->prepare($sql);

// Bind Search Parameters
if (!empty($search)) {
    $searchParam = "%{$search}%";
    $stmt->bindParam(':search', $searchParam);
}
if (!empty($category)) {
    $stmt->bindParam(':category', $category);
}
if (!empty($minPrice)) {
    $stmt->bindParam(':min_price', $minPrice);
}
if (!empty($maxPrice)) {
    $stmt->bindParam(':max_price', $maxPrice);
}

$stmt->execute();
$productsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="main-content catalog-page">
        <section class="catalog" style="margin-top: 30px;">
            <div class="container">
                <!-- Search and Filter Form -->
                <form method="GET" class="catalog-filters">
                    <div class="search-container">
                        <input
                                type="text"
                                name="search"
                                placeholder="Поиск товаров..."
                                value="<?= htmlspecialchars($search) ?>"
                        >
                    </div>

                    <div class="filter-container">
                        <select name="category">
                            <option value="">Все категории</option>
                            <?php foreach ($categories as $cat): ?>
                                <option
                                        value="<?= $cat['category_id'] ?>"
                                    <?= $category == $cat['category_id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="sort">
                            <option value="default">Сортировка</option>
                            <option value="price_asc" <?= $sortBy == 'price_asc' ? 'selected' : '' ?>>Цена: по возрастанию</option>
                            <option value="price_desc" <?= $sortBy == 'price_desc' ? 'selected' : '' ?>>Цена: по убыванию</option>
                            <option value="newest" <?= $sortBy == 'newest' ? 'selected' : '' ?>>Сначала новые</option>
                        </select>

                        <div class="price-range">
                            <input
                                    type="number"
                                    name="min_price"
                                    placeholder="Мин. цена"
                                    value="<?= htmlspecialchars($minPrice) ?>"
                            >
                            <input
                                    type="number"
                                    name="max_price"
                                    placeholder="Макс. цена"
                                    value="<?= htmlspecialchars($maxPrice) ?>"
                            >
                        </div>

                        <button type="submit" class="filter-submit">Применить</button>
                    </div>
                </form>
                <div class="row gap-30 f-wrap">
                    <?php foreach ($productsItems as $productItem): ?>
                        <div class="product-card">
                            <div class="row justify-content-end w100">
                                <div class="chip">
                                    <?php
                                    $colorCount = $productItem['color_count'];
                                    if ($colorCount % 10 == 1 && $colorCount % 100 != 11) {
                                        echo $colorCount . ' цвет';
                                    } elseif ($colorCount % 10 >= 2 && $colorCount % 10 <= 4 && ($colorCount % 100 < 10 || $colorCount % 100 >= 20)) {
                                        echo $colorCount . ' цвета';
                                    } else {
                                        echo $colorCount . ' цветов';
                                    }
                                    ?>
                                </div>
                            </div>
                            <a href="/product?id=<?= $productItem['product_id']; ?>">
                                <img src="assets/<?= $productItem['main_image_path'] ?>" alt="" class="catalog-main-img">
                            </a>
                            <div class="row align-items-center justify-content-sb w100">
                                <div class="col">
                                    <a href="/product?id=<?= $productItem['product_id']; ?>" class="product-name"><?= $productItem['product_name']; ?></a>
                                    <span class="product-price">
                                        <?php
                                        $originalPrice = $productItem['product_price'];
                                        $discountedPrice = $originalPrice * (1 - $productItem['product_discount'] / 100);

                                        // Проверяем есть ли скидка и отличается ли цена со скидкой от оригинальной
                                        if ($productItem['product_discount'] > 0 && $discountedPrice < $originalPrice):
                                            ?>
                                            <span class="old-price">
                                                <?= number_format($originalPrice, 2, ',', ' ') . ' ₽'; ?>
                                            </span>
                                            <span class="discounted-price">
                                                <?= number_format($discountedPrice, 2, ',', ' ') . ' ₽'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="regular-price">
                                                <?= number_format($originalPrice, 2, ',', ' ') . ' ₽'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <a href="/product?id=<?= $productItem['product_id']; ?>" class="catalog-btn">
                                    <img src="assets/images/shopping-bag.svg" alt="">
                                </a>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
            <?php if(count($productsItems) == 12): ?>
                <div class="pagination">
                    <a href="?page=2" class="btn">Показать ещё</a>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let debounceTimer;

            function updateCatalog() {
                const queryParams = new URLSearchParams({
                    search: $('input[name="search"]').val(),
                    category: $('select[name="category"]').val(),
                    sort: $('select[name="sort"]').val(),
                    min_price: $('input[name="min_price"]').val(),
                    max_price: $('input[name="max_price"]').val()
                });

                // Update URL without reloading the page
                window.history.replaceState({}, '', `${window.location.pathname}?${queryParams}`);

                // Show loading state
                $('.row.gap-30.f-wrap').addClass('loading').css('opacity', '0.6');

                $.ajax({
                    url: window.location.pathname,
                    type: 'GET',
                    data: queryParams.toString(),
                    success: function(data) {
                        // Extract the product grid content from the response
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data;
                        const newProducts = $(tempDiv).find('.row.gap-30.f-wrap').html();

                        // Update the product grid
                        $('.row.gap-30.f-wrap').html(newProducts).removeClass('loading').css('opacity', '1');
                    },
                    error: function() {
                        $('.row.gap-30.f-wrap').removeClass('loading').css('opacity', '1');
                        // Optionally show error message
                    }
                });
            }

            // Debounced function to prevent too many requests
            function debouncedUpdate() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(updateCatalog, 500);
            }

            // Remove the submit button from HTML
            $('.filter-submit').remove();

            // Add event listeners for all filter inputs
            $('input[name="search"], select[name="category"], select[name="sort"], input[name="min_price"], input[name="max_price"]')
                .on('input change', debouncedUpdate);

            // Add loading animation style
            $('<style>')
                .text(`
            .row.gap-30.f-wrap.loading {
                position: relative;
                transition: opacity 0.3s;
            }
            .row.gap-30.f-wrap.loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #4a90e2;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: translate(-50%, -50%) rotate(0deg); }
                100% { transform: translate(-50%, -50%) rotate(360deg); }
            }
        `)
                .appendTo('head');
        });
    </script>
<?php
include 'includes/footer.php';
?>