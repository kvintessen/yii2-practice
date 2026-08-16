<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Category;
use app\models\Order;
use app\models\OrderItem;
use app\models\Product;
use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Seeds demo data for the online store: the admin/demo accounts, customer
 * users, a category tree, a product catalog, and a handful of orders in
 * various statuses.
 *
 * Every action is idempotent (matched by username/slug/sku, or — for
 * orders, which have no natural key — by "any order already exists"), so
 * it's safe to run `php yii seed/all` repeatedly against a database that
 * already has some or all of the seeded rows. Migrations only create
 * schema (including the 'admin' RBAC role) — this command is the single
 * source of the admin/admin and demo/demo accounts for dev/prod; the test
 * suite uses its own fixtures instead.
 */
class SeedController extends Controller
{
    private const CUSTOMER_PASSWORD = 'password';

    /** @var array<int, array{username: string, email: string}> */
    private const CUSTOMERS = [
        ['username' => 'alice', 'email' => 'alice@example.com'],
        ['username' => 'bob', 'email' => 'bob@example.com'],
        ['username' => 'carol', 'email' => 'carol@example.com'],
        ['username' => 'dave', 'email' => 'dave@example.com'],
        ['username' => 'erin', 'email' => 'erin@example.com'],
    ];

    /** @var array<int, array{name: string, slug: string, parent: string|null}> */
    private const CATEGORIES = [
        ['name' => 'Electronics', 'slug' => 'electronics', 'parent' => null],
        ['name' => 'Laptops', 'slug' => 'laptops', 'parent' => 'electronics'],
        ['name' => 'Smartphones', 'slug' => 'smartphones', 'parent' => 'electronics'],
        ['name' => 'Accessories', 'slug' => 'accessories', 'parent' => 'electronics'],
        ['name' => 'Clothing', 'slug' => 'clothing', 'parent' => null],
        ['name' => "Men's Clothing", 'slug' => 'mens-clothing', 'parent' => 'clothing'],
        ['name' => "Women's Clothing", 'slug' => 'womens-clothing', 'parent' => 'clothing'],
        ['name' => 'Books', 'slug' => 'books', 'parent' => null],
        ['name' => 'Home & Kitchen', 'slug' => 'home-kitchen', 'parent' => null],
    ];

