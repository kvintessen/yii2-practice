---
title: Validate Through Model rules(), Not Ad Hoc Checks
impact: HIGH
impactDescription: Centralizes validation so it can't be skipped by a new entry point
tags: form, validation, rules, core-validators
---

## Validate Through Model rules(), Not Ad Hoc Checks

**Impact: HIGH**

Define validation in the model's `rules()` method using core validators (`required`, `email`, `integer`, `string`, `in`, `unique`, ...), then run it via `$model->load($data)` + `$model->validate()`. Ad hoc `if` checks in a controller are easy to forget in a second entry point (API, console command) that reuses the same model.

## Bad Example

```php
<?php
public function actionCreate()
{
    $data = Yii::$app->request->post();
    // Validation logic lives only in the controller — an API endpoint
    // or console command reusing Customer won't get these checks
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new BadRequestHttpException('Invalid email');
    }
    $customer = new Customer($data);
    $customer->save(false); // validation skipped entirely
}
```

## Good Example

```php
<?php
class Customer extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            ['name', 'string', 'max' => 255],
        ];
    }
}

public function actionCreate()
{
    $customer = new Customer();
    if ($customer->load(Yii::$app->request->post(), '') && $customer->save()) {
        // save() calls validate() internally by default
        return $this->redirect(['view', 'id' => $customer->id]);
    }
    return $this->render('create', ['model' => $customer]);
}
```

## Why

- **One source of truth**: any code path that saves a `Customer` — web controller, REST API, console command — gets the same validation.
- **`save()` validates by default**: calling `save(false)` to skip validation should be a deliberate, rare exception, not the default path.
- **Declarative and testable**: rules can be unit-tested directly against the model without going through HTTP.

Reference: [Validating Input Guide](https://www.yiiframework.com/doc/guide/2.0/en/input-validation)
