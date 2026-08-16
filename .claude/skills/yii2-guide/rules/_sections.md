# Sections

This file defines all sections, their ordering, impact levels, and descriptions.
The section ID (in parentheses) is the filename prefix used to group rules.

---

## 1. Configuration & Application Structure (core)

**Impact:** HIGH
**Description:** How a Yii2 application is wired together — configuration arrays, path aliases, the DI container, the service locator, behaviors, and events. Getting these right keeps components testable, decoupled, and consistent with framework conventions instead of fighting them.

## 2. Database & Active Record (db / ar)

**Impact:** CRITICAL
**Description:** Query Builder and Active Record are the primary way Yii2 apps talk to the database. Covers safe query construction, migrations, CRUD conventions, relations and eager loading, transactions, and reusable query scopes. Getting this wrong causes SQL injection, N+1 query storms, or inconsistent writes.

## 3. Forms & Validation (form)

**Impact:** HIGH
**Description:** Model-based validation (`rules()`), the `ActiveForm` widget, scenarios/safe attributes, and custom validators. This is Yii2's primary defense against malformed and malicious input reaching business logic — and the main guard against mass-assignment vulnerabilities.

## 4. Security (sec)

**Impact:** CRITICAL
**Description:** CSRF protection, output encoding (XSS), SQL injection prevention, password hashing, RBAC authorization, and authentication via `IdentityInterface`. These map directly to Yii2's official Security Best Practices guide and OWASP-relevant concerns.

## 5. Caching (cache)

**Impact:** MEDIUM
**Description:** Data caching, fragment caching in views, and HTTP caching via the `HttpCache` filter. Misused caching causes stale or leaked data; unused caching wastes obvious performance wins.

## 6. REST API (rest)

**Impact:** MEDIUM
**Description:** Building JSON/XML APIs with `yii\rest\ActiveController`, controlling serialized output with `fields()`/`extraFields()`, and protecting endpoints with rate limiting.

## 7. Testing (test)

**Impact:** MEDIUM
**Description:** This project uses Codeception with Unit/Functional/Acceptance suites and the Yii2 module. Covers matching test type to the concern under test and using `ActiveFixture` for deterministic, order-independent data.

## 8. Routing (route)

**Impact:** MEDIUM
**Description:** `urlManager` configuration, pretty URL rules, and controller/action naming conventions that keep routes both readable and safe (not accidentally exposing every controller/action pair).
