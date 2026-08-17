<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg">
        <img src="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg" alt="Yii Framework" height="100">
    </picture>
    <h1 align="center">Yii 2 Basic — интернет-магазин</h1>
    <br>
</p>

Проект на базе шаблона [Yii 2 Basic](https://www.yiiframework.com/), PHP >=8.2, Yii2 ~2.0.54.

Вместо демонстрационной in-memory авторизации из стокового шаблона здесь реализована полноценная
аутентификация на базе БД (`models/User.php`, `models/LoginForm.php`, `models/SignupForm.php`),
отдельная от неё **админ-панель со своей собственной сессией и RBAC-ролью `admin`**, а также
демо-каталог интернет-магазина (клиенты, категории, товары, заказы).

## Содержание

- [Быстрый старт (Docker)](#быстрый-старт-docker)
- [Makefile: список команд](#makefile-список-команд)
- [Структура проекта](#структура-проекта)
- [Конфигурация](#конфигурация)
- [Аутентификация: storefront и admin](#аутентификация-storefront-и-admin)
- [Оплата](#оплата)
- [Тестирование](#тестирование)
- [Статический анализ и code style](#статический-анализ-и-code-style)
- [Установка без Docker](#установка-без-docker)

## Быстрый старт (Docker)

Стек: `nginx` + `php-fpm` (PHP 8.4 в контейнере, `composer.json` требует >=8.2) + `postgres`,
описан в `docker-compose.yml` и `.env`.

Полное развёртывание проекта одной командой:

```bash
make setup
```

Она делает всё по порядку: копирует `.env.example` → `.env`, собирает образы, ставит зависимости
composer, поднимает контейнеры, применяет миграции, засевает демо-данные и собирает
Codeception-акторов. После неё приложение доступно на **http://127.0.0.1:8080**
(порт берётся из `NGINX_PORT` в `.env`).

Готовые тестовые аккаунты после `make setup` (создаются командой `seed`):

| Логин | Пароль     | Доступ                          |
|-------|------------|----------------------------------|
| admin | admin      | storefront + `/admin` (роль admin) |
| demo  | demo       | storefront                       |
| alice, bob, carol, dave, erin | password | storefront (клиенты магазина) |

Если нужно повторить развёртывание с нуля (включая сброс БД):

```bash
make clean   # остановить стек и удалить volume с данными Postgres
make setup
```

## Makefile: список команд

```bash
make help
```

выведет актуальный список целей. Основные:

| Команда | Что делает |
|---|---|
| `make setup` | Полное развёртывание: env → build → install → up → migrate → seed → codecept-build |
| `make env` | Создать `.env` из `.env.example`, если его ещё нет |
| `make build` | Собрать docker-образы (`docker compose build`) |
| `make install` | `composer install` внутри контейнера (создаёт `vendor/`, генерирует `cookieValidationKey`) |
| `make up` / `make down` | Поднять / остановить стек |
| `make stop` / `make restart` | Остановить без удаления контейнеров / перезапустить |
| `make migrate` / `make migrate-down` | Применить / откатить последнюю миграцию |
| `make seed` | Засеять демо-данные (`php yii seed/all`) |
| `make codecept-build` | Пересобрать `Support/_generated` акторы Codeception |
| `make test` | Прогнать все тесты (Unit + Functional + Acceptance) |
| `make test-unit` / `make test-functional` / `make test-acceptance` | Прогнать конкретный набор тестов |
| `make static` | phpstan (level 5) |
| `make cs` / `make cs-fix` | phpcs / автофикс phpcbf |
| `make logs` / `make ps` / `make sh` | Логи стека / статус контейнеров / shell в контейнере php |
| `make clean` | `docker compose down -v` — полный сброс, включая данные Postgres |

## Структура проекта

      assets/             ассеты (JS/CSS)
      commands/            консольные команды (в т.ч. seed/all)
      config/              конфигурация приложения
      controllers/         контроллеры storefront
      docker/               Dockerfile (multi-stage) и конфиг nginx
      mail/                вью для писем
      migrations/          миграции БД
      models/              модели (в т.ч. User, LoginForm, SignupForm)
      modules/admin/       модуль админ-панели (отдельная авторизация)
      services/            сервисный слой (например AdminLoginService)
      tests/               тесты Codeception (Unit, Functional, Acceptance)
      vendor/              зависимости composer
      views/               вью storefront и админки
      web/                 точка входа и веб-ресурсы

## Конфигурация

`config/db.php` и `config/test_db.php` читают подключение из переменных `.env`:
`POSTGRES_HOST` / `POSTGRES_DB` / `POSTGRES_USER` / `POSTGRES_PASSWORD`. Порт Postgres **внутри**
DSN зашит как `5432` (внутренний порт контейнера) независимо от `POSTGRES_PORT` — эта переменная
влияет только на проброс порта на хост.

Тестовая БД — `{POSTGRES_DB}_test` (см. `config/test_db.php`), отдельная от dev-базы.

**Заметки:**
- Данные Postgres хранятся в volume `db-data`; `make clean` (`docker compose down -v`) их сбрасывает.
- Xdebug вшит в стадию `dev` образа, но по умолчанию выключен. Включить на один запуск:
  `XDEBUG_MODE=debug docker compose up -d`, либо выставить `XDEBUG_MODE=debug` в `.env`.
- `docker/php/Dockerfile` содержит 4 стадии: `base` (только PHP + расширения), `dev`
  (bind-mount воркфлоу выше, Xdebug), `build` (`composer install --no-dev`, приложение
  запекается в образ — не используется docker-compose.yml) и `prod` (только результат `build`,
  без composer и компиляторов, запускается от `www-data`). Продовый образ собирается так:
  `docker build -f docker/php/Dockerfile --target prod -t <tag> .`.

Запуск без Docker (локальный PHP/Postgres) — правьте `config/db.php` напрямую:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=yii2',
    'username' => 'yii2',
    'password' => 'secret',
    'charset' => 'utf8',
];
```

## Аутентификация: storefront и admin

- **Storefront**: `SiteController::actionLogin/actionSignup/actionLogout` — единственные точки
  входа для обычных пользователей. `LoginForm` ищет пользователя через `User::findByUsername()`
  и проверяет пароль через `security->validatePassword()`; `SignupForm::signup()` хеширует пароль
  через `security->generatePasswordHash()`.
- **Admin — полностью отдельная сессия** от storefront, хотя модель/таблица `User` та же.
  `config/web.php` регистрирует второй компонент `adminUser` (`yii\web\User`) со своими
  `idParam`/`authTimeoutParam`/`identityCookie`, чтобы не пересекаться с сессией обычного `user`.
  `modules/admin/controllers/SiteController` аутентифицирует через `Yii::$app->adminUser` с
  помощью `services/AdminLoginService`, который дополнительно проверяет наличие RBAC-роли
  `admin` (`authManager->checkAccess`) — обычный логин никогда не даёт доступ в админку, и
  наоборот, даже для аккаунта с этой ролью.
- Миграции создают только схему, включая саму RBAC-роль `admin` как *item*, но **не** назначают
  её ни одному пользователю — это уже данные. Назначение роли и создание аккаунтов `admin`/`admin`
  и `demo`/`demo` делает `php yii seed/all` (`make seed`), идемпотентно.
- Для тестов вместо seed используются фикстуры `tests/Support/Fixtures/UserFixture.php` (+
  `AdminRoleAssignmentFixture.php`) с фиксированными id `100`/`101`.

## Оплата

Заказ (`models/Order`) доходит до `STATUS_NEW`, дальше оплата — отдельная сущность `models/Payment`
(на один заказ может быть несколько попыток оплаты разными способами), обрабатываемая через
`services/Payment/`:

- **`PaymentGatewayInterface`** — контракт шлюза (`createPayment`/`handleCallback`), реализации
  резолвятся через DI-singleton `PaymentGatewayRegistry` (`config/web.php`), так что в форме на
  `/orders/<id>/pay` покупатель выбирает из всех зарегистрированных провайдеров.
- **`YooKassaGateway`** — реализация на [ЮKassa](https://yookassa.ru/developers/api). Тестовые
  креды (shopId/secretKey) берутся из личного кабинета ЮKassa (тестовый магазин) и задаются через
  `.env`: `YOOKASSA_SHOP_ID` / `YOOKASSA_SECRET_KEY`. Без них шлюз просто не будет работать — в
  форме выбора останется только `fake`.
  **Важно про вебхуки**: `handleCallback()` не доверяет телу входящего POST — оттуда берётся
  только id платежа, дальше статус переспрашивается напрямую у ЮKassa (`GET /payments/{id}` с
  теми же кредами). Источник истины — этот авторизованный запрос, а не то, что кто-то смог
  отправить на наш `/payment/yookassa/callback`.
- **`RobokassaGateway`** — реализация на [Robokassa](https://docs.robokassa.ru/). Отличается от
  ЮKassa архитектурно: Robokassa не выдаёт свой id платежа, `InvId` генерируется нами же
  (`random_int`, не автоинкремент `Payment::$id` — чтобы не светить наружу, сколько платежей
  прошло через магазин) прямо в `createPayment()`, без единого сетевого запроса — это просто
  подписанная ссылка на `auth.robokassa.ru`. `ResultURL` у Robokassa вызывается **только при
  успехе** (в отличие от ЮKassa, где колбэки идут на разные статусы), а сама подпись — `md5`
  по фиксированной формуле с паролем №2 (`OutSum:InvId:Пароль2`), без дополнительного обратного
  запроса к провайдеру. Креды — `MerchantLogin` + пароли №1/№2 из технических настроек магазина —
  через `.env`: `ROBOKASSA_MERCHANT_LOGIN` / `ROBOKASSA_PASSWORD1` / `ROBOKASSA_PASSWORD2`, плюс
  `ROBOKASSA_TEST_MODE=1` (по умолчанию) шлёт `IsTest=1`, из-за чего Robokassa сверяет подпись по
  тестовой, а не боевой, паре паролей — при выходе в прод переключите на `0` и боевые пароли.
  Важно: Robokassa **не прощает** дженерик-ответ вида `ok` — она опрашивает `ResultURL`, пока не
  получит тело ответа ровно `OK<InvId>`, поэтому у формата ack-ответа на колбэк появился свой
  метод в интерфейсе, `getCallbackAcknowledgement()` (у ЮKassa/`fake` он просто возвращает `ok`).
- **`FakeGateway`** — шлюз без сети, третий implementer интерфейса. Проходит тот же код колбэка,
  что и боевые провайдеры, просто вызывается вручную: на странице `/payment/return` после выбора
  `fake`-способа есть панель "Dev tools: simulate the gateway's webhook" (видна везде, кроме
  `YII_ENV_PROD`) с кнопками, которые постят на реальный `payment/fake/callback` — удобно
  прогнать весь путь заказа до `paid` без тестового магазина.
- Обработка колбэка идемпотентна: повторная доставка одного и того же события — no-op
  (`services/Payment/PaymentCallbackHandler.php` проверяет, не находится ли `Payment` уже в
  терминальном статусе), но ack провайдеру всё равно возвращается по формату этого провайдера —
  иначе он продолжит ретраить уже обработанное уведомление.
- `PaymentController::actionCallback` — единственный экшен с отключённым CSRF (точечно, в
  `beforeAction`, только для этого action id) — это server-to-server вызов провайдера, а не
  запрос из браузера с сессией.

## Тестирование

Тесты лежат в `tests/`, три набора: `Unit`, `Functional`, `Acceptance` (Codeception).

```bash
make test                  # все наборы
make test-unit              # только Unit
make test-functional        # только Functional
make test-acceptance        # только Acceptance
```

Прогон конкретного файла/теста:

```bash
docker compose exec php vendor/bin/codecept run tests/Unit/Models/LoginFormTest.php --env php-builtin
docker compose exec php vendor/bin/codecept run tests/Unit/Models/LoginFormTest.php:testCorrectLogin --env php-builtin
```

После изменения акторов/модулей Codeception пересоберите классы:

```bash
make codecept-build
```

**Acceptance-тесты** по умолчанию используют `PhpBrowser` против встроенного PHP-сервера
(`--env php-builtin`). Чтобы гонять их в реальном браузере через WebDriver + Selenium, включите
соответствующую (закомментированную) конфигурацию в `tests/Acceptance.suite.yml`.

`tests/Acceptance.suite.yml` использует `transaction: false`: тестируемое приложение работает в
отдельном PHP-процессе (встроенный сервер) и видит только закоммиченные данные — поэтому любые
ORM/fixture-записи из тестового процесса не должны оборачиваться в откатываемую транзакцию.

### Покрытие кода

```bash
docker compose exec php vendor/bin/codecept run --coverage --coverage-html --coverage-xml --env php-builtin
```

Результат — в `tests/Support/output`.

## Статический анализ и code style

```bash
make static   # phpstan, level 5
make cs       # phpcs (Yii2 coding standard)
make cs-fix   # phpcbf автофикс
```

Оба инструмента ограничены каталогами `assets, commands, controllers, mail, models, widgets` и
`tests/*` (см. `phpstan.neon` / `phpcs.xml.dist`) — `config/`, `views/`, `web/` не проверяются.

## Установка без Docker

Через Composer:

```bash
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

либо в существующем проекте:

```bash
composer install
```

Задайте `cookieValidationKey` в `config/web.php`:

```php
'request' => [
    'cookieValidationKey' => '<секретная случайная строка>',
],
```

Создайте БД (Postgres или совместимую, вручную — Docker-контейнер делает это автоматически по
`POSTGRES_DB`) и настройте `config/db.php`, затем примените миграции и, при желании, seed:

```bash
php yii migrate
php yii seed/all
```

Приложение будет доступно по адресу вида `http://localhost/basic/web/`.

## Лицензия

[![License](https://img.shields.io/badge/License-BSD--3--Clause-brightgreen.svg?style=for-the-badge&logo=opensourceinitiative&logoColor=white&labelColor=555555)](LICENSE.md)