    /**
     * @var array<int, array{
     *     name: string, slug: string, sku: string, price: float, stock: int,
     *     status: string, description: string, categories: string[]
     * }>
     */
    private const PRODUCTS = [
        ['name' => 'MacBook Pro 14"', 'slug' => 'macbook-pro-14', 'sku' => 'LAP-001', 'price' => 1999.00, 'stock' => 15, 'status' => Product::STATUS_ACTIVE, 'description' => 'Apple M3 Pro, 14-inch Liquid Retina XDR display, 18GB RAM.', 'categories' => ['laptops']],
        ['name' => 'Dell XPS 13', 'slug' => 'dell-xps-13', 'sku' => 'LAP-002', 'price' => 1299.00, 'stock' => 20, 'status' => Product::STATUS_ACTIVE, 'description' => 'Compact 13.4-inch ultrabook with Intel Core Ultra and InfinityEdge display.', 'categories' => ['laptops']],
        ['name' => 'Lenovo ThinkPad X1 Carbon', 'slug' => 'thinkpad-x1-carbon', 'sku' => 'LAP-003', 'price' => 1499.00, 'stock' => 10, 'status' => Product::STATUS_ACTIVE, 'description' => 'Business ultrabook, carbon-fiber chassis, MIL-SPEC durability.', 'categories' => ['laptops']],
        ['name' => 'iPhone 15 Pro', 'slug' => 'iphone-15-pro', 'sku' => 'PHN-001', 'price' => 1099.00, 'stock' => 30, 'status' => Product::STATUS_ACTIVE, 'description' => 'Titanium design, A17 Pro chip, 48MP main camera.', 'categories' => ['smartphones']],
        ['name' => 'Samsung Galaxy S24', 'slug' => 'samsung-galaxy-s24', 'sku' => 'PHN-002', 'price' => 899.00, 'stock' => 25, 'status' => Product::STATUS_ACTIVE, 'description' => 'AMOLED display, Snapdragon 8 Gen 3, AI-powered camera suite.', 'categories' => ['smartphones']],
        ['name' => 'Google Pixel 8', 'slug' => 'google-pixel-8', 'sku' => 'PHN-003', 'price' => 699.00, 'stock' => 18, 'status' => Product::STATUS_DRAFT, 'description' => 'Google Tensor G3, stock Android, 7 years of OS updates. Not yet launched.', 'categories' => ['smartphones']],
        ['name' => 'Wireless Mouse', 'slug' => 'wireless-mouse', 'sku' => 'ACC-001', 'price' => 29.99, 'stock' => 100, 'status' => Product::STATUS_ACTIVE, 'description' => 'Ergonomic wireless mouse with silent clicks and USB-C charging.', 'categories' => ['accessories', 'electronics']],
        ['name' => 'USB-C Hub', 'slug' => 'usb-c-hub', 'sku' => 'ACC-002', 'price' => 49.99, 'stock' => 60, 'status' => Product::STATUS_ACTIVE, 'description' => '7-in-1 USB-C hub: HDMI, SD card reader, 3x USB-A, PD passthrough.', 'categories' => ['accessories']],
        ['name' => 'Mechanical Keyboard', 'slug' => 'mechanical-keyboard', 'sku' => 'ACC-003', 'price' => 89.99, 'stock' => 40, 'status' => Product::STATUS_ACTIVE, 'description' => 'Hot-swappable mechanical keyboard with tactile brown switches.', 'categories' => ['accessories']],
        ['name' => "Men's Denim Jacket", 'slug' => 'mens-denim-jacket', 'sku' => 'MEN-001', 'price' => 79.99, 'stock' => 50, 'status' => Product::STATUS_ACTIVE, 'description' => 'Classic fit denim jacket, 100% cotton.', 'categories' => ['mens-clothing']],
        ['name' => "Men's Running Shoes", 'slug' => 'mens-running-shoes', 'sku' => 'MEN-002', 'price' => 119.99, 'stock' => 35, 'status' => Product::STATUS_ACTIVE, 'description' => 'Lightweight running shoes with responsive cushioning.', 'categories' => ['mens-clothing']],
        ['name' => "Women's Summer Dress", 'slug' => 'womens-summer-dress', 'sku' => 'WOM-001', 'price' => 59.99, 'stock' => 45, 'status' => Product::STATUS_ACTIVE, 'description' => 'Flowy midi dress in breathable linen blend.', 'categories' => ['womens-clothing']],
        ['name' => "Women's Leather Handbag", 'slug' => 'womens-leather-handbag', 'sku' => 'WOM-002', 'price' => 149.99, 'stock' => 20, 'status' => Product::STATUS_ACTIVE, 'description' => 'Full-grain leather handbag with adjustable strap.', 'categories' => ['womens-clothing']],
        ['name' => 'Clean Code', 'slug' => 'clean-code-book', 'sku' => 'BOOK-001', 'price' => 34.99, 'stock' => 75, 'status' => Product::STATUS_ACTIVE, 'description' => 'Robert C. Martin — a handbook of agile software craftsmanship.', 'categories' => ['books']],
        ['name' => 'The Pragmatic Programmer', 'slug' => 'pragmatic-programmer-book', 'sku' => 'BOOK-002', 'price' => 39.99, 'stock' => 60, 'status' => Product::STATUS_ACTIVE, 'description' => 'Hunt & Thomas — journey to mastery, 20th anniversary edition.', 'categories' => ['books']],
        ['name' => 'Stainless Steel Cookware Set', 'slug' => 'stainless-steel-cookware-set', 'sku' => 'HOME-001', 'price' => 199.99, 'stock' => 12, 'status' => Product::STATUS_ACTIVE, 'description' => '10-piece tri-ply stainless steel cookware set, oven safe.', 'categories' => ['home-kitchen']],
        ['name' => 'Electric Kettle', 'slug' => 'electric-kettle', 'sku' => 'HOME-002', 'price' => 34.99, 'stock' => 55, 'status' => Product::STATUS_DRAFT, 'description' => '1.7L rapid-boil electric kettle with auto shut-off. Pending QA.', 'categories' => ['home-kitchen']],
    ];

