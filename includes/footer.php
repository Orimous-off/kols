<?php
include 'db.php';
global $pdo;

$request = $_SERVER['REQUEST_URI'];
$clean_request = strtok($request, '?');

$stmt = $pdo->query("SELECT title, link FROM navigation WHERE is_visible = 1 ORDER BY position ASC");
$menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM company_info LIMIT 1");
$companyInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<div id="subscriptionResult" class="notification"></div>
<footer>
    <div class="footer-content container">
        <div class="row justify-content-sb align-items-center">
            <div class="row align-items-center gap-10">
                <a href="/" class="footer-logo">
                    <?php echo $companyInfo['logoName']?>
                </a>
                <div class="line"></div>
                <?php
                $stmt = $pdo->query("SELECT * FROM social_networks");
                $social_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="links row align-items-center gap-10">
                    <?php
                        foreach ($social_links as $link) {
                    ?>
                        <a href="<?= $link['link'];?>">
                            <i class="fa-brands fa-<?= $link['title'];?>"></i>
                        </a>
                    <?php } ?>
                    <a href="tel:<?php echo $companyInfo['phone']?>">
                        <i class="fa-solid fa-square-phone"></i>
                    </a>
                </div>
            </div>
            <form method="post" class="subscriptionForm">
                <input type="email" name="email" placeholder="Ваша почта">
                <button type="submit">
                    <img src="assets/images/arrow.svg" alt="arrow">
                </button>
            </form>
        </div>
        <div class="f-heading">
            ДАВАЙТЕ ПОГОВОРИМ
        </div>
        <div class="row align-items-center justify-content-sb">
            <p class="text-center">
                Защита прав 2025, tetushi-furniture.ru
            </p>
            <ul>
                <?php foreach ($menuItems as $item): ?>
                    <li>
                        <a
                            href="<?php echo $item['link']; ?>"
                            <?php if($clean_request == $item['link']) echo 'class="active"'?>>
                            <?php echo $item['title']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</footer>
<script src="assets/js/main.js"></script>
<script src="assets/js/subscribe.js"></script>
</root>
</body>
</html>