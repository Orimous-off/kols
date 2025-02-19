<?php
header('Content-Type: application/json');

require_once "db.php";
global $pdo;

$response = ['status' => 'error'];

try {
    // Email validation
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        throw new Exception('Invalid email');
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM mailing WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        $response['status'] = 'exists';
    } else {
        // Insert new email
        $insert_stmt = $pdo->prepare("INSERT INTO mailing (email) VALUES (:email)");

        if ($insert_stmt->execute(['email' => $email])) {
            $response['status'] = 'success';
        }
    }

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    // Close connection
    $pdo = null;
}

echo json_encode($response);
