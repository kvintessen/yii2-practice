# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Yii2 "basic" application template (`yiisoft/yii2-app-basic`), PHP >=8.2, running on Yii2 ~2.0.54.
The stock template's demo/in-memory auth has been replaced with a real database-backed user
system: `models/User.php` (ActiveRecord + `IdentityInterface`), `models/LoginForm.php`,
`models/SignupForm.php`, and a matching migration (`migrations/m260816_125902_create_user_table.php`).

## Development environment

Dev stack is `nginx` + `php-fpm` (PHP 8.4 in the container, `php.ini`/`composer.json` require >=8.2)
+ `postgres`, defined in `docker-compose.yml` and `.env`. All app commands below run **inside the
`php` container** unless you have a local PHP/Postgres setup instead.

```bash
docker compose build
docker compose run --rm php composer install     # creates vendor/, generates cookieValidationKey
docker compose up -d
docker compose exec php php yii migrate           # apply migrations (creates the `user` table)
```

App is served at `http://127.0.0.1:8080` (port from `NGINX_PORT` in `.env`).

- Xdebug is bundled in the `dev` image stage but disabled by default; enable per-run with
  `XDEBUG_MODE=debug docker compose up -d` or set it in `.env`.
- `config/db.php` / `config/test_db.php` read `POSTGRES_HOST` / `POSTGRES_DB` / `POSTGRES_USER` /
  `POSTGRES_PASSWORD` from `.env`. The Postgres **port inside** the DSN is hardcoded to `5432`
  (container-internal) regardless of `POSTGRES_PORT`, which only maps the host-side port.
- The test database is `{POSTGRES_DB}_test` (see `config/test_db.php`) — separate from the dev DB.
- Postgres data persists in the `db-data` volume; `docker compose down -v` resets it.
- `docker/php/Dockerfile` has four stages: `base` (PHP + extensions only), `dev` (bind-mount
  workflow above, Xdebug), `build` (runs `composer install --no-dev` and bakes the app in — not
  used by `docker-compose.yml`), and `prod` (copies the `build` output only; no composer, no
  compilers, runs as `www-data`). Build the deployable image with
  `docker build -f docker/php/Dockerfile --target prod -t <tag> .`; both `dev` and `prod` expose a
  `pgrep`-based `HEALTHCHECK` on the php-fpm master process.

## Common commands

Run these with `docker compose exec php <cmd>` (or bare, if running PHP locally with vendor installed).

```bash
# Tests (Codeception: Unit, Functional, Acceptance suites)
composer tests                                    # = codecept run --env php-builtin
vendor/bin/codecept build                          # regenerate Support/_generated actor classes after actor/module changes
vendor/bin/codecept run --env php-builtin
vendor/bin/codecept run Unit --env php-builtin
vendor/bin/codecept run Functional --env php-builtin
vendor/bin/codecept run Acceptance --env php-builtin
vendor/bin/codecept run tests/Unit/Models/LoginFormTest.php --env php-builtin   # single file
vendor/bin/codecept run tests/Unit/Models/LoginFormTest.php:testCorrectLogin --env php-builtin  # single test

# Static analysis / linting
composer static                                    # phpstan, level 5, memory_limit=-1
composer cs                                         # phpcs (Yii2 coding standard, phpcs.xml.dist)
composer cs-fix                                     # phpcbf autofix

# Migrations
php yii migrate                                     # apply
php yii migrate/create create_xxx_table              # scaffold a new migration
php yii migrate/down                                 # revert last

# Console commands live in commands/ (e.g. `php yii hello`)
```

Acceptance tests default to `PhpBrowser` against the built-in PHP server (`--env php-builtin`).
Switching to real-browser WebDriver+Selenium requires editing `tests/Acceptance.suite.yml`
(commented example config included there).

Coverage: `vendor/bin/codecept run --coverage --coverage-html --coverage-xml --env php-builtin`,
output under `tests/Support/output`.

## Architecture notes

- **DI-injected `Security`**: `SiteController`, `LoginForm`, and `SignupForm` all take
  `yii\base\Security` via constructor injection (readonly property) rather than calling
  `Yii::$app->security` directly — follow this pattern for new auth-related code so it stays
  testable without a full app instance.
- **Auth flow**: `SiteController::actionLogin/actionSignup/actionLogout` are the only site auth
  entry points. `LoginForm` looks up `User::findByUsername()` and validates via
  `security->validatePassword()`; `SignupForm::signup()` hashes with
  `security->generatePasswordHash()` and calls `User::generateAuthKey()` before saving.
- **User table** is created by migration, not fixtures/seeds baked into the schema file. The
  migration seeds two demo accounts (`admin`/`demo`, ids 100/101) with fixed `auth_key`/
  `access_token` values that the test suite asserts against — treat those ids/keys as stable
  test fixtures, not incidental data, and mirror them in `tests/Support/data` fixtures if you add
  more.
- **Mailer**: `MailerInterface` is registered as a DI singleton in `config/web.php` (and
  console) pointing at `yii\symfonymailer\Mailer` with `useFileTransport => true` (mail written to
  files, not sent) by default. In tests, `tests/Support/MailerBootstrap.php` re-registers the DI
  singleton to Codeception's `TestMailer` mock *after* app bootstrap, since the container is wired
  before Codeception swaps the mailer component — needed because DI singletons don't
  auto-follow component replacement.
- **Config layering**: `config/web.php` and `config/console.php` both merge `params.php` +
  `db.php`; `config/test.php` merges `params.php` + `test_db.php` and is what Codeception's Yii2
  module loads (`codeception.yml` -> `modules.config.Yii2.configFile`). `gii` and `debug` modules
  only bootstrap under `YII_ENV_DEV`.
- **PHPStan** (level 5) and **phpcs** (Yii2 coding standard) both scope to
  `assets, commands, controllers, mail, models, widgets` plus `tests/*` (not `config/`, `views/`,
  `web/`) — check `phpstan.neon` / `phpcs.xml.dist` before assuming a directory is linted.

## Skills / agent guidance in this repo

Two skill sets are vendored under `.claude/skills/` (and mirrored in `.agents/skills/`):
- `yii2-guide` — Yii2 2.0.x rules (security, AR, forms, caching, REST, routing, testing), sourced
  from the official framework guide. Load it for anything touching validation rules, RBAC, CSRF,
  Active Record relations/scopes/transactions, or Codeception fixtures.
- `php-best-practices` — PSR/SOLID/modern-PHP rules (typed properties, readonly, enums, etc.).

Use the `git-commit` skill for commit message conventions in this repo rather than inventing your
own format.
