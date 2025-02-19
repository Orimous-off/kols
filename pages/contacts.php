<?php
include 'includes/db.php';
include 'includes/header.php';
global $pdo;
?>
<div class="main-content">
    <section class="contacts" style="margin-top: 50px;">
        <div class="container col gap-20">
            <?php
            $stmt = $pdo->query("SELECT * FROM company_info LIMIT 1");
            $companyInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <div class="row w-max justify-content-sb">
                <div class="col">
                    <h3>Телефон</h3>
                    <a href="tel:<?php echo $companyInfo['phone']; ?>" class="icon-link">
                        <i class="fa-solid fa-phone"></i>
                        <?php echo $companyInfo['phone']; ?>
                    </a>
                </div>
                <div class="col">
                    <h3>Почта</h3>
                        <a href="mailto:<?= $companyInfo['email']; ?>" class="icon-link">
                            <i class="fa-solid fa-envelope"></i>
                            <?= $companyInfo['email']; ?>
                        </a>
                </div>
                <div class="col">
                    <h3>Адрес</h3>
                        <div class="icon-link">
                            <i class="fa-solid fa-location-dot"></i>
                            <?= $companyInfo['address']; ?>
                        </div>
                </div>
            </div>
            <iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3Ab74a79b03dd92e8433fb363ba9c076e5e7100043758550b407a7d7fd3a8b8856&amp;source=constructor" width="1200"
                    height="350"
                    frameborder="0"
                    style="border-radius: 10px"
            >
            </iframe>
        </div>
    </section>
    <section class="form-section">
        <?php
        $stmt = $pdo->query("SELECT * FROM `form_content` LIMIT 1");
        $formContent = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="container">
            <div class="card row justify-content-sb">
                <div class="col gap-20 form-card">
                    <h2><?php echo $formContent['heading']?></h2>
                    <p>
                        <?php echo $formContent['subtitle']?>
                    </p>
                    <form id="feedback" method="post" class="col gap-20 flex-wrap">
                        <div class="row gap-20 align-items-stretch">
                            <div class="col justify-content-sb gap-20">
                                <input type="text" name="name" required placeholder="Ваше имя">
                                <input type="tel" name="phone" min="11" required placeholder="Ваш телефон">
                            </div>
                            <textarea name="comment" minlength="10" required placeholder="Ваш вопрос"></textarea>
                        </div>
                        <div class="row gap-30 align-items-center">
                            <p>
                                <?php echo $formContent['privacy_policy']?>
                            </p>
                            <button class="btn">
                                Отправить
                            </button>
                        </div>
                    </form>
                </div>
                <div>
                    <img src="assets<?= $formContent['image_url']?>" alt="Форма">
                </div>
            </div>
        </div>
        <script>
            // Маска для телефона
            document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
                let x = e.target.value.replace(/\D/g, '')
                    .match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
                // Start with +7
                let formattedNumber = '+7';
                // Add area code if exists
                if (x[2]) {
                    formattedNumber += ` (${x[2]})`;
                }
                // Add first part of number if exists
                if (x[3]) {
                    formattedNumber += ` ${x[3]}`;
                }
                // Add first hyphen and next two digits if they exist
                if (x[4]) {
                    formattedNumber += `-${x[4]}`;
                }
                // Add final hyphen and last two digits if they exist
                if (x[5]) {
                    formattedNumber += `-${x[5]}`;
                }
                e.target.value = formattedNumber;
            });
        </script>
        <script src="assets/js/feedback.js"></script>
    </section>
</div>
<?php
include 'includes/footer.php';
?>