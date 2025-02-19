<?php
// Prevent any output before header() call
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => ''];

try {
    // Include database connection
    require_once "db.php";
    global $pdo;

    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Input validation
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);

    // Check if required fields are not empty
    if (empty($name) || empty($phone) || empty($comment)) {
        throw new Exception('Все поля обязательны для заполнения');
    }

    // Prepare and execute the statement
    $insert_stmt = $pdo->prepare("INSERT INTO feedback (name, phone, comment) VALUES (:name, :phone, :comment)");

    if ($insert_stmt->execute([
        'name' => $name,
        'phone' => $phone,
        'comment' => $comment
    ])) {
        $response['status'] = 'success';
        $response['message'] = 'Данные успешно сохранены';
    } else {
        throw new Exception('Ошибка при сохранении данных');
    }

} catch (PDOException $e) {
    $response['message'] = 'Ошибка базы данных';
    error_log('Database Error: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Error: ' . $e->getMessage());
} finally {
    // Clean output buffer
    ob_clean();

    // Close connection
    if (isset($pdo)) {
        $pdo = null;
    }
}

// Ensure only JSON is output
echo json_encode($response);
exit;