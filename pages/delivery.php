<?php
ob_start();
session_start();
require_once 'includes/db.php';
global $pdo;

include 'includes/header.php';
?>
    <div class="container delivery-page">
        <h1 class="page-title">Доставка</h1>

        <div class="delivery-methods">
            <div class="delivery-method">
                <div class="method-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3h18v18H3z"/>
                        <path d="M21 3v18"/>
                        <path d="M3 9h18"/>
                        <path d="M9 21V9"/>
                    </svg>
                </div>
                <h2>Доставка по городу</h2>
                <div class="method-details">
                    <h3>Условия доставки:</h3>
                    <ul>
                        <li>Бесплатная доставка по всему городу Тетюши</li>
                        <li>Доставка осуществляется в течение 1-2 рабочих дней</li>
                        <li>Подъем на этаж включён в стоимость</li>
                    </ul>
                    <div class="method-benefits">
                        <p><strong>Преимущества:</strong></p>
                        <ul>
                            <li>Бесплатная доставка до квартиры</li>
                            <li>Предварительное согласование времени</li>
                            <li>Профессиональные грузчики</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="delivery-method">
                <div class="method-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="15" height="13"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                </div>
                <h2>Доставка по району</h2>
                <div class="method-details">
                    <h3>Условия доставки:</h3>
                    <ul>
                        <li>Стоимость: 30 руб/км от границы города</li>
                        <li>Доставка в течение 2-3 рабочих дней</li>
                        <li>Максимальное расстояние доставки - 100 км</li>
                    </ul>
                    <div class="method-benefits">
                        <p><strong>Преимущества:</strong></p>
                        <ul>
                            <li>Доставка до дома в любой населённый пункт района</li>
                            <li>Страховка груза на время перевозки</li>
                            <li>Помощь в разгрузке</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="delivery-info">
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
                    <h3>График доставки</h3>
                    <p>Доставка осуществляется ежедневно с 9:00 до 20:00. Точное время согласовывается с менеджером.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <h3>Уведомления</h3>
                    <p>За час до доставки вам позвонит водитель и сообщит точное время прибытия.</p>
                </div>

                <div class="info-card">
                    <div class="card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <h3>Документы</h3>
                    <p>При получении товара вам будут предоставлены все необходимые документы и гарантийный талон.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .delivery-page {
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

        .delivery-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .delivery-method {
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

        .delivery-method h2 {
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

        .delivery-info {
            margin-top: 3rem;
        }

        .delivery-info h2 {
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
            .delivery-methods {
                grid-template-columns: 1fr;
            }

            .info-cards {
                grid-template-columns: 1fr;
            }

            .delivery-method {
                padding: 1.5rem;
            }
        }
    </style>

<?php
include 'includes/footer.php';
ob_end_flush();
?>