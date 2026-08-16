---
title: Encode Output for Its Context (XSS Prevention)
impact: CRITICAL
impactDescription: Blocks stored/reflected XSS from user-controlled content
tags: sec, xss, html-encode, htmlpurifier, security
---

## Encode Output for Its Context (XSS Prevention)

**Impact: CRITICAL**

Never echo user-controlled data into a view unescaped. Use `Html::encode()` for plain text, and `HtmlPurifier::process()` when the content is genuinely expected to contain (a safe subset of) HTML.

## Bad Example

```php
<?php
// view — $model->username is user-controlled; a value like <script>...</script>
// executes in every visitor's browser (stored XSS)
?>
<h1>Welcome, <?= $model->username ?></h1>
<div><?= $comment->body ?></div>
```

## Good Example

```php
<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>
<h1>Welcome, <?= Html::encode($model->username) ?></h1>

<?php // Only when body is intentionally rich text, sanitize instead of encoding it away ?>
<div><?= HtmlPurifier::process($comment->body) ?></div>
```

## Why

- **`Html::encode()` is the default for plain text**: it escapes `<`, `>`, `&`, quotes, etc. so injected markup renders as inert text.
- **`HtmlPurifier` for intentional rich content**: strips dangerous tags/attributes (`<script>`, `onerror=`, ...) while preserving a safe allow-listed subset of HTML — never trust raw HTML input directly.
- **Context matters**: encoding rules differ for HTML body, HTML attributes, JS strings, and URLs — pick the escaping mechanism that matches where the value lands.

Reference: [Security Best Practices Guide — Preventing XSS](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)
