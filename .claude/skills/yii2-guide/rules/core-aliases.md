---
title: Path Aliases Instead of Hard-Coded Paths
impact: MEDIUM
impactDescription: Keeps paths portable across environments and deployments
tags: core, aliases, paths, config
---

## Path Aliases Instead of Hard-Coded Paths

**Impact: MEDIUM**

Yii2 represents file paths and URLs with `@`-prefixed aliases (`@app`, `@web`, `@runtime`, `@vendor`, ...) resolved via `Yii::getAlias()`. Use them instead of `__DIR__`-relative concatenation or absolute paths, and register custom aliases for app-specific directories.

## Bad Example

```php
<?php
class UploadService
{
    public function targetDir(): string
    {
        // Breaks if the app is moved, deployed differently, or run from a different entry script
        return __DIR__ . '/../../web/uploads';
    }
}
```

## Good Example

```php
<?php
// config/web.php
'aliases' => [
    '@uploads' => '@webroot/uploads',
],

// UploadService.php
class UploadService
{
    public function targetDir(): string
    {
        return Yii::getAlias('@uploads');
    }
}
```

## Why

- **Portability**: Aliases resolve relative to `@app`/`@webroot`, so the app works regardless of deployment layout.
- **Consistency**: Framework classes (e.g. `FileCache::$cachePath`) already accept aliases directly (`'@runtime/cache'`) — using the same convention in app code keeps things uniform.
- **Refactor safety**: Moving a directory means updating one alias definition, not every hard-coded reference.

Reference: [Aliases Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-aliases)
