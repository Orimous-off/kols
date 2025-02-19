<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            width: 100%;
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            background: #f8f9fa;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #666;
            border: none;
        }

        .tab.active {
            background: #4A90E2;
            color: white;
        }

        .form-group {
            margin-bottom: 14px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5ee;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: #333;
        }

        input:focus {
            outline: none;
            border-color: #4A90E2;
            box-shadow: 0 0 0 3px rgba(74,144,226,0.1);
        }

        input::placeholder {
            color: #aab;
        }

        button {
            background-color: #4A90E2;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background-color: #357ABD;
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        .error {
            color: #e74c3c;
            margin-top: 8px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .error:before {
            content: "⚠";
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #loginForm, #registerForm {
            display: none;
            animation: fadeIn 0.3s ease forwards;
        }

        /* Специальные стили для поля телефона */
        input[type="tel"] {
            letter-spacing: 0.5px;
            font-family: monospace;
            font-size: 1.1rem;
        }

        .input-highlight {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            border-radius: 8px;
            box-shadow: 0 0 0 2px #4A90E2;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="tabs">
        <div class="tab active" onclick="showTab('login')">Вход</div>
        <div class="tab" onclick="showTab('register')">Регистрация</div>
    </div>
    <form id="loginForm">
        <div class="form-group">
            <label for="loginCredential">Email или телефон</label>
            <input type="text" id="loginCredential" name="credential" required>
            <div class="input-highlight"></div>
        </div>
        <div class="form-group">
            <label for="loginPassword">Пароль</label>
            <input type="password" id="loginPassword" name="password" required>
            <div class="input-highlight"></div>
        </div>
        <button type="submit">Войти</button>
        <div class="error" id="loginError"></div>
    </form>
    <form id="registerForm" style="display: none;">
        <div class="form-group">
            <label for="registerUsername">Имя пользователя</label>
            <input type="text" id="registerUsername" name="username" required>
            <div class="input-highlight"></div>
        </div>
        <div class="form-group">
            <label for="registerEmail">Email</label>
            <input type="email" id="registerEmail" name="email" required>
            <div class="input-highlight"></div>
        </div>
        <div class="form-group">
            <label for="registerPhone">Телефон</label>
            <input type="tel" id="registerPhone" name="phone_number" placeholder="+7 (___) ___-__-__" required>
            <div class="input-highlight"></div>
        </div>
        <div class="form-group">
            <label for="registerPassword">Пароль</label>
            <input type="password" id="registerPassword" name="password" required>
            <div class="input-highlight"></div>
        </div>
        <div class="form-group">
            <label for="confirmPassword">Подтвердите пароль</label>
            <input type="password" id="confirmPassword" name="confirm_password" required>
            <div class="input-highlight"></div>
        </div>
        <button type="submit">Зарегистрироваться</button>
        <div class="error" id="registerError"></div>
    </form>
</div>
<script>
    function showTab(tab) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelector(`.tab:${tab === 'login' ? 'first-child' : 'last-child'}`).classList.add('active');
        document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
        document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
    }

    function phoneMask(input) {
        let value = input.value.replace(/\D+/g, '');
        let numberLength = 11;

        let result = '+7 (';

        if (value.length > 1) {
            result += value.substring(1, 4);
        }
        if (value.length >= 4) {
            result += ') ' + value.substring(4, 7);
        }
        if (value.length >= 7) {
            result += '-' + value.substring(7, 9);
        }
        if (value.length >= 9) {
            result += '-' + value.substring(9, 11);
        }

        input.value = result;
    }

    const phoneInput = document.getElementById('registerPhone');
    phoneInput.addEventListener('input', function(e) {
        phoneMask(e.target);
    });

    phoneInput.addEventListener('focus', function(e) {
        if (!e.target.value) {
            e.target.value = '+7 (';
        }
    });

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'login');
        try {
            const response = await fetch('includes/auth.php', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log('Response data:', data);  // Добавим отладку

            if (data.success) {
                if (data.status === 'admin') {
                    window.location.href = '/admin';
                } else {
                    window.location.href = '/profile';
                }
            } else {
                document.getElementById('loginError').textContent = data.message || 'Неверные данные';
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('loginError').textContent = 'Произошла ошибка при входе: ' + error.message;
        }
    });

    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const password = form.querySelector('#registerPassword').value;
        const confirmPassword = form.querySelector('#confirmPassword').value;

        if (password !== confirmPassword) {
            document.getElementById('registerError').textContent = 'Пароли не совпадают';
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'register');
        try {
            const response = await fetch('includes/auth.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                window.location.href = '/profile';
            } else if(data.admin) {
                window.location.href = '/admin';
            }
            else {
                document.getElementById('registerError').textContent = data.message || 'Ошибка при регистрации';
            }
        } catch (error) {
            document.getElementById('registerError').textContent = 'Произошла ошибка';
        }
    });

    showTab('login');
</script>
</body>
</html>