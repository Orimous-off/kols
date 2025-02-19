<?php
global $pdo;
session_start();
require_once 'db.php';

class Auth {
    private $pdo;
    private const SESSION_DURATION = '3 months';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($username, $email, $phone_number, $password)
    {
        try {
            // Проверяем существование email
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Email уже используется");
            }

            // Проверяем существование телефона
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE phone_number = ?");
            $stmt->execute([$phone_number]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Номер телефона уже используется");
            }

            $this->pdo->beginTransaction();

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, phone_number, password_hash) VALUES (?, ?, ?, ?)");
            $success = $stmt->execute([$username, $email, $phone_number, $password_hash]);

            if ($success) {
                $userId = $this->pdo->lastInsertId();
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+' . self::SESSION_DURATION));

                $stmt = $this->pdo->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $token, $expires]);

                // Сохраняем расширенные данные пользователя в сессии
                $this->setUserSession($userId, false, $username, $email);
                setcookie('remember_token', $token, strtotime('+' . self::SESSION_DURATION), '/', '', true, true);

                $this->pdo->commit();
                return true;
            }

            $this->pdo->rollBack();
            throw new Exception("Ошибка при создании пользователя");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function setUserSession($userId, $isAdmin, $username, $email) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['is_admin'] = $isAdmin;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['last_activity'] = time();
    }

    public function login($credential, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? OR phone_number = ?");
        $stmt->execute([$credential, $credential]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Сохраняем расширенные данные пользователя в сессии
            $this->setUserSession(
                $user['id'],
                $user['is_admin'] == 1,
                $user['username'],
                $user['email']
            );

            // Создаем токен для remember me
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+' . self::SESSION_DURATION));

            $stmt = $this->pdo->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);
            setcookie('remember_token', $token, strtotime('+' . self::SESSION_DURATION), '/', '', true, true);

            return [
                'success' => true,
                'status' => $user['is_admin'] == 1 ? 'admin' : 'success',
                'username' => $user['username']
            ];
        }
        return false;
    }

    public function checkAuth() {
        // Проверяем активность сессии
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
            // Если прошло больше часа, очищаем сессию
            $this->logout();
            return false;
        }

        // Обновляем время последней активности
        $_SESSION['last_activity'] = time();

        if (isset($_SESSION['user_id'])) {
            return true;
        }

        if (isset($_COOKIE['remember_token'])) {
            $stmt = $this->pdo->prepare("
                SELECT users.* FROM users 
                JOIN user_sessions ON users.id = user_sessions.user_id 
                WHERE user_sessions.token = ? AND user_sessions.expires_at > NOW()
            ");
            $stmt->execute([$_COOKIE['remember_token']]);
            $user = $stmt->fetch();

            if ($user) {
                // Обновляем данные сессии
                $this->setUserSession(
                    $user['id'],
                    $user['is_admin'] == 1,
                    $user['username'],
                    $user['email']
                );
                return true;
            }
        }

        return false;
    }

    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    public function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'is_admin' => $_SESSION['is_admin']
            ];
        }
        return null;
    }

    public function logout() {
        if (isset($_COOKIE['remember_token'])) {
            $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE token = ?");
            $stmt->execute([$_COOKIE['remember_token']]);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        session_destroy();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $auth = new Auth($pdo);
    $response = ['success' => false];

    try {
        if ($_POST['action'] === 'login') {
            $loginResult = $auth->login($_POST['credential'], $_POST['password']);

            if ($loginResult !== false) {
                $response = $loginResult;
            } else {
                throw new Exception('Неверные учетные данные');
            }
        } elseif ($_POST['action'] === 'register') {
            if ($auth->register(
                $_POST['username'],
                $_POST['email'],
                $_POST['phone_number'],
                $_POST['password']
            )) {
                $response['success'] = true;
            }
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}