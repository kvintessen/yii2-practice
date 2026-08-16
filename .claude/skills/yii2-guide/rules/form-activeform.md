---
title: Build Forms with ActiveForm, Not Hand-Written Inputs
impact: HIGH
impactDescription: Wires client-side JS validation to the same rules() as the server, for free
tags: form, activeform, widget, client-validation
---

## Build Forms with ActiveForm, Not Hand-Written Inputs

**Impact: HIGH**

The `ActiveForm` widget binds `<input>` fields to a model's attributes, generating labels, error placeholders, and client-side JS validation derived directly from the model's `rules()`. Hand-written `<input>` tags require re-implementing that wiring manually and tend to drift out of sync with server-side rules.

## Bad Example

```php
<?php
// view — hand-rolled markup, no client-side validation, easy to typo the field name
?>
<form method="post">
    <input type="text" name="LoginForm[username]" value="<?= $model->username ?>">
    <input type="password" name="LoginForm[password]">
    <button type="submit">Login</button>
</form>
```

## Good Example

```php
<?php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['id' => 'login-form']); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php ActiveForm::end(); ?>
```

## Why

- **Client + server validation from one source**: JS validation rules are generated from the same `rules()` the server enforces — no duplicate logic to keep in sync.
- **Correct field names automatically**: `field($model, 'username')` generates the right `ModelName[attribute]` name/id, avoiding typos in hand-written markup.
- **Error display included**: validation error placeholders render automatically next to each field.

Reference: [Creating Forms Guide](https://www.yiiframework.com/doc/guide/2.0/en/input-forms)
