<?php global $pdo;

$currentSection = $_GET['sections'] ?? 'advantages';
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<?php switch($currentSection) {
    case 'advantages': { ?>
        <div class="tab-content" id="advantages">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <?php
            ini_set('display_errors', 1);
            ini_set('error_reporting', E_ALL);
            ini_set('log_errors', 1);
            ini_set('error_log', dirname(__FILE__) . '/debug.log');

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Логируем все входящие данные
                error_log("POST Data: " . print_r($_POST, true));

                try {
                    if (!isset($pdo)) {
                        throw new Exception('Database connection not established');
                    }

                    $action = $_POST['action'] ?? '';

                    if ($action === 'save_card') {
                        $response = array();

                        // Логируем полученные данные
                        error_log("Received data - ID: {$_POST['id']}, Title: {$_POST['title']}, Subtitle: {$_POST['subtitle']}");

                        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                        $title = $_POST['title'] ?? '';
                        $subtitle = $_POST['subtitle'] ?? '';

                        if ($id <= 0) {
                            throw new Exception('Invalid ID provided');
                        }

                        // Логируем SQL запрос
                        $sql = "UPDATE advantages SET title = ?, subtitle = ? WHERE id = ?";
                        error_log("Executing SQL: $sql with params: " . print_r([$title, $subtitle, $id], true));

                        $stmt = $pdo->prepare($sql);
                        $result = $stmt->execute([$title, $subtitle, $id]);

                        if ($result) {
                            $response = [
                                'success' => true,
                                'message' => 'Данные успешно сохранены',
                                'data' => [
                                    'id' => $id,
                                    'title' => $title,
                                    'subtitle' => $subtitle
                                ]
                            ];
                        } else {
                            $response = [
                                'success' => false,
                                'message' => 'Ошибка при сохранении',
                                'error' => $stmt->errorInfo()
                            ];
                        }

                        // Логируем ответ
                        error_log("Sending response: " . print_r($response, true));

                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;
                    }
                } catch (Exception $e) {
                    error_log("Error occurred: " . $e->getMessage());
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }
            }

            $advantages = $pdo->query("SELECT * FROM advantages ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <h2 class="text-center">Преимущества</h2>
            <div class="edit-grid">
                <?php foreach ($advantages as $advantage): ?>
                    <form class="edit-card" data-id="<?= htmlspecialchars($advantage['id']) ?>">
                        <div class="icon-section">
                            <img src="../assets<?= htmlspecialchars($advantage['icon_url']) ?>" alt="Преимущество">
                            <div class="image-upload">
                                <label class="upload-btn">
                                    <input type="file" name="icon" class="image-input" accept="image/jpeg,image/png,image/gif" style="display:none;">
                                    Изменить
                                </label>
                            </div>
                            <div class="error-message"></div>
                        </div>
                        <div class="title-section">
                            <input
                                    type="text"
                                    name="title"
                                    class="title-input"
                                    value="<?= htmlspecialchars($advantage['title']) ?>"
                                    data-field="title"
                                    maxlength="255"
                                    placeholder="Заголовок">
                            <div class="error-message"></div>
                        </div>
                        <div class="subtitle-section">
                            <input
                                    type="text"
                                    name="subtitle"
                                    class="subtitle-input"
                                    value="<?= htmlspecialchars($advantage['subtitle']) ?>"
                                    data-field="subtitle"
                                    maxlength="255"
                                    placeholder="Подзаголовок">
                            <div class="error-message"></div>
                        </div>
                        <div class="status-message"></div>
                        <button type="submit" class="save-btn">Сохранить</button>
                    </form>
                <?php endforeach; ?>

                <script>
                    $(document).ready(function() {
                        $('.edit-card').on('submit', function(event) {
                            event.preventDefault();
                            const form = $(this);
                            const statusMessage = form.find('.status-message');
                            const submitButton = form.find('.save-btn');

                            // Очищаем предыдущие сообщения
                            statusMessage.empty();

                            const formData = {
                                action: 'save_card',
                                id: form.data('id'),
                                title: form.find('[name="title"]').val(),
                                subtitle: form.find('[name="subtitle"]').val()
                            };

                            // Выводим отладочную информацию в консоль
                            console.log('Sending data:', formData);

                            submitButton.prop('disabled', true).text('Сохранение...');

                            $.ajax({
                                url: window.location.href,
                                method: 'POST',
                                data: formData,
                                dataType: 'json'
                            })
                                .done(function(response) {
                                    console.log('Server response:', response);

                                    if (response && response.success) {
                                        statusMessage.html('<div class="alert-success">Сохранено успешно!</div>');
                                    } else {
                                        statusMessage.html('<div class="alert-error">Ошибка: ' +
                                            (response ? response.message : 'Неизвестная ошибка') + '</div>');
                                    }
                                })
                                .fail(function(jqXHR, textStatus, errorThrown) {
                                    console.error('Ajax error:', {
                                        status: textStatus,
                                        error: errorThrown,
                                        response: jqXHR.responseText
                                    });

                                    statusMessage.html('<div class="alert-error">Ошибка сохранения: ' + textStatus + '</div>');
                                })
                                .always(function() {
                                    submitButton.prop('disabled', false).text('Сохранить');
                                });
                        });
                    });
                </script>

                <style>
                    .save-btn {
                        margin-top: 10px;
                        padding: 8px 16px;
                        background-color: #4CAF50;
                        color: white;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                    }

                    .save-btn:hover {
                        background-color: #45a049;
                    }

                    .save-btn:disabled {
                        background-color: #cccccc;
                        cursor: not-allowed;
                    }

                    .edit-card {
                        padding: 15px;
                        margin-bottom: 20px;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                    }

                    .status-message {
                        margin-top: 10px;
                    }

                    .alert-success {
                        padding: 10px;
                        background-color: #dff0d8;
                        border: 1px solid #d6e9c6;
                        color: #3c763d;
                        border-radius: 4px;
                    }

                    .alert-error {
                        padding: 10px;
                        background-color: #f2dede;
                        border: 1px solid #ebccd1;
                        color: #a94442;
                        border-radius: 4px;
                    }
                </style>
            </div>
        </div>
        <?php exit();
    }
    case 'main-hero': {?>
        <div class="tab-content" id="main-hero">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';

                switch ($action) {
                    case 'update_image':
                        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                            die(json_encode(['success' => false, 'message' => 'Ошибка при загрузке файла.']));
                        }

                        $uploadDir = '../assets/images/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $fileInfo = pathinfo($_FILES['image']['name']);
                        $extension = strtolower($fileInfo['extension']);

                        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
                        if (!in_array($extension, $allowedTypes)) {
                            die(json_encode(['success' => false, 'message' => 'Недопустимый тип файла.']));
                        }

                        $newFileName = uniqid() . '.' . $extension;
                        $uploadPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            $relativePath = '/images/' . $newFileName;
                            $stmt = $pdo->prepare("UPDATE main_hero SET image_url = ? WHERE id = 1");
                            $stmt->execute([$relativePath]);
                            echo json_encode(['success' => true, 'newImageUrl' => $relativePath]);
                        }
                        exit;

                    case 'update_field':
                        $id = $_POST['id'];
                        $field = $_POST['field'];
                        $value = $_POST['value'];

                        if (!in_array($field, ['title', 'subtitle'])) {
                            die(json_encode(['success' => false, 'message' => 'Недопустимое поле.']));
                        }

                        $stmt = $pdo->prepare("UPDATE main_hero SET $field = ? WHERE id = 1");
                        $stmt->execute([$value]);
                        echo json_encode(['success' => true]);
                        exit;
                }
            }

            $mainHero = $pdo->query("SELECT * FROM main_hero")->fetch(PDO::FETCH_ASSOC);
            ?>
            <h2 class="text-center">Главный экран</h2>
            <div class="edit-grid">
                <div class="edit-card" data-id="<?= $mainHero['id'] ?>">
                    <div class="image-section">
                        <img src="../assets<?= $mainHero['image_url'] ?>" alt="Главный экран" id="mainImage">
                        <div class="image-upload">
                            <label class="upload-btn">
                                <input type="file" id="imageUpload" accept="image/*" style="display: none;">
                                Изменить
                            </label>
                        </div>
                        <div class="error-message"></div>
                    </div>
                    <div class="title-section">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($mainHero['title']); ?>" onchange="updateField(<?= $mainHero['id']; ?>, 'title', this.value)" placeholder="Заголовок">
                    </div>
                    <div class="subtitle-section">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($mainHero['subtitle']); ?>" onchange="updateField(<?= $mainHero['id']; ?>, 'subtitle', this.value)" placeholder="Подзаголовок">
                    </div>
                </div>
            </div>
        </div>
        <?php exit();
    }
    case 'about-content': {?>
        <div class="tab-content" id="about-content">
            <h2 class="text-center">О нас</h2>
            <!-- Добавьте содержимое для раздела "О нас" -->
        </div>
        <?php
    }
        break;
    case 'about-hero': {?>
        <div class="tab-content" id="about-hero">
            <h2 class="text-center">Главный экран: О нас</h2>
            <!-- Добавьте содержимое для раздела "Главный экран: О нас" -->
        </div>
        <?php
    }
        break;
    case 'categories': {?>
        <div class="tab-content" id="categories">
            <h2 class="text-center">Категории</h2>
            <!-- Добавьте содержимое для раздела "Категории" -->
        </div>
        <?php
    }
        break;
    case 'company': {?>
        <div class="tab-content" id="company">
            <h2 class="text-center">Компания</h2>
            <!-- Добавьте содержимое для раздела "Компания" -->
        </div>
        <?php
    }
        break;
    case 'feedback': {?>
        <div class="tab-content" id="feedback">
            <h2 class="text-center">Обратная связь</h2>
            <!-- Добавьте содержимое для раздела "Обратная связь" -->
        </div>
        <?php
    }
        break;
    case 'form_content': {?>
        <div class="tab-content" id="form-content">
            <h2 class="text-center">Форма обратной связи</h2>
            <!-- Добавьте содержимое для раздела "Форма обратной связи" -->
        </div>
        <?php
    }
        break;
    case 'mailing': {?>
        <div class="tab-content" id="mailing">
            <h2 class="text-center">Рассылка почты</h2>
            <!-- Добавьте содержимое для раздела "Рассылка почты" -->
        </div>
        <?php
    }
        break;
    case 'manufacturers': {?>
        <div class="tab-content" id="manufacturers">
            <h2 class="text-center">Производители</h2>
            <!-- Добавьте содержимое для раздела "Производители" -->
        </div>
        <?php
    }
        break;
    case 'orders': {?>
        <div class="tab-content" id="orders">
            <h2 class="text-center">Заказы</h2>
            <!-- Добавьте содержимое для раздела "Заказы" -->
        </div>
        <?php
    }
        break;
    case 'products': {?>
        <div class="tab-content" id="products">
            <h2 class="text-center">Товары</h2>
            <!-- Добавьте содержимое для раздела "Товары" -->
        </div>
        <?php
    }
        break;
    case 'product_colors': {?>
        <div class="tab-content" id="product-colors">
            <h2 class="text-center">Цвета продуктов</h2>
            <!-- Добавьте содержимое для раздела "Цвета продуктов" -->
        </div>
        <?php exit();
    }
    case 'product_images': {?>
        <div class="tab-content" id="product-images">
            <h2 class="text-center">Изображения продуктов</h2>
            <!-- Добавьте содержимое для раздела "Изображения продуктов" -->
        </div>
        <?php
    }
        break;
    case 'reviews': {?>
        <div class="tab-content" id="reviews">
            <h2 class="text-center">Отзывы</h2>
            <!-- Добавьте содержимое для раздела "Отзывы" -->
        </div>
        <?php exit();
    }
    case 'social_networks': {?>
        <div class="tab-content" id="social-networks">
            <h2 class="text-center">Социальные сети</h2>
            <!-- Добавьте содержимое для раздела "Социальные сети" -->
        </div>
        <?php
    }
        break;
    case 'users': {?>
        <div class="tab-content" id="users">
            <h2 class="text-center">Пользователи</h2>
            <!-- Добавьте содержимое для раздела "Пользователи" -->
        </div>
        <?php
    }
        break;
    default: {?>
        <div class="error-message">
            <h2 class="text-center">Раздел не найден</h2>
            <p>Запрошенный раздел не существует.</p>
        </div>
        <?php
    } break;
}?>