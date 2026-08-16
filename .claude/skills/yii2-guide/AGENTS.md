# Yii2 Guide - Complete Reference

**Version:** 1.0.0
**Focus:** Yii2 2.0.x on PHP 8.2+, sourced from the official framework guide
**Rules:** 32 (6 security + 6 db/AR + 6 core + 4 form + 3 cache + 3 REST + 2 test + 2 routing)
**License:** MIT

---

## Step 1: Detect the Stack

**Check the project's Yii2 and PHP versions before giving advice.**

```bash
grep -A2 '"yiisoft/yii2"' composer.json   # e.g. "~2.0.54"
grep '"php"' composer.json                # e.g. ">=8.2"
php -v
```

This project (`yiisoft/yii2-app-basic`) pins `yiisoft/yii2: ~2.0.54` on `php: >=8.2`, uses Codeception (`codeception/codeception ^5.0`) with Unit/Functional/Acceptance suites configured against `config/test.php`, and `yii2-debug`/`yii2-gii` as dev-only modules.

## Overview

Practical Yii2 2.0.x guidance covering Active Record, validation, security, caching, REST APIs, routing, and testing. Each rule includes bad and good examples grounded in the official guide, plus a link to the source page.

## Categories

1. **Security (CRITICAL)** - CSRF, XSS, SQL injection, password hashing, RBAC, authentication
2. **Database & Active Record (CRITICAL)** - Query Builder, migrations, AR CRUD, relations, transactions, scopes
3. **Configuration & Application Structure (HIGH)** - App config, aliases, DI container, service locator, behaviors, events
4. **Forms & Validation (HIGH)** - Model rules, ActiveForm, scenarios/safe attributes, custom validators
5. **Caching (MEDIUM)** - Data, fragment, and HTTP caching
6. **REST API (MEDIUM)** - ActiveController, serialization, rate limiting
7. **Testing (MEDIUM)** - Codeception suites, fixtures
8. **Routing (MEDIUM)** - URL rules, controller/action conventions

---

## 1. Security

### 1.1 Keep CSRF Protection Enabled

**Impact:** CRITICAL

Yii2 validates a CSRF token on unsafe HTTP methods by default (`enableCsrfValidation = true`). Disabling it globally to make an external tool's POST "just work" removes protection from every form in the app, not just the one endpoint that needed an exception.

**Bad:**
```php
<?php
// config/web.php
'components' => [
    'request' => [
        'enableCsrfValidation' => false, // disabled app-wide to unblock one Postman test
    ],
],
```

**Good:**
```php
<?php
// config/web.php — leave CSRF validation on by default
'components' => [
    'request' => [
        'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY'),
    ],
],

// Only the specific stateless API controller that genuinely needs it opts out, explicitly:
class ApiController extends \yii\rest\ActiveController
{
    public $enableCsrfValidation = false;
}
```
```php
<?php
// view — when submitting via raw AJAX instead of ActiveForm, include the token
?>
<?= Html::csrfMetaTags() ?>
```

**Why:**
- **Scoped exceptions, not a global switch**: disabling CSRF on one token-authenticated REST controller is very different from disabling it for every session-authenticated form.
- **`ActiveForm` already includes the token** automatically; hand-rolled AJAX should add `Html::csrfMetaTags()`/the `X-CSRF-Token` header instead.
- **`sameSite` cookie attribute** adds a second browser-level CSRF mitigation layer.

Reference: [Security Best Practices Guide](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)

---

### 1.2 Encode Output for Its Context (XSS Prevention)

**Impact:** CRITICAL

Never echo user-controlled data into a view unescaped. Use `Html::encode()` for plain text, and `HtmlPurifier::process()` when the content is genuinely expected to contain (a safe subset of) HTML.

**Bad:**
```php
<?php
// $model->username is user-controlled; <script>...</script> executes for every visitor
?>
<h1>Welcome, <?= $model->username ?></h1>
<div><?= $comment->body ?></div>
```

**Good:**
```php
<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>
<h1>Welcome, <?= Html::encode($model->username) ?></h1>
<div><?= HtmlPurifier::process($comment->body) ?></div>
```

**Why:**
- **`Html::encode()` is the default for plain text**: escapes `<`, `>`, `&`, quotes so injected markup renders as inert text.
- **`HtmlPurifier` for intentional rich content**: strips dangerous tags/attributes while preserving a safe allow-listed subset.
- **Context matters**: HTML body, HTML attributes, JS strings, and URLs each need matching escaping.

Reference: [Security Best Practices Guide — Preventing XSS](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)

---

### 1.3 Never Concatenate User Input into SQL

**Impact:** CRITICAL

Both Query Builder and Active Record bind condition values through PDO automatically when you use hash/operator `where()` formats — but only if you actually use them. Raw `createCommand($sql)` with interpolated variables reopens SQL injection.

**Bad:**
```php
<?php
$search = Yii::$app->request->get('q');
$customers = Customer::findBySql(
    "SELECT * FROM customer WHERE name LIKE '%$search%'"
)->all();
```

**Good:**
```php
<?php
$search = Yii::$app->request->get('q');

$customers = Customer::find()
    ->where(['like', 'name', $search])
    ->all();

// If raw SQL is genuinely required, bind parameters explicitly
$customers = Customer::findBySql(
    'SELECT * FROM customer WHERE name LIKE :search',
    [':search' => "%$search%"]
)->all();
```

**Why:**
- **Binding, not string tricks**: the value is sent separately from SQL structure, so it can never be interpreted as SQL.
- **Applies equally to AR and Query Builder** since `ActiveQuery` extends `Query`.
- **`findBySql()`/raw `createCommand()` must use bound `:param` placeholders**, never string interpolation.

Reference: [Security Best Practices Guide — Preventing SQL Injections](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)

---

### 1.4 Hash Passwords with the Security Component, Never MD5/SHA1

**Impact:** CRITICAL

Use `Yii::$app->security->generatePasswordHash()` (bcrypt) to hash passwords, and `validatePassword()` to verify. `md5()`/`sha1()` are not designed to resist brute-force password cracking.

**Bad:**
```php
<?php
$user->password_hash = md5($password);

if (md5($input) === $user->password_hash) {
    Yii::$app->user->login($user);
}
```

**Good:**
```php
<?php
$user->password_hash = Yii::$app->security->generatePasswordHash($password);
$user->save();

if (Yii::$app->security->validatePassword($input, $user->password_hash)) {
    Yii::$app->user->login($user);
}
```

**Why:**
- **Bcrypt is deliberately slow**, resisting brute-force/rainbow-table attacks unlike general-purpose hashes.
- **Salting handled automatically**: the salt is embedded in the resulting hash string.
- **One call each way**: no reason to hand-roll hashing.

