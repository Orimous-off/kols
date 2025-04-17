<?php
include 'includes/db.php';
include 'includes/header.php';
global $pdo;
?>
<div class="main-content about">
    <section class="hero">
        <?php
        $stmt = $pdo->query("SELECT * FROM main_hero");
        $aboutHeroContent = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="container h-max gap-20">
            <div class="col">
                <h1>
                    <?= $aboutHeroContent['title'];?>
                </h1>
                <p>
                    <?= $aboutHeroContent['subtitle'];?>
                </p>
            </div>
            <a href="#about-us" class="btn black">
                <span>
                    Читать больше
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
    <section id="about-us">
        <?php
        $stmt = $pdo->query("SELECT * FROM about_content");
        $aboutContent = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="container">
            <div class="mini-container col gap-20">
                <h2><?= $aboutContent['heading'];?></h2>
                <h1><?=$aboutContent['title'];?></h1>
                <div class="row gap-30">
                    <p>
                        <?= $aboutContent['paragraph_first'];?>
                    </p>
                    <p>
                        <?= $aboutContent['paragraph_second'];?>
                    </p>
                </div>
            </div>
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
                        <div class="row gap-30 align-items-center privacy-policy">
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
        <script src="/assets/js/privacyPolicyParagraph.js"></script>
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
        .about .hero .container {
            background: url("assets/<?= $aboutHeroContent['image_url'];?>") no-repeat bottom center;
        }
    </style>
</div>
<?php
include 'includes/footer.php';
?>