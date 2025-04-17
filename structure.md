dim-kolsanov
│
├── /admin
│   ├── /api
│   │   ├── /products
│   │   │   ├── add_product.php        # Добавление продукта
│   │   │   ├── delete_product.php     # Удаление продукта
│   │   │   ├── get_product.php        # Получение данных о продукте
│   │   │   ├── toggle_visibility.php  # Переключение видимости продукта
│   │   │   └── update_product.php     # Обновление продукта
│   │   ├── about-content.php          # Управление контентом страницы "О нас"
│   │   ├── about-hero.php             # Управление героем страницы "О нас"
│   │   ├── category.php               # Управление категориями
│   │   ├── company.php                # Управление данными компании
│   │   ├── edit-order.php             # Редактирование заказов
│   │   ├── main-hero.php              # Управление главным героем
│   │   ├── manufacturers.php          # Управление производителями
│   │   ├── navigation.php             # Управление навигацией
│   │   ├── orders.php                 # Управление заказами
│   │   ├── products.php               # Управление продуктами
│   │   └── social-networks.php        # Управление соцсетями
│   ├── /content
│   │   ├── about-content.php          # Управление контентом страницы "О нас"
│   │   ├── about-hero.php             # Управление героем страницы "О нас"
│   │   ├── category.php               # Управление категориями
│   │   ├── company.php                # Управление данными компании
│   │   ├── main-hero.php              # Управление главным героем
│   │   ├── manufacturers.php          # Управление производителями
│   │   ├── navigation.php             # Управление навигацией
│   │   ├── orders.php                 # Управление заказами
│   │   ├── products.php               # Управление продуктами
│   │   └── social-networks.php        # Управление соцсетями
│   ├── dashboard.php                  # Главный дашборд админ-панели
│   └── index.php                      # Главная страница админ-панели
│
├── /assets
│   ├── /css
│   │   ├── admin.css                  # Стили админ-панели
│   │   └── style.css                  # Основные стили
│   ├── /images                        # Изображения для сайта
│   ├── /js
│   │   ├── admin.js                   # Скрипты админ-панели
│   │   ├── feedback.js                # Скрипты для обратной связи
│   │   ├── main.js                    # Скрипты фронтенда
│   │   ├── privacyPolicyParagraph.js  # Скрипты для политики конфиденциальности
│   │   └── subscribe.js               # Скрипты для подписки
│
├── /includes
│   ├── /autoUpdate
│   │   ├── cartHandler.php            # Обработка корзины
│   │   ├── getUserPhone.php           # Получение телефона пользователя
│   │   ├── orderHandler.php           # Обработка заказов
│   │   └── update_profile.php         # Обновление профиля
│   ├── auth.php                       # Скрипт проверки авторизации
│   ├── db.php                         # Подключение к базе данных
│   ├── feedback.php                   # Обработка обратной связи
│   ├── footer.php                     # Общий футер сайта
│   ├── header.php                     # Общая шапка сайта
│   └── subscribe.php                  # Обработка подписки
│
├── /pages                             # Динамически подключаемые страницы
│   ├── about.php                      # Страница "О нас"
│   ├── cart.php                       # Страница корзины
│   ├── catalog.php                    # Страница "Каталог"
│   ├── contacts.php                   # Страница "Контакты"
│   ├── delivery.php                   # Страница доставки
│   ├── home.php                       # Главная страница
│   ├── login.php                      # Страница входа
│   ├── not-found.php                  # Страница 404
│   ├── payment.php                    # Страница оплаты
│   ├── product.php                    # Страница продукта
│   └── profile.php                    # Страница профиля
│
├── .htaccess                          # Настройка ЧПУ (человекопонятных URL)
├── config.php                         # Основные настройки проекта
├── index.php                          # Точка входа для маршрутизации
└── structure.md                       # Описание структуры проекта