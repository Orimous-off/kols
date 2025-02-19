<?php
include 'includes/db.php';
include 'includes/header.php';
global $pdo;
?>
<div class="main-content">
    <section class="hero">
        <?php
            $stmt = $pdo->query("SELECT * FROM main_hero");
            $mainHeroContent = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="container h-max gap-20">
            <div class="col">
                <h1>
                    <?= $mainHeroContent['title'];?>
                </h1>
                <p>
                    <?= $mainHeroContent['subtitle'];?>
                </p>
            </div>
            <a href="/catalog" class="btn black">
                <span>
                    Купить сейчас
                </span>
                <svg height="20" width="20" fill="none" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <g clipPath="url(#clip0_2012_296)">
                        <path d="M4.16659 10.8333H13.4749L9.40825 14.9C9.08325 15.225 9.08325 15.7583 9.40825 16.0833C9.73325 16.4083 10.2583 16.4083 10.5833 16.0833L16.0749 10.5917C16.3999 10.2667 16.3999 9.74166 16.0749 9.41666L10.5916 3.91666C10.2666 3.59166 9.74159 3.59166 9.41659 3.91666C9.09159 4.24166 9.09159 4.76666 9.41659 5.09166L13.4749 9.16666H4.16659C3.70825 9.16666 3.33325 9.54166 3.33325 10C3.33325 10.4583 3.70825 10.8333 4.16659 10.8333Z" fill="white"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_2012_296">
                            <rect height="20" width="20" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
            </a>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="row align-items-center justify-content-sb">
                <h2>Наш каталог</h2>
            </div>
            <?php
            $sql = "
                SELECT 
                    p.product_id AS product_id,
                    p.name AS product_name,
                    p.price AS product_price,
                    p.discount_percentage AS product_discount,
                    COUNT(pc.color_name) AS color_count,
                    p.stock_quantity AS product_stock,
                    p.description AS product_description,
                    p.is_new,
                    p.is_featured,
                    pi.image_path AS main_image_path
                FROM 
                    products p
                LEFT JOIN 
                    product_colors pc ON p.product_id = pc.product_id
                LEFT JOIN 
                    product_images pi ON p.product_id = pi.product_id AND pi.is_main_image = 1
                GROUP BY 
                    p.product_id, pi.image_path
                LIMIT 6
            ";
            $stmt = $pdo->query($sql);
            $productsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="row gap-30 f-wrap justify-content-sb pt-25">
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
            <div class="row justify-content-center">
                <a href="/catalog" class="round-link">
                    Весь <br>
                    каталог
                </a>
            </div>
        </div>
    </section>
    <section class="quality-section">
        <div class="container">
            <h2>Качетсво мебели</h2>
            <div class="row justify-content-sb pt-25 f-wrap">
                <div class="col justify-content-sb">
                    <img src="assets/images/quality-1.jpg" alt="">
                    <p>
                        Мы используем только сертифицированные материалы высшего качества от проверенных поставщиков. Каждый компонент тщательно отбирается нашими экспертами.
                    </p>
                </div>
                <div class="col justify-content-sb">
                    <p>
                        Производство оснащено передовым оборудованием из Германии и Италии. Каждый этап создания мебели контролируется опытными специалистами.
                    </p>
                    <img src="assets/images/quality-2.jpg" alt="">
                </div>
                <div class="col justify-content-sb">
                    <img src="assets/images/quality-3.jpg" alt="">
                    <p>
                        Профессиональная доставка, сборка и установка мебели. Послегарантийное обслуживание и поддержка клиентов.
                    </p>
                </div>
            </div>
        </div>
    </section>
    <section class="promo">
        <div class="container">
            <div class="row align-items-center justify-content-sb">
                <h2>Покупайте у нас самое лучшее</h2>
                <div class="row gap-10 align-items-center">
                    <div class="block position-rel">
                        <div class="round-chip black">20%</div>
                        <svg height="63" width="72" fill="none" viewBox="0 0 72 63" xmlns="http://www.w3.org/2000/svg">
                            <path d="M36.1032 14.0827C36.7292 14.0827 37.3295 13.8648 37.7721 13.4769C38.2147 13.089 38.4634 12.5628 38.4634 12.0143V1.97597C38.4356 1.4434 38.1745 0.940743 37.7344 0.572669C37.2944 0.204594 36.7093 -0.000532089 36.1009 1.0366e-06C35.4926 0.000534162 34.908 0.206686 34.4688 0.575531C34.0295 0.944376 33.7696 1.44749 33.743 1.9801V12.0122C33.743 13.1498 34.798 14.0807 36.1032 14.0807V14.0827ZM43.6865 15.4024C43.9727 15.5079 44.2799 15.5627 44.5904 15.5634C44.9009 15.5642 45.2084 15.5111 45.4953 15.407C45.7822 15.3029 46.0427 15.15 46.2617 14.9572C46.4807 14.7643 46.6539 14.5353 46.7713 14.2834L51.1518 5.01041C51.3903 4.50426 51.3901 3.93586 51.1512 3.42984C50.9123 2.92382 50.4542 2.52149 49.8773 2.31106C49.2981 2.1016 48.6478 2.1015 48.0685 2.31077C47.4892 2.52005 47.028 2.92171 46.7854 3.42803L42.412 12.7031C42.1734 13.2092 42.1736 13.7776 42.4126 14.2836C42.6515 14.7896 43.1096 15.192 43.6865 15.4024ZM50.118 19.167C50.3367 19.3598 50.5967 19.5127 50.8831 19.6171C51.1694 19.7215 51.4765 19.7752 51.7867 19.7752C52.0968 19.7752 52.4039 19.7215 52.6903 19.6171C52.9767 19.5127 53.2367 19.3598 53.4553 19.167L61.5508 12.066C61.7761 11.875 61.9557 11.6467 62.0792 11.3942C62.2026 11.1418 62.2675 10.8703 62.27 10.5957C62.2725 10.321 62.2126 10.0487 62.0937 9.79456C61.9748 9.54043 61.7994 9.30959 61.5776 9.11552C61.3559 8.92144 61.0922 8.76801 60.8021 8.66419C60.512 8.56036 60.2011 8.50822 59.8878 8.5108C59.5744 8.51338 59.2647 8.57063 58.9769 8.67921C58.689 8.78779 58.4287 8.94553 58.2111 9.14322L50.1157 16.2381C49.6735 16.6269 49.4252 17.1536 49.4252 17.7026C49.4252 18.2515 49.6735 18.7782 50.1157 19.167H50.118ZM54.4207 24.8036C54.5387 25.0547 54.7123 25.2829 54.9314 25.475C55.1505 25.6671 55.4108 25.8194 55.6972 25.923C55.9837 26.0266 56.2908 26.0795 56.6007 26.0788C56.9106 26.078 57.2173 26.0235 57.5031 25.9185L68.0767 22.0753C68.3656 21.9727 68.6284 21.8211 68.85 21.629C69.0717 21.4369 69.2477 21.2083 69.368 20.9563C69.4882 20.7043 69.5503 20.434 69.5507 20.1608C69.5511 19.8877 69.4898 19.6171 69.3703 19.3649C69.2507 19.1126 69.0754 18.8836 68.8543 18.6911C68.6333 18.4985 68.3709 18.3462 68.0823 18.243C67.7937 18.1398 67.4847 18.0877 67.173 18.0898C66.8614 18.0918 66.5532 18.1479 66.2665 18.2548L55.6928 22.096C54.4891 22.5345 53.9179 23.7466 54.4183 24.8015L54.4207 24.8036ZM69.7383 29.3749L58.2866 29.3791C56.9885 29.3791 55.9265 30.3057 55.9335 31.4475C55.9335 32.5935 56.9885 33.516 58.289 33.516L69.743 33.5119C70.3507 33.4881 70.9245 33.2597 71.3449 32.8744C71.7654 32.4891 72 31.9765 72 31.4434C72 30.9103 71.7654 30.3977 71.3449 30.0124C70.9245 29.6271 70.3507 29.3988 69.743 29.3749H69.7383ZM68.0862 40.8136L57.5125 36.9766C57.2261 36.8721 56.9191 36.8181 56.6089 36.8177C56.2987 36.8173 55.9915 36.8705 55.7048 36.9742C55.418 37.078 55.1575 37.2302 54.9379 37.4223C54.7184 37.6143 54.5442 37.8424 54.4254 38.0935C53.9297 39.1526 54.4962 40.3627 55.6999 40.8032L66.2806 44.6361C66.5681 44.7451 66.8775 44.8029 67.1907 44.8062C67.504 44.8094 67.8148 44.7581 68.1052 44.6551C68.3956 44.552 68.6597 44.3994 68.8821 44.2062C69.1046 44.0129 69.2809 43.7827 69.401 43.5291C69.521 43.2756 69.5823 43.0036 69.5813 42.729C69.5802 42.4545 69.5169 42.1829 69.395 41.93C69.2731 41.6771 69.095 41.448 68.8711 41.256C68.6472 41.0639 68.382 40.9129 68.0909 40.8115L68.0862 40.8136ZM53.4695 43.7301C53.0244 43.3533 52.4282 43.1448 51.8093 43.1496C51.1905 43.1543 50.5985 43.3718 50.1609 43.7553C49.7233 44.1388 49.4751 44.6576 49.4697 45.2C49.4644 45.7423 49.7022 46.2648 50.1322 46.6549L58.2371 53.7498C58.68 54.1373 59.2804 54.3548 59.9062 54.3544C60.5321 54.354 61.1321 54.1358 61.5744 53.7477C62.4949 52.9369 62.4949 51.6275 61.5744 50.8208L53.4695 43.7301ZM46.7901 48.6179C46.5501 48.111 46.0901 47.7085 45.5114 47.4988C44.9326 47.2891 44.2826 47.2895 43.7042 47.4999C43.1258 47.7103 42.6665 48.1134 42.4272 48.6206C42.188 49.1278 42.1884 49.6975 42.4285 50.2044L46.8185 59.4712C46.9373 59.7223 47.1115 59.9504 47.331 60.1424C47.5506 60.3345 47.8111 60.4868 48.0978 60.5905C48.3846 60.6942 48.6918 60.7474 49.002 60.747C49.3122 60.7466 49.6192 60.6926 49.9056 60.5882C51.1093 60.1538 51.6805 58.9396 51.1801 57.8785L46.7901 48.6179ZM36.1268 48.831C35.5008 48.831 34.9005 49.0489 34.4579 49.4368C34.0153 49.8247 33.7666 50.3508 33.7666 50.8994L33.776 60.9315C33.776 61.4801 34.0247 62.0062 34.4673 62.3942C34.9099 62.7821 35.5103 63 36.1362 63C37.4414 63 38.4964 62.0733 38.4917 60.9315L38.4846 50.8953C38.4846 49.7576 37.4249 48.8268 36.1268 48.831ZM28.5364 47.5154C28.2499 47.4118 27.9428 47.3586 27.6328 47.3589C27.3229 47.3593 27.016 47.4131 26.7297 47.5174C26.4435 47.6217 26.1835 47.7744 25.9646 47.9668C25.7457 48.1592 25.5722 48.3875 25.454 48.6386L21.0829 57.9116C20.9359 58.2263 20.8794 58.5678 20.9184 58.9062C20.9575 59.2445 21.0908 59.5693 21.3067 59.8519C21.5225 60.1344 21.8144 60.3662 22.1565 60.5267C22.4986 60.6872 22.8804 60.7715 23.2685 60.7723C23.7355 60.7722 24.192 60.6507 24.5802 60.4231C24.9684 60.1956 25.2709 59.8722 25.4493 59.4939L29.8227 50.2168C30.0607 49.7097 30.0592 49.1404 29.8184 48.6342C29.5776 48.128 29.1174 47.7263 28.5388 47.5175L28.5364 47.5154ZM22.1025 43.7612C21.883 43.5691 21.6225 43.4169 21.3358 43.3131C21.0491 43.2094 20.7419 43.1562 20.4317 43.1566C20.1215 43.1569 19.8145 43.2109 19.5282 43.3154C19.2418 43.4198 18.9817 43.5727 18.7629 43.7653L10.6792 50.8663C10.4601 51.0585 10.2863 51.2867 10.1677 51.5377C10.0492 51.7887 9.98825 52.0578 9.98836 52.3295C9.98847 52.6012 10.0496 52.8702 10.1684 53.1212C10.2871 53.3721 10.4611 53.6002 10.6804 53.7922C10.8997 53.9842 11.16 54.1366 11.4464 54.2404C11.7329 54.3443 12.0399 54.3977 12.3499 54.3976C12.6599 54.3975 12.9668 54.3439 13.2532 54.2399C13.5396 54.1358 13.7997 53.9833 14.0189 53.7912L22.1072 46.688C22.3264 46.4957 22.5001 46.2673 22.6185 46.0161C22.7368 45.7648 22.7975 45.4956 22.7971 45.2237C22.7967 44.9519 22.7351 44.6828 22.6159 44.4319C22.4967 44.1809 22.3223 43.953 22.1025 43.7612ZM17.7975 38.1349C17.5575 37.6283 17.0976 37.226 16.5191 37.0165C15.9406 36.8071 15.2908 36.8076 14.7128 37.0179L4.13909 40.8653C3.58881 41.0918 3.15936 41.4956 2.94006 41.9925C2.72075 42.4895 2.72849 43.0414 2.96166 43.5335C3.19482 44.0255 3.63546 44.4199 4.19193 44.6345C4.7484 44.8491 5.37785 44.8675 5.94936 44.6858L16.523 40.8384C17.722 40.3937 18.2932 39.1836 17.7975 38.1287V38.1349ZM16.2728 31.4848C16.2728 30.9362 16.0242 30.4101 15.5816 30.0221C15.1389 29.6342 14.5386 29.4163 13.9127 29.4163L2.46572 29.4308C2.14721 29.4183 1.82911 29.4625 1.53062 29.5607C1.23214 29.6589 0.959451 29.8091 0.72902 30.0022C0.498589 30.1953 0.315191 30.4274 0.189894 30.6843C0.0645975 30.9412 0 31.2178 0 31.4972C0 31.7766 0.0645975 32.0531 0.189894 32.3101C0.315191 32.567 0.498589 32.799 0.72902 32.9922C0.959451 33.1853 1.23214 33.3355 1.53062 33.4337C1.82911 33.5319 2.14721 33.5761 2.46572 33.5636L13.9174 33.5512C15.2202 33.5512 16.2776 32.6204 16.2728 31.4827V31.4848ZM4.10369 22.1353L14.6892 25.962C14.9772 26.0726 15.2875 26.1318 15.6019 26.1358C15.9164 26.1399 16.2286 26.0889 16.5202 25.9858C16.8118 25.8826 17.077 25.7295 17.3001 25.5353C17.5232 25.341 17.6998 25.1097 17.8195 24.8549C17.9392 24.6 17.9996 24.3267 17.9971 24.0511C17.9946 23.7756 17.9293 23.5032 17.805 23.25C17.6807 22.9969 17.4999 22.768 17.2733 22.5769C17.0466 22.3859 16.7787 22.2364 16.4853 22.1374L5.9116 18.3086C5.34144 18.1353 4.71728 18.1597 4.1669 18.3766C3.61653 18.5935 3.18159 18.9866 2.95114 19.4753C2.72069 19.9641 2.71216 20.5116 2.92731 21.0058C3.14246 21.4999 3.565 21.9032 4.10841 22.1332L4.10369 22.1353ZM18.7204 19.1981C18.94 19.39 19.2007 19.5421 19.4875 19.6456C19.7743 19.7492 20.0815 19.8022 20.3917 19.8016C20.7018 19.801 21.0088 19.7469 21.2951 19.6423C21.5814 19.5376 21.8413 19.3846 22.06 19.1919C22.5009 18.8026 22.7474 18.2758 22.7452 17.7273C22.743 17.1788 22.4923 16.6536 22.0482 16.267L13.9433 9.18666C13.5001 8.79943 12.8995 8.58233 12.2737 8.5831C11.6479 8.58388 11.048 8.80247 10.606 9.19079C10.1636 9.57869 9.91499 10.1047 9.91499 10.6532C9.91499 11.2017 10.1636 11.7277 10.606 12.1156L18.7204 19.1981ZM25.3926 14.3041C25.6336 14.8107 26.0944 15.2127 26.6736 15.4215C27.2527 15.6304 27.9029 15.6291 28.4809 15.4179C29.059 15.2067 29.5177 14.8029 29.756 14.2953C29.9944 13.7877 29.9929 13.218 29.7519 12.7113L25.3549 3.44458C25.1139 2.93768 24.653 2.53544 24.0736 2.32636C23.4942 2.11727 22.8438 2.11847 22.2654 2.32967C21.687 2.54088 21.228 2.94481 20.9895 3.45258C20.7509 3.96036 20.7522 4.5304 20.9932 5.0373L25.3926 14.3041Z" fill="#565656"/>
                        </svg>
                    </div>
                    <p>
                        Скидка на <br>
                        данные товары
                    </p>
                </div>
            </div>
            <?php
            $sqlDiscounted = "
                SELECT 
                    p.product_id AS product_id,
                    p.name AS product_name,
                    p.price AS product_price,
                    p.discount_percentage AS product_discount,
                    COUNT(pc.color_name) AS color_count,
                    p.stock_quantity AS product_stock,
                    p.description AS product_description,
                    p.is_new,
                    p.is_featured,
                    pi.image_path AS main_image_path
                FROM 
                    products p
                LEFT JOIN 
                    product_colors pc ON p.product_id = pc.product_id
                LEFT JOIN 
                    product_images pi ON p.product_id = pi.product_id AND pi.is_main_image = 1
                WHERE 
                    p.discount_percentage > 0
                GROUP BY 
                    p.product_id, pi.image_path
                LIMIT 3
            ";
            $stmtDiscounted = $pdo->query($sqlDiscounted);
            $discountedProducts = $stmtDiscounted->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="row gap-30 justify-content-center f-wrap pt-25">
                <?php foreach ($discountedProducts as $discountedProduct): ?>
                    <div class="product-card">
                        <div class="row justify-content-end w100">
                            <div class="chip">
                                <?php
                                $colorCount = $discountedProduct['color_count'];
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
                        <a href="/product?id=<?= $discountedProduct['product_id']; ?>">
                            <img src="assets/<?= $discountedProduct['main_image_path'] ?>" alt="" class="catalog-main-img">
                        </a>
                        <div class="row align-items-center justify-content-sb w100">
                            <div class="col">
                                <a href="/product?id=<?= $discountedProduct['product_id']; ?>" class="product-name"><?= $discountedProduct['product_name']; ?></a>
                                <span class="product-price">
                                    <?php
                                    $originalPrice = $discountedProduct['product_price'];
                                    $discountedPrice = $originalPrice * (1 - $discountedProduct['product_discount'] / 100);

                                    // Проверяем есть ли скидка и отличается ли цена со скидкой от оригинальной
                                    if ($discountedProduct['product_discount'] > 0 && $discountedPrice < $originalPrice):
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
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="email-subscription">
        <div class="container">
            <div class="col justify-content-sb">
                <h1 class="text-left">
                    ДАВАЙТЕ <br>
                    ПОГОВОРИМ
                </h1>
                <div class="row w100 justify-content-end">
                    <div class="card col justify-content-center align-items-center text-center gap-10">
                        <p>
                            Получайте все новости о нашем магазине. <br>Просто подпишитесь и получайте все новости.
                        </p>
                        <form class="subscriptionForm" method="post">
                            <input type="email" name="email" placeholder="Ваша почта">
                            <button type="submit">
                                <img src="assets/images/arrow.svg" alt="arrow">
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="advantages">
        <div class="container">
            <div class="row gap-30 align-items-stretch text-center">
                <?php
                $stmt = $pdo->query("SELECT * FROM advantages");
                $advantagesItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php foreach ($advantagesItems as $advantagesItem): ?>
                    <div class="card sec col justify-content-center align-items-center">
                        <img src="assets<?= $advantagesItem['icon_url']?>" alt="">
                        <div class="col">
                            <h3><?= $advantagesItem['title']?></h3>
                            <p>
                                <?= $advantagesItem['subtitle']?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="reviews">
        <div class="container">
            <div class="row">
                <img src="assets/images/reviews.jpg" alt="">
                <div class="col justify-content-sb">
                    <h2>
                        Слова наших <br>
                        Довольных клиентов
                    </h2>
                    <div class="col">
                        <?php
                            $stmt = $pdo->query("SELECT * FROM reviews ORDER BY id DESC LIMIT 3");
                            $reviewsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="reviews-slider">
                            <div class="reviews-wrapper">
                                <?php
                                $stmt = $pdo->query("SELECT * FROM reviews ORDER BY id DESC LIMIT 3");
                                $reviewsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <?php foreach ($reviewsItems as $reviewsItem): ?>
                                    <div class="review-flex">
                                        <div class="review-card">
                                            <span>
                                                <?= $reviewsItem['title']; ?>
                                            </span>
                                            <p>
                                                <?= $reviewsItem['description'];?>
                                            </p>
                                            <div class="review-author">
                                                <img src="assets/images/account.svg" alt="">
                                                <span>
                                                    <?= $reviewsItem['author'];?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="dots">
                                <?php foreach ($reviewsItems as $index => $reviewsItem): ?>
                                    <span
                                        class="
                                            dot
                                            <?php
                                                if($index === 0){
                                                    echo ' active';
                                                }
                                            ?>
                                        "
                                        data-index="<?= $index; ?>">

                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    startAutoSlide();
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
        .hero .container {
            background: url("assets/<?= $mainHeroContent['image_url']?>") no-repeat center center;
        }
    </style>
</div>
<?php
include 'includes/footer.php';
?>