    /**
     * @var array<int, array{username: string, status: string, daysAgo: int, items: array<int, array{sku: string, quantity: int}>}>
     */
    private const ORDERS = [
        ['username' => 'demo', 'status' => Order::STATUS_DONE, 'daysAgo' => 25, 'items' => [
            ['sku' => 'LAP-001', 'quantity' => 1],
            ['sku' => 'ACC-001', 'quantity' => 1],
        ]],
        ['username' => 'alice', 'status' => Order::STATUS_PAID, 'daysAgo' => 14, 'items' => [
            ['sku' => 'PHN-001', 'quantity' => 1],
        ]],
        ['username' => 'alice', 'status' => Order::STATUS_NEW, 'daysAgo' => 2, 'items' => [
            ['sku' => 'MEN-002', 'quantity' => 1],
            ['sku' => 'BOOK-001', 'quantity' => 2],
        ]],
        ['username' => 'bob', 'status' => Order::STATUS_SHIPPED, 'daysAgo' => 7, 'items' => [
            ['sku' => 'LAP-002', 'quantity' => 1],
            ['sku' => 'ACC-002', 'quantity' => 1],
        ]],
        ['username' => 'carol', 'status' => Order::STATUS_NEW, 'daysAgo' => 1, 'items' => [
            ['sku' => 'WOM-001', 'quantity' => 2],
        ]],
        ['username' => 'dave', 'status' => Order::STATUS_DONE, 'daysAgo' => 30, 'items' => [
            ['sku' => 'ACC-003', 'quantity' => 1],
            ['sku' => 'BOOK-002', 'quantity' => 1],
            ['sku' => 'HOME-001', 'quantity' => 1],
        ]],
        ['username' => 'erin', 'status' => Order::STATUS_PAID, 'daysAgo' => 10, 'items' => [
            ['sku' => 'PHN-002', 'quantity' => 1],
            ['sku' => 'WOM-002', 'quantity' => 1],
        ]],
    ];

    /**
     * Runs every seeder in dependency order: users, categories, products,
     * then orders (which reference the first three).
     */
    public function actionAll(): int
    {
        $this->actionUsers();
        $this->actionCategories();
        $this->actionProducts();
        $this->actionOrders();

        $this->stdout("\nSeeding complete.\n");

        return ExitCode::OK;
    }

    /**
     * Seeds the admin/demo accounts (with the admin RBAC role assigned to
     * the admin account) plus the customer accounts. Skips any username
     * that already exists.
     */
    public function actionUsers(): int
    {
        $this->seedAccount('admin', 'admin@example.com', 'admin', isAdmin: true);
        $this->seedAccount('demo', 'demo@example.com', 'demo', isAdmin: false);

        foreach (self::CUSTOMERS as $data) {
            $this->seedAccount($data['username'], $data['email'], self::CUSTOMER_PASSWORD, isAdmin: false);
        }

        return ExitCode::OK;
    }

    private function seedAccount(string $username, string $email, string $password, bool $isAdmin): void
    {
        $user = User::findByUsername($username);

        if ($user === null) {
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->password_hash = Yii::$app->security->generatePasswordHash($password);
            $user->generateAuthKey();

            if (!$user->save()) {
                $this->stderr("Failed to create user '{$username}': " . json_encode($user->errors) . "\n");

                return;
            }

            $this->stdout("- created user '{$username}' (password: {$password})\n");
        } else {
            $this->stdout("- user '{$username}' already exists, skipping\n");
        }

        if ($isAdmin) {
            $this->assignAdminRole($user);
        }
    }

    private function assignAdminRole(User $user): void
    {
        $authManager = Yii::$app->authManager;
        $adminRole = $authManager->getRole('admin');

        if ($adminRole === null) {
            $this->stderr("Role 'admin' not found — run migrations first.\n");

            return;
        }

        if ($authManager->getAssignment('admin', $user->id) === null) {
            $authManager->assign($adminRole, $user->id);
            $this->stdout("- assigned 'admin' role to '{$user->username}'\n");
        }
    }

