<?php
global $pdo;
// Начинаем буферизацию вывода
ob_start();

// Включаем отображение ошибок только в лог
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    // Проверка AJAX запроса
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Очищаем буфер вывода перед отправкой JSON
        ob_clean();

        // Получение данных из БД
        $stmt = $pdo->query("SELECT * FROM main_hero LIMIT 1");
        $hero = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hero) {
            $stmt = $pdo->prepare("INSERT INTO main_hero (id, title, subtitle, image_url) VALUES (1, '', '', '')");
            $stmt->execute();
            $hero = ['id' => 1, 'title' => '', 'subtitle' => '', 'image_url' => ''];
        }

        // Загрузка изображения
        $image_url = $hero['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/';

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $fileName = uniqid() . '.' . $fileExtension;
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $image_url = '/images/' . $fileName;

               /* // Удаляем старое изображение
                if ($hero['image_url'] && file_exists('../assets' . $hero['image_url'])) {
                    unlink('../assets' . $hero['image_url']);
                }*/
            }
        }

        // Обновление данных
        $stmt = $pdo->prepare("
            UPDATE main_hero 
            SET title = :title, 
                subtitle = :subtitle, 
                image_url = :image_url 
            WHERE id = :id
        ");

        $updateResult = $stmt->execute([
            'title' => $_POST['title'],
            'subtitle' => $_POST['subtitle'],
            'image_url' => $image_url,
            'id' => $hero['id']
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $updateResult,
            'message' => $updateResult ? 'Данные успешно обновлены' : 'Ошибка при обновлении данных',
            'reload' => $updateResult
        ]);
        exit;
    }

    // Получение данных для отображения формы
    $stmt = $pdo->query("SELECT * FROM main_hero LIMIT 1");
    $hero = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hero) {
        $stmt = $pdo->prepare("INSERT INTO main_hero (id, title, subtitle, image_url) VALUES (1, '', '', '')");
        $stmt->execute();
        $hero = ['id' => 1, 'title' => '', 'subtitle' => '', 'image_url' => ''];
    }

} catch (Exception $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    } else {
        $error = $e->getMessage();
    }
}
?>

<div class="bg-white rounded-lg shadow-sm p-6">
    <h2 class="text-2xl font-semibold mb-6">Редактирование главного экрана</h2>

    <?php if (isset($error)): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" data-ajax>
        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Заголовок</label>
            <input type="text"
                   id="title"
                   name="title"
                   value="<?php echo htmlspecialchars($hero['title'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Подзаголовок</label>
            <input type="text"
                   id="subtitle"
                   name="subtitle"
                   value="<?php echo htmlspecialchars($hero['subtitle'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-6">
            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Изображение</label>
            <?php if (!empty($hero['image_url'])): ?>
                <div class="mb-2">
                    <img src="../assets<?php echo htmlspecialchars($hero['image_url']); ?>"
                         alt="Текущее изображение"
                         class="max-w-md h-auto rounded">
                </div>
            <?php endif; ?>
            <input type="file"
                   id="image"
                   name="image"
                   accept=".jpg,.jpeg,.png,.webp"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="mt-1 text-sm text-gray-500">Рекомендуемый размер: 1920x1080px. Допустимые форматы: JPG, PNG, WebP</p>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Сохранить изменения
            </button>
        </div>
    </form>
</div>

<script>
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Если ответ не в формате JSON, выводим его как текст
                    const text = await response.text();
                    console.error('Received non-JSON response:', text);
                    alert('Получен некорректный ответ от сервера');
                    return;
                }

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    if (result.reload) {
                        location.reload();
                    }
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Request error:', error);
                alert('Произошла ошибка при выполнении запроса');
            }
        });
    });
</script>