<?php
session_start();
require_once 'includes/db.php';
global $pdo;

include 'includes/header.php';
?>
    <div class="container payment-page">
        <h1 class="page-title">Способы оплаты</h1>

        <div class="payment-methods">
            <div class="payment-method">
                <div class="method-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <h2>Оплата при получении</h2>
                <div class="method-details">
                    <h3>Как это работает:</h3>
                    <ul>
                        <li>Оформите заказ на сайте</li>
                        <li>Дождитесь звонка менеджера для подтверждения</li>
                        <li>Получите заказ и оплатите его наличными или картой</li>
                    </ul>
                    <div class="method-benefits">
                        <p><strong>Преимущества:</strong></p>
                        <ul>
                            <li>Оплата после проверки товара</li>
                            <li>Возможность осмотреть товар перед покупкой</li>
                            <li>Оплата наличными или картой</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="payment-method">
                <div class="method-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <h2>Оплата при самовывозе</h2>
                <div class="method-details">
                    <h3>Как это работает:</h3>
                    <ul>
                        <li>Оформите заказ и выберите пункт самовывоза</li>
                        <li>Дождитесь уведомления о готовности заказа</li>
                        <li>Приезжайте в удобное время и оплачивайте при получении</li>
                    </ul>
                    <div class="method-benefits">
                        <p><strong>Преимущества:</strong></p>
                        <ul>
                            <li>Экономия на доставке</li>
                            <li>Возможность забрать заказ в удобное время</li>
                            <li>Проверка товара на месте</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="payment-info">
            <h2>Важная информация</h2>
            <div class="info-cards">
                <div class="info-card">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12" y2="8"/>
                        </svg>
                    </div>
                    <h3>Подтверждение заказа</h3>
                    <p>После оформления заказа наш менеджер свяжется с вами для подтверждения деталей в течение рабочего дня.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <h3>Безопасность</h3>
                    <p>Мы гарантируем сохранность ваших данных и безопасность оплаты при любом выбранном способе.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <h3>Гарантия</h3>
                    <p>На все товары предоставляется гарантия. При возникновении вопросов обращайтесь в службу поддержки.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .payment-page {
            padding: 2rem 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #333;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .payment-method {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .method-icon {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: #4CAF50;
        }

        .method-icon svg {
            width: 48px;
            height: 48px;
        }

        .payment-method h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .method-details {
            color: #666;
        }

        .method-details h3 {
            color: #333;
            margin-bottom: 1rem;
        }

        .method-details ul {
            list-style: none;
            padding: 0;
        }

        .method-details li {
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .method-details li:before {
            content: "•";
            color: #4CAF50;
            position: absolute;
            left: 0;
        }

        .method-benefits {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .payment-info {
            margin-top: 3rem;
        }

        .payment-info h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card-icon {
            color: #4CAF50;
            margin-bottom: 1rem;
        }

        .info-card h3 {
            margin-bottom: 0.5rem;
            color: #333;
        }

        .info-card p {
            color: #666;
            margin: 0;
        }

        @media (max-width: 768px) {
            .payment-methods {
                grid-template-columns: 1fr;
            }

            .info-cards {
                grid-template-columns: 1fr;
            }

            .payment-method {
                padding: 1.5rem;
            }
        }
    </style>

<?php
include 'includes/footer.php';
?>