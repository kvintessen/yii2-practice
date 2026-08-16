---
title: Encapsulate Custom Rules as Validators, Not Post-Save Checks
impact: MEDIUM
impactDescription: Reuses non-standard validation logic across models instead of duplicating it in controllers
tags: form, validation, custom-validator, inline-validator
---

## Encapsulate Custom Rules as Validators, Not Post-Save Checks

**Impact: MEDIUM**

For logic core validators don't cover, write an inline validator (a model method) or, if it's reused across models, a standalone class extending `yii\validators\Validator`. Don't move that logic into the controller after `validate()` — it bypasses the model's validation contract and won't run for other entry points.

## Bad Example

```php
<?php
public function actionCreate()
{
    $model = new Event();
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        // Business rule checked only here, after the model already reported itself "valid"
        if ($model->start_date >= $model->end_date) {
            Yii::$app->session->setFlash('error', 'Start date must be before end date');
            return $this->render('create', ['model' => $model]);
        }
        $model->save();
    }
}
```

## Good Example

```php
<?php
class Event extends \yii\base\Model
{
    public $start_date;
    public $end_date;

    public function rules()
    {
        return [
            [['start_date', 'end_date'], 'required'],
            ['end_date', 'validateDateRange'], // inline validator
        ];
    }

    public function validateDateRange($attribute, $params)
    {
        if ($this->start_date >= $this->end_date) {
            $this->addError($attribute, 'End date must be after start date.');
        }
    }
}
```

## Why

- **Model stays the single source of validity**: `$model->validate()` returning `true` means genuinely valid everywhere the model is used, not just in one controller action.
- **Errors attach to the right field**: `addError($attribute, ...)` surfaces in `ActiveForm` next to the relevant input automatically.
- **Reusable as a standalone class**: logic needed across multiple models belongs in a `yii\validators\Validator` subclass overriding `validateAttribute()`.

Reference: [Validating Input Guide — Creating Validators](https://www.yiiframework.com/doc/guide/2.0/en/input-validation)