    /**
     * Seeds the category tree (parents before children, matched by slug).
     */
    public function actionCategories(): int
    {
        foreach (self::CATEGORIES as $data) {
            if (Category::findOne(['slug' => $data['slug']]) !== null) {
                $this->stdout("- category '{$data['slug']}' already exists, skipping\n");
                continue;
            }

            $category = new Category();
            $category->name = $data['name'];
            $category->slug = $data['slug'];

            if ($data['parent'] !== null) {
                $category->parent_id = Category::findOne(['slug' => $data['parent']])?->id;
            }

            if (!$category->save()) {
                $this->stderr('Failed to create category \'' . $data['slug'] . '\': ' . json_encode($category->errors) . "\n");
                continue;
            }

            $this->stdout("- created category '{$data['name']}'\n");
        }

        return ExitCode::OK;
    }

    /**
     * Seeds the product catalog and links each product to its categories
     * (matched by sku).
     */
    public function actionProducts(): int
    {
        foreach (self::PRODUCTS as $data) {
            if (Product::findOne(['sku' => $data['sku']]) !== null) {
                $this->stdout("- product '{$data['sku']}' already exists, skipping\n");
                continue;
            }

            $product = new Product();
            $product->name = $data['name'];
            $product->slug = $data['slug'];
            $product->description = $data['description'];
            $product->price = (string) $data['price'];
            $product->stock = $data['stock'];
            $product->status = $data['status'];
            $product->sku = $data['sku'];

            if (!$product->save()) {
                $this->stderr('Failed to create product \'' . $data['sku'] . '\': ' . json_encode($product->errors) . "\n");
                continue;
            }

            foreach ($data['categories'] as $slug) {
                $category = Category::findOne(['slug' => $slug]);

                if ($category !== null) {
                    $product->link('categories', $category);
                }
            }

            $this->stdout("- created product '{$data['name']}' ({$data['sku']})\n");
        }

        return ExitCode::OK;
    }

    /**
     * Seeds a handful of orders across statuses/users, snapshotting product
     * name/price into order_item the same way PlaceOrderHandler does. Has
     * no natural key to dedupe on, so it only runs when the order table is
     * still empty.
     */
    public function actionOrders(): int
    {
        if (Order::find()->exists()) {
            $this->stdout("- orders already seeded, skipping\n");

            return ExitCode::OK;
        }

        foreach (self::ORDERS as $data) {
            $user = User::findByUsername($data['username']);

            if ($user === null) {
                $this->stderr("User '{$data['username']}' not found, skipping order.\n");
                continue;
            }

            $order = new Order();
            $order->user_id = $user->id;
            $order->status = $data['status'];
            $order->total = '0';

            if (!$order->save()) {
                $this->stderr('Failed to create order for \'' . $data['username'] . '\': ' . json_encode($order->errors) . "\n");
                continue;
            }

            $total = 0.0;

            foreach ($data['items'] as $itemData) {
                $product = Product::findOne(['sku' => $itemData['sku']]);

                if ($product === null) {
                    continue;
                }

                $lineTotal = round((float) $product->price * $itemData['quantity'], 2);

                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $product->id;
                $orderItem->product_name = $product->name;
                $orderItem->unit_price = $product->price;
                $orderItem->quantity = $itemData['quantity'];
                $orderItem->line_total = (string) $lineTotal;
                $orderItem->save();

                $total += $lineTotal;

                Yii::$app->db->createCommand()->update(
                    '{{%product}}',
                    ['stock' => max(0, $product->stock - $itemData['quantity'])],
                    ['id' => $product->id],
                )->execute();
            }

            $order->total = (string) round($total, 2);
            $order->save(false, ['total']);

            $timestamp = time() - $data['daysAgo'] * 86400;
            Yii::$app->db->createCommand()->update(
                '{{%order}}',
                ['created_at' => $timestamp, 'updated_at' => $timestamp],
                ['id' => $order->id],
            )->execute();
            Yii::$app->db->createCommand()->update(
                '{{%order_item}}',
                ['created_at' => $timestamp],
                ['order_id' => $order->id],
            )->execute();

            $this->stdout("- created order #{$order->id} for '{$data['username']}' ({$data['status']}, total {$order->total})\n");
        }

        return ExitCode::OK;
    }
}
