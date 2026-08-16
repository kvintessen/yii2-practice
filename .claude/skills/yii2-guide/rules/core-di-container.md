---
title: Constructor Injection via the DI Container
impact: HIGH
impactDescription: Makes classes testable and dependencies explicit
tags: core, di, container, dependency-injection
---

## Constructor Injection via the DI Container

**Impact: HIGH**

`Yii::$container` powers `Yii::createObject()`, which every framework factory method uses internally. It resolves type-hinted constructor parameters automatically, so classes can declare dependencies as constructor arguments instead of instantiating them internally.

## Bad Example

```php
<?php
class ReportGenerator
{
    private PdfRenderer $renderer;

    public function __construct()
    {
        $this->renderer = new PdfRenderer(); // hard-coded dependency, can't be swapped or mocked
    }
}
```

## Good Example

```php
<?php
class ReportGenerator
{
    public function __construct(
        private PdfRenderer $renderer,
    ) {
    }
}

// Container resolves PdfRenderer automatically from the type hint:
$generator = Yii::createObject(ReportGenerator::class);

// Or bind an interface to a specific implementation globally:
Yii::$container->set(RendererInterface::class, PdfRenderer::class);
Yii::$container->setSingleton(RendererInterface::class, PdfRenderer::class); // shared instance
```

## Why

- **Testability**: Tests can pass a mock/stub `PdfRenderer` instead of the real one.
- **Global swap point**: `Yii::$container->set()` lets you rebind an interface to a different implementation app-wide without touching call sites.
- **Singletons for stateless services**: `setSingleton()` avoids re-instantiating stateless objects (e.g. renderers, formatters) on every request.

Reference: [DI Container Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-di-container)
