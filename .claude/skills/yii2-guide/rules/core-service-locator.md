---
title: Access Shared Services via the Service Locator
impact: HIGH
impactDescription: Keeps components swappable and centrally configured
tags: core, service-locator, components, application
---

## Access Shared Services via the Service Locator

**Impact: HIGH**

`Yii::$app` is a service locator: components like `db`, `cache`, and `mailer` are registered once in config and retrieved by ID via `Yii::$app->get('id')` or the magic property `Yii::$app->id`. Register custom services the same way instead of instantiating them ad hoc.

## Bad Example

```php
<?php
class InvoiceMailer
{
    public function send(Invoice $invoice): void
    {
        // Bypasses config: ignores the mailer transport/credentials set up in web.php
        $mailer = new \yii\symfonymailer\Mailer();
        $mailer->compose()->setTo($invoice->email)->send();
    }
}
```

## Good Example

```php
<?php
// config/web.php
'components' => [
    'invoiceMailer' => [
        'class' => \app\components\InvoiceMailer::class,
    ],
],

// usage anywhere in the app
Yii::$app->invoiceMailer->send($invoice);

// InvoiceMailer.php — pulls the shared mailer component, doesn't build its own
class InvoiceMailer extends \yii\base\Component
{
    public function send(Invoice $invoice): void
    {
        Yii::$app->mailer->compose()->setTo($invoice->email)->send();
    }
}
```

## Why

- **Single configuration point**: Transport, credentials, and options live in one place (`web.php`), not duplicated at every call site.
- **Swappable at runtime**: Reconfiguring `mailer` in `config/test.php` (e.g. to a file-based transport for tests) doesn't require touching `InvoiceMailer`.
- **Consistent with the framework's own components**: `db`, `cache`, `urlManager`, `user`, etc. all follow this same access pattern.

Reference: [Service Locator Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-service-locator)