Reference: [Security Guide — Password Hashing](https://www.yiiframework.com/doc/guide/2.0/en/security-passwords)

---

### 1.5 Authorize with RBAC, Not Scattered Role Checks

**Impact:** CRITICAL

Configure the `authManager` component (`PhpManager` or `DbManager`), define roles/permissions/rules through it, and check access with `Yii::$app->user->can('permissionName')` instead of hard-coded `if ($user->role === 'admin')` checks.

**Bad:**
```php
<?php
public function actionDelete($id)
{
    if (Yii::$app->user->identity->role !== 'admin') {
        throw new ForbiddenHttpException();
    }
    Post::findOne($id)->delete();
}
```

**Good:**
```php
<?php
// config/web.php
'components' => [
    'authManager' => ['class' => \yii\rbac\DbManager::class],
],
```
```php
<?php
// one-time setup
$auth = Yii::$app->authManager;
$deletePost = $auth->createPermission('deletePost');
$auth->add($deletePost);
$admin = $auth->createRole('admin');
$auth->add($admin);
$auth->addChild($admin, $deletePost);
$auth->assign($admin, $userId);
```
```php
<?php
public function actionDelete($id)
{
    if (!Yii::$app->user->can('deletePost')) {
        throw new ForbiddenHttpException();
    }
    Post::findOne($id)->delete();
}
```

**Why:**
- **Single place to reason about permissions**, not scattered controller conditionals.
- **Business rules supported**: e.g. `can('updatePost', ['post' => $post])` for author-only edits.
- **Roles compose**: `admin` can inherit everything `editor` has.

Reference: [Authorization Guide (RBAC)](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization)

---

### 1.6 Implement IdentityInterface Fully

**Impact:** CRITICAL

Configure the `user` component with an `identityClass` implementing `yii\web\IdentityInterface`, then log in/out via `Yii::$app->user->login()`/`logout()`. Hand-rolled `$_SESSION['user_id']` auth skips the auth-key validation that guards cookie-based auto-login.

**Bad:**
```php
<?php
public function actionLogin()
{
    $user = User::findOne(['username' => Yii::$app->request->post('username')]);
    if ($user && password_verify(Yii::$app->request->post('password'), $user->password_hash)) {
        $_SESSION['user_id'] = $user->id;
    }
}
```

**Good:**
```php
<?php
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public static function findIdentity($id) { return static::findOne($id); }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
    public function getId() { return $this->id; }
    public function getAuthKey() { return $this->auth_key; }
    public function validateAuthKey($authKey) { return $this->auth_key === $authKey; }
}
```
```php
<?php
// config/web.php
'components' => ['user' => ['identityClass' => 'app\models\User']],
```
```php
<?php
public function actionLogin()
{
    $model = new LoginForm();
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
        return $this->goBack();
    }
}
```

**Why:**
- **`auth_key` guards auto-login cookies** against tampering, which a hand-rolled session check has no equivalent for.
- **Consistent access everywhere**: `Yii::$app->user->identity`/`->id`/`->isGuest` work app-wide.
- **Token auth for APIs comes for free** via `findIdentityByAccessToken()`.

Reference: [Authentication Guide](https://www.yiiframework.com/doc/guide/2.0/en/security-authentication)

---

## 2. Database & Active Record

### 2.1 Query Builder with Bound Parameters

**Impact:** CRITICAL

`yii\db\Query` builds SQL programmatically and automatically binds parameters in hash-format and operator-format `where()` conditions. Never interpolate user input directly into a SQL string.

**Bad:**
```php
<?php
$status = Yii::$app->request->get('status');
$rows = Yii::$app->db->createCommand("SELECT * FROM user WHERE status=$status")->queryAll();
```

**Good:**
```php
<?php
$status = Yii::$app->request->get('status');
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['status' => $status])
    ->all();

$rows = (new \yii\db\Query())
    ->from('user')
    ->where('status = :status', [':status' => $status])
    ->all();
```

**Why:**
- **No injection surface**: hash/operator `where()` and `params`/`addParams()` route values through PDO binding.
- **Readable conditions**: `['like', 'name', $term]`, `['between', 'id', 1, 10]` express intent clearly.
- **Same guarantee applies to Active Record** since `ActiveQuery` extends `Query`.

Reference: [Query Builder Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-query-builder)

---

### 2.2 Transactional, Reversible Migrations

**Impact:** CRITICAL

Prefer `safeUp()`/`safeDown()` over `up()`/`down()` — the `safe*` variants run implicitly inside a transaction. Use portable abstract types instead of raw column DDL.

**Bad:**
```php
<?php
public function up()
{
    $this->execute('CREATE TABLE `order` (id INT PRIMARY KEY, total DECIMAL(10,2))');
    $this->execute('CREATE INDEX idx_total ON `order` (total)');
}
public function down() { $this->execute('DROP TABLE `order`'); }
```

**Good:**
```php
<?php
public function safeUp()
{
    $this->createTable('{{%order}}', [
        'id' => $this->primaryKey(),
        'total' => $this->decimal(10, 2)->notNull(),
        'created_at' => $this->integer()->notNull(),
    ]);
    $this->createIndex('idx-order-total', '{{%order}}', 'total');
}
public function safeDown() { $this->dropTable('{{%order}}'); }
```

**Why:**
- **Atomic**: a failure partway through `safeUp()`/`safeDown()` rolls back cleanly.
- **Portable**: `$this->primaryKey()`/`decimal()` etc. generate correct DDL per driver.
- **Reverse order in `safeDown()`**.

Reference: [Migrations Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations)

---

### 2.3 Idiomatic Active Record CRUD

**Impact:** CRITICAL

Use `find()`/`save()`/`delete()` rather than raw `createCommand()` for tables backed by a model — raw commands skip validation, events, and behaviors.

**Bad:**
```php
<?php
Yii::$app->db->createCommand()->update('customer', ['email' => $newEmail], ['id' => $id])->execute();
```

**Good:**
```php
<?php
$customer = Customer::findOne($id);
$customer->email = $newEmail;
$customer->save();

$customer = new Customer();
$customer->name = 'James';
$customer->save();

$customer->delete();
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

**Why:**
- **Validation runs automatically** via `save()`.
- **Events and behaviors fire** (timestamps, audit hooks, cache invalidation).
- **Consistent, readable API** for simple CRUD.

Reference: [Active Record Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)

---

### 2.4 Eager-Load Relations to Avoid N+1 Queries

**Impact:** CRITICAL

Declare relations with `hasOne()`/`hasMany()`. Accessing a relation in a loop without `with()` triggers one query per row.

**Bad:**
```php
<?php
$customers = Customer::find()->limit(100)->all();
foreach ($customers as $customer) {
    foreach ($customer->orders as $order) { /* 1 query per customer */ }
}
```

**Good:**
```php
<?php
$customers = Customer::find()->with('orders')->limit(100)->all();
foreach ($customers as $customer) {
    foreach ($customer->orders as $order) { /* no additional query */ }
}
```

**Why:**
- **Predictable query count**: `with()` runs 2 queries total, not 1-per-row.
- **Same access pattern**: `$customer->orders` unchanged; only loading strategy differs.
- **Compose with custom scopes** for relation filtering.

Reference: [Active Record Guide — Relational Data](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)

---

### 2.5 Wrap Multi-Step Writes in a Transaction

**Impact:** CRITICAL

When an operation touches more than one AR/table, wrap writes in a transaction so a failure rolls back everything.

**Bad:**
```php
<?php
$order = new Order($orderData);
$order->save();
foreach ($order->items as $item) {
    $product = Product::findOne($item->product_id);
    $product->stock -= $item->quantity;
    $product->save(); // may fail later, leaving order without stock deduction
}
```

**Good:**
```php
<?php
Yii::$app->db->transaction(function ($db) use ($orderData) {
    $order = new Order($orderData);
    if (!$order->save()) {
        throw new \yii\base\Exception('Could not save order');
    }
    foreach ($order->items as $item) {
        $product = Product::findOne($item->product_id);
        $product->stock -= $item->quantity;
        if (!$product->save()) {
            throw new \yii\base\Exception('Could not update stock');
        }
    }
});
```

**Why:**
- **All-or-nothing**: any exception rolls back everything done so far.
- **No manual begin/commit bookkeeping** needed.
- **`transactions()` method** on an AR class marks scenarios that always run inside a transaction.

Reference: [Active Record Guide — Transactions](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)

---

### 2.6 Reusable Query Scopes via a Custom ActiveQuery Class

**Impact:** MEDIUM

Extend `ActiveQuery` with named, chainable methods and override `find()` to return it. Use `andOnCondition()`/`orOnCondition()` so scopes compose.

**Bad:**
```php
<?php
$active = Comment::find()->where(['active' => true])->all();
$activeForPost = Comment::find()->where(['active' => true, 'post_id' => $postId])->all();
```

**Good:**
```php
<?php
class CommentQuery extends \yii\db\ActiveQuery
{
    public function active($state = true) { return $this->andOnCondition(['active' => $state]); }
}
class Comment extends \yii\db\ActiveRecord
{
    public static function find() { return new CommentQuery(static::class); }
}
$active = Comment::find()->active()->all();
$activeForPost = Comment::find()->active()->andWhere(['post_id' => $postId])->all();
```

**Why:**
- **Single definition**: `active()` defined once, reused everywhere, including relations.
- **Chainable** with `with()`, `andWhere()`, etc.
- **`andOnCondition()` preserves existing conditions** instead of overwriting them.

Reference: [Active Record Guide — Customizing Query Classes](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)

---

## 3. Configuration & Application Structure

### 3.1 Application Configuration Structure

**Impact:** HIGH

`components` registers services, `params` holds tunables, `bootstrap` lists what runs every request. Keep environment-only concerns out of the base config.

**Bad:**
```php
<?php
return [
    'id' => 'app',
    'bootstrap' => ['log', 'debug', 'gii'], // bootstrapped unconditionally
    'components' => [
        'db' => ['dsn' => '...', 'password' => 'hardcoded-secret'],
    ],
];
```

**Good:**
```php
<?php
$config = [
    'id' => 'app',
    'bootstrap' => ['log'],
    'components' => ['db' => require __DIR__ . '/db.php'],
    'params' => require __DIR__ . '/params.php',
];
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = ['class' => 'yii\debug\Module'];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => 'yii\gii\Module'];
}
return $config;
```

**Why:**
- **No leaked debug tooling** in production.
- **Secrets stay out of tracked config** (env vars via `config/db.php`/`.env`).
- **Single source of tunables** via `params.php`.

Reference: [Application Structure Guide](https://www.yiiframework.com/doc/guide/2.0/en/structure-applications)

---

### 3.2 Path Aliases Instead of Hard-Coded Paths

**Impact:** MEDIUM

Use `@`-prefixed aliases resolved via `Yii::getAlias()` instead of `__DIR__`-relative concatenation.

**Bad:**
```php
<?php
return __DIR__ . '/../../web/uploads';
```

**Good:**
```php
<?php
// config/web.php: 'aliases' => ['@uploads' => '@webroot/uploads']
return Yii::getAlias('@uploads');
```

**Why:**
- **Portability** across deployment layouts.
- **Consistency** with framework classes that already accept aliases.
- **Refactor safety**: move a directory, update one alias.

Reference: [Aliases Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-aliases)

---

### 3.3 Constructor Injection via the DI Container

**Impact:** HIGH

`Yii::$container` resolves type-hinted constructor parameters automatically via `Yii::createObject()`.

**Bad:**
```php
<?php
class ReportGenerator
{
    private PdfRenderer $renderer;
    public function __construct() { $this->renderer = new PdfRenderer(); }
}
```

**Good:**
```php
<?php
class ReportGenerator
{
    public function __construct(private PdfRenderer $renderer) {}
}
$generator = Yii::createObject(ReportGenerator::class);
Yii::$container->set(RendererInterface::class, PdfRenderer::class);
Yii::$container->setSingleton(RendererInterface::class, PdfRenderer::class);
```

**Why:**
- **Testability**: pass a mock instead of the real dependency.
- **Global swap point** via `set()`.
- **Singletons for stateless services** via `setSingleton()`.

Reference: [DI Container Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-di-container)

---

### 3.4 Access Shared Services via the Service Locator

**Impact:** HIGH

`Yii::$app` is a service locator: components are registered once in config and retrieved by ID.

**Bad:**
```php
<?php
$mailer = new \yii\symfonymailer\Mailer(); // bypasses configured transport/credentials
```

**Good:**
```php
<?php
// config/web.php: 'components' => ['invoiceMailer' => [...]]
Yii::$app->invoiceMailer->send($invoice);
```

**Why:**
- **Single configuration point** for transport/options.
- **Swappable at runtime** (e.g. `config/test.php` overrides).
- **Consistent** with `db`, `cache`, `urlManager`, `user`.

Reference: [Service Locator Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-service-locator)

---

### 3.5 Use Behaviors Instead of Duplicating Cross-Cutting Logic

**Impact:** MEDIUM

Attach reusable logic (timestamps, blame columns) via `behaviors()` instead of overriding lifecycle methods in every model.

**Bad:**
```php
<?php
public function beforeSave($insert)
{
    if ($insert) { $this->created_at = time(); }
    $this->updated_at = time();
    return parent::beforeSave($insert);
}
```

**Good:**
```php
<?php
public function behaviors()
{
    return [[
        'class' => TimestampBehavior::class,
        'attributes' => [
            self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
            self::EVENT_BEFORE_UPDATE => ['updated_at'],
        ],
    ]];
}
```

**Why:**
- **No copy-pasted lifecycle overrides.**
- **Composable**: multiple behaviors, no inheritance conflicts.
- **Detachable** at runtime.

Reference: [Behaviors Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-behaviors)

---

### 3.6 Decouple Side Effects with Events

**Impact:** MEDIUM

Use `on()`/`trigger()`, or class-level `Event::on()` for AR classes, to keep side effects out of the core class.

**Bad:**
```php
<?php
public function afterSave($insert, $changedAttributes)
{
    parent::afterSave($insert, $changedAttributes);
    if ($insert) {
        Yii::$app->mailer->compose('order-confirmation', ['order' => $this])->send();
        Yii::$app->slack->notify("New order #{$this->id}");
    }
}
```

**Good:**
```php
<?php
// Order stays focused on persistence
Event::on(Order::class, Order::EVENT_AFTER_INSERT, function ($event) {
    Yii::$app->mailer->compose('order-confirmation', ['order' => $event->sender])->send();
});
Event::on(Order::class, Order::EVENT_AFTER_INSERT, function ($event) {
    Yii::$app->slack->notify("New order #{$event->sender->id}");
});
```

**Why:**
- **Single Responsibility** kept intact.
- **Add/remove listeners** without touching the model.
- **Matches framework idioms** (`EVENT_BEFORE_INSERT`, etc.).

Reference: [Events Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-events)

---

## 4. Forms & Validation

### 4.1 Validate Through Model rules()

**Impact:** HIGH

Define validation in `rules()` and run it via `load()` + `validate()`. Ad hoc controller checks are easy to miss in a second entry point.

**Bad:**
```php
<?php
if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    throw new BadRequestHttpException('Invalid email');
}
$customer->save(false); // validation skipped
```

**Good:**
```php
<?php
public function rules()
{
    return [
        [['name', 'email'], 'required'],
        ['email', 'email'],
        ['email', 'unique'],
    ];
}
// controller
if ($customer->load(Yii::$app->request->post(), '') && $customer->save()) { /* ... */ }
```

**Why:**
- **One source of truth** across web/API/console entry points.
- **`save()` validates by default.**
- **Testable directly** against the model.

Reference: [Validating Input Guide](https://www.yiiframework.com/doc/guide/2.0/en/input-validation)

---

### 4.2 Build Forms with ActiveForm

**Impact:** HIGH

`ActiveForm` binds inputs to model attributes, generating labels, errors, and client-side JS validation from `rules()`.

**Bad:**
```php
<input type="text" name="LoginForm[username]" value="<?= $model->username ?>">
```

**Good:**
```php
<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
<?php ActiveForm::end(); ?>
```

**Why:**
- **Client + server validation from one source.**
- **Correct field names automatically.**
- **Error display included.**

Reference: [Creating Forms Guide](https://www.yiiframework.com/doc/guide/2.0/en/input-forms)

---

### 4.3 Scenarios and Safe Attributes Prevent Mass Assignment

**Impact:** CRITICAL

`load()` only mass-assigns attributes that are both in an active rule and part of the current scenario. Restrict scenarios so e.g. `is_admin` is never assignable from a public form.

**Bad:**
```php
<?php
[['username', 'email', 'password', 'is_admin'], 'safe'], // is_admin mass-assignable everywhere
$user->load(Yii::$app->request->post()); // attacker adds User[is_admin]=1
```

**Good:**
```php
<?php
public function scenarios()
{
    $scenarios = parent::scenarios();
    $scenarios[self::SCENARIO_SIGNUP] = ['username', 'email', 'password']; // is_admin excluded
    return $scenarios;
}
$user = new User(['scenario' => User::SCENARIO_SIGNUP]);
$user->load(Yii::$app->request->post()); // is_admin ignored even if present
```

**Why:**
- **Closes the classic mass-assignment hole.**
- **Context-appropriate validation** per scenario.
- **Fails safe**: unlisted attributes are simply not set.

Reference: [Validating Input Guide — Scenarios](https://www.yiiframework.com/doc/guide/2.0/en/input-validation)

---

### 4.4 Encapsulate Custom Rules as Validators

**Impact:** MEDIUM

Write an inline validator (model method) or standalone `Validator` subclass instead of moving business-rule checks into the controller after `validate()`.

**Bad:**
```php
<?php
if ($model->validate()) {
    if ($model->start_date >= $model->end_date) { /* error, but model already said valid */ }
}
```

**Good:**
```php
<?php
public function rules() { return [['end_date', 'validateDateRange']]; }
public function validateDateRange($attribute, $params)
{
    if ($this->start_date >= $this->end_date) {
        $this->addError($attribute, 'End date must be after start date.');
    }
}
```

**Why:**
- **Model stays the single source of validity.**
- **Errors attach to the right field** for `ActiveForm`.
- **Reusable as a standalone class** across models.

Reference: [Validating Input Guide — Creating Validators](https://www.yiiframework.com/doc/guide/2.0/en/input-validation)

---

## 5. Caching

### 5.1 Cache Keys Must Reflect Every Determining Factor

**Impact:** MEDIUM

`getOrSet()` is the standard pattern; the key must encode every input that affects the result. Pair TTL with a `Dependency` when data can change before expiry.

**Bad:**
```php
<?php
$data = Yii::$app->cache->get('dashboard'); // same key for every user
```

**Good:**
```php
<?php
$data = Yii::$app->cache->getOrSet(
    ['dashboard', 'user' => $userId],
    fn () => $this->buildDashboard($userId),
    3600,
    new TagDependency(['tags' => "user-$userId-dashboard"])
);
TagDependency::invalidate(Yii::$app->cache, "user-$userId-dashboard");
```

**Why:**
- **Key uniqueness prevents leaks** across users/locales/params.
- **`getOrSet()` collapses get/compute/set boilerplate.**
- **Dependencies invalidate on real change**, not just TTL.

Reference: [Caching Data Guide](https://www.yiiframework.com/doc/guide/2.0/en/caching-data)

---

### 5.2 Vary Fragment Cache and Exempt Per-Request Content

**Impact:** MEDIUM

Use `variations` to split cache per language/role, and `renderDynamic()` for content that must always be fresh (CSRF tokens) inside a cached fragment.

**Bad:**
```php
<?php
if ($this->beginCache('sidebar-form', ['duration' => 3600])) {
    echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken); // goes stale
    $this->endCache();
}
```

**Good:**
```php
<?php
if ($this->beginCache('sidebar-form', ['duration' => 3600, 'variations' => [Yii::$app->language]])) {
    echo $this->renderDynamic('return Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken);');
    $this->endCache();
}
```

**Why:**
- **`variations` splits the cache key** per language/role.
- **`renderDynamic()` for per-request content** even inside cached fragments.
- **Nested fragments have independent lifetimes.**

Reference: [Fragment Caching Guide](https://www.yiiframework.com/doc/guide/2.0/en/caching-fragment)

---

### 5.3 Use HttpCache for Read-Heavy, Rarely-Changing Actions

**Impact:** MEDIUM

`yii\filters\HttpCache` lets clients skip re-fetching unchanged content via `Last-Modified`/`ETag`.

**Bad:**
```php
<?php
public function actionView($id) { return $this->render('view', ['model' => Article::findOne($id)]); }
```

**Good:**
```php
<?php
public function behaviors()
{
    return ['httpCache' => [
        'class' => \yii\filters\HttpCache::class,
        'only' => ['view'],
        'lastModified' => fn ($action, $params) => Article::findOne(Yii::$app->request->get('id'))->updated_at,
    ]];
}
```

**Why:**
- **304 Not Modified skips the render entirely.**
- **`etagSeed` for finer-grained invalidation.**
- **Set `sessionCacheLimiter` deliberately** to avoid header conflicts.

Reference: [HTTP Caching Guide](https://www.yiiframework.com/doc/guide/2.0/en/caching-http)

---

## 6. REST API

### 6.1 Build REST Endpoints on ActiveController

**Impact:** MEDIUM

`yii\rest\ActiveController` implements standard CRUD REST actions and content negotiation for a `modelClass`.

**Bad:**
```php
<?php
class UserController extends \yii\web\Controller
{
    public function actionIndex() { /* hand-written */ }
    public function actionView($id) { /* hand-written */ }
    // ...
}
```

**Good:**
```php
<?php
class UserController extends \yii\rest\ActiveController
{
    public $modelClass = 'app\models\User';
}
// urlManager: ['class' => 'yii\rest\UrlRule', 'controller' => 'user']
```

**Why:**
- **8 standard endpoints for free.**
- **Content negotiation built in.**
- **Override only what's non-standard.**

Reference: [REST Quick Start Guide](https://www.yiiframework.com/doc/guide/2.0/en/rest-quick-start)

---

### 6.2 Whitelist Serialized Fields

**Impact:** CRITICAL

By default `fields()` returns every attribute, including `password_hash`/`auth_key`. Override it to whitelist what's exposed.

**Bad:**
```php
<?php
class User extends \yii\db\ActiveRecord
{
    // No fields() override — GET /users/1 returns password_hash, auth_key, tokens
}
```

**Good:**
```php
<?php
public function fields()
{
    $fields = parent::fields();
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);
    return $fields;
}
public function extraFields() { return ['profile']; }
```

**Why:**
- **Default is "expose everything"** — treat `fields()` as a required review point on schema changes.
- **`extraFields()` keeps default payloads small.**
- **Centralized in the model.**

Reference: [REST Resources Guide](https://www.yiiframework.com/doc/guide/2.0/en/rest-resources)

---

### 6.3 Rate-Limit Public API Endpoints

**Impact:** MEDIUM

Implement `RateLimitInterface` on the identity class; `RateLimiter` attaches automatically and returns HTTP 429 once exceeded.

**Bad:**
```php
<?php
class ApiUser extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    // no rate limiting
}
```

**Good:**
```php
<?php
class ApiUser extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface, \yii\filters\RateLimitInterface
{
    public function getRateLimit($request, $action) { return [100, 600]; }
    public function loadAllowance($request, $action) { return [$this->allowance, $this->allowance_updated_at]; }
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->updateAttributes(['allowance' => $allowance, 'allowance_updated_at' => $timestamp]);
    }
}
```

**Why:**
- **Automatic once implemented**, no per-controller wiring.
- **Standard headers** let well-behaved clients back off.
- **Storage is flexible** (DB, cache, etc.).

Reference: [Rate Limiting Guide](https://www.yiiframework.com/doc/guide/2.0/en/rest-rate-limiting)

---

## 7. Testing

### 7.1 Match Codeception Suite to What's Under Test

**Impact:** MEDIUM

This project ships `tests/Unit`, `tests/Functional`, `tests/Acceptance` (configured in `codeception.yml` with the `Yii2` module against `config/test.php`). Unit tests isolate a class; Functional drives controllers without a browser; Acceptance drives a real browser.

**Bad:**
```php
<?php
// tests/Acceptance — a pure model-validation check doesn't need a browser
class UserValidationCest
{
    public function checkEmailValidation(AcceptanceTester $I) { /* slow, wrong layer */ }
}
```

**Good:**
```php
<?php
// tests/Unit/models/UserTest.php
class UserTest extends \Codeception\Test\Unit
{
    public function testEmailMustBeValid()
    {
        $user = new User(['email' => 'not-an-email']);
        $this->assertFalse($user->validate(['email']));
    }
}
```

**Why:**
- **Unit for isolated logic**, fast.
- **Functional for request/response flow** without a real browser.
- **Acceptance sparingly**, for real user journeys only.

Reference: [Testing Guide](https://www.yiiframework.com/doc/guide/2.0/en/test-environment-setup)

---

### 7.2 Use ActiveFixture for Deterministic Test Data

**Impact:** MEDIUM

Define an `ActiveFixture` per model backed by a data file, declared in `_fixtures()`. Fixtures reset to a known state before each test.

**Bad:**
```php
<?php
$user = User::findOne(['username' => 'admin']); // assumes leftover row exists
```

**Good:**
```php
<?php
class UserFixture extends \yii\test\ActiveFixture { public $modelClass = 'app\models\User'; }
// tests/Support/data/user.php returns ['admin' => ['username' => 'admin', ...]]
public function _fixtures() { return ['users' => UserFixture::class]; }
```

**Why:**
- **Deterministic, order-independent.**
- **Named row access** by alias.
- **Matches this project's existing `codeception.yml` setup.**

Reference: [Fixtures Guide](https://www.yiiframework.com/doc/guide/2.0/en/test-fixtures)

---

## 8. Routing

### 8.1 Configure urlManager Explicitly

**Impact:** MEDIUM

Enable pretty URLs with `enableStrictParsing => true` and explicit `rules`, not pretty URLs with no strict parsing (still resolves any existing `controller/action`).

**Bad:**
```php
<?php
'urlManager' => ['enablePrettyUrl' => true], // any controller/action reachable
```

**Good:**
```php
<?php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [
        'post/<id:\d+>' => 'post/view',
        '<controller:[\w-]+>/<action:[\w-]+>' => '<controller>/<action>',
    ],
],
```

**Why:**
- **`enableStrictParsing` closes the implicit-route gap.**
- **Typed parameters validate at the routing layer.**
- **`showScriptName => false`** with proper rewriting.

Reference: [Routing Guide](https://www.yiiframework.com/doc/guide/2.0/en/runtime-routing)

---

### 8.2 One actionXxx() per Operation

**Impact:** MEDIUM

Give each operation its own `actionXxx()` with typed parameters instead of one action branching on a hidden `mode` parameter.

**Bad:**
```php
<?php
public function actionManage($id, $mode)
{
    if ($mode === 'publish') { /* ... */ } elseif ($mode === 'unpublish') { /* ... */ }
}
```

**Good:**
```php
<?php
public function actionPublish(int $id) { /* ... */ }
public function actionUnpublish(int $id) { /* ... */ }
public function behaviors()
{
    return ['verbs' => ['class' => \yii\filters\VerbFilter::class, 'actions' => ['publish' => ['post'], 'unpublish' => ['post']]]];
}
```

**Why:**
- **Filters/behaviors target individual actions.**
- **Self-documenting routes** in logs and access-control config.
- **Typed action parameters** get automatic coercion/validation.

Reference: [Routing Guide — Creating URL Rules](https://www.yiiframework.com/doc/guide/2.0/en/runtime-routing)

---

## References

- [Yii2 Definitive Guide](https://www.yiiframework.com/doc/guide/2.0/en/)
- [Yii2 Class Reference](https://www.yiiframework.com/doc/api/2.0)
- [Security Best Practices](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)
- [yiisoft/yii2 on GitHub](https://github.com/yiisoft/yii2)
- [Codeception Documentation](https://codeception.com/docs)

---

**Last Updated:** August 2026
**Version:** 1.0.0
**License:** MIT
