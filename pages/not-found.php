<?php
include 'includes/db.php';
include 'includes/header.php';
global $pdo;

?>
<div class="main-content h-max not-found">
    <div class="container col justify-content-center align-items-center" style="flex: 1;">
        <div class="not-found col gap-20 w-max justify-content-center align-items-center text-center position-rel">
            <h1>404</h1>
            <p style="font-size: 20px;">
                К сожадению данная страница не найдена
            </p>
            <a href="/" class="btn">Вернуться на главную</a>
        </div>
    </div>
</div>
<script>
    document.body.style.overflowY = 'hidden';
</script>