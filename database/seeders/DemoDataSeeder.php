<?php

namespace Database\Seeders;

use App\Enums\AddressType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds a realistic demo catalog, customer, and order.
 *
 * Every record is created only when it does not already exist, so re-running
 * the seeder never replaces data that a developer or admin has changed.
 */
class DemoDataSeeder extends Seeder
{
    /**
     * Seed the database with demo store data.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $categories = $this->seedCategories();

            $this->seedProducts($categories);
        });

        DB::transaction(function (): void {
            $this->seedCustomerOrder();
        });
    }

    /**
     * Seed the category tree and return the categories keyed by name.
     *
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $tree = [
            'Electronics' => ['Phones', 'Laptops', 'Audio'],
            'Home & Kitchen' => ['Cookware', 'Storage'],
            'Books' => ['Fiction', 'Business'],
        ];

        $categories = [];

        foreach ($tree as $parentName => $childNames) {
            $parent = Category::query()->firstOrCreate(
                ['slug' => Str::slug($parentName)],
                ['name' => $parentName],
            );

            $categories[$parentName] = $parent;

            foreach ($childNames as $childName) {
                $child = Category::query()->firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    ['name' => $childName, 'parent_id' => $parent->getKey()],
                );

                $categories[$childName] = $child;
            }
        }

        return $categories;
    }

    /**
     * Seed the demo products, each with a primary image.
     *
     * @param  array<string, Category>  $categories
     */
    private function seedProducts(array $categories): void
    {
        $definitions = [
            ['category' => 'Phones', 'name' => 'Aurora X2 Smartphone', 'sku' => 'SKU-PHONE-0001', 'price' => 699.00, 'stock' => 25, 'featured' => true, 'description' => 'Flagship 6.5" smartphone with a 108 MP camera and all-day battery.'],
            ['category' => 'Phones', 'name' => 'Aurora X2 Clear Case', 'sku' => 'SKU-PHONE-0002', 'price' => 24.99, 'stock' => 120, 'featured' => false, 'description' => 'Shock-absorbing clear case that shows off the Aurora X2.'],
            ['category' => 'Laptops', 'name' => 'Vertex Pro 14 Laptop', 'sku' => 'SKU-LAPTOP-0001', 'price' => 1299.00, 'stock' => 12, 'featured' => true, 'description' => '14" aluminium ultrabook with 16 GB RAM and a 1 TB SSD.'],
            ['category' => 'Audio', 'name' => 'EchoBuds Wireless Earbuds', 'sku' => 'SKU-AUDIO-0001', 'price' => 149.99, 'stock' => 60, 'featured' => true, 'description' => 'True wireless earbuds with active noise cancelling and 30 h battery.'],
            ['category' => 'Audio', 'name' => 'Studio One Over-Ear Headphones', 'sku' => 'SKU-AUDIO-0002', 'price' => 249.50, 'stock' => 30, 'featured' => true, 'description' => 'Closed-back studio headphones with replaceable memory-foam pads.'],
            ['category' => 'Cookware', 'name' => 'Cast Iron Skillet 10 Inch', 'sku' => 'SKU-COOK-0001', 'price' => 44.95, 'stock' => 80, 'featured' => false, 'description' => 'Pre-seasoned cast iron skillet for stovetop, oven, and grill.'],
            ['category' => 'Cookware', 'name' => 'Stainless Steel Cookware Set', 'sku' => 'SKU-COOK-0002', 'price' => 189.00, 'stock' => 20, 'featured' => true, 'description' => 'Ten-piece tri-ply stainless steel cookware set.'],
            ['category' => 'Storage', 'name' => 'Stackable Storage Bins Set of 4', 'sku' => 'SKU-STORE-0001', 'price' => 34.99, 'stock' => 100, 'featured' => false, 'description' => 'Clear stackable bins with snap lids for pantry or garage storage.'],
            ['category' => 'Fiction', 'name' => 'The Midnight Archive', 'sku' => 'SKU-BOOK-0001', 'price' => 16.99, 'stock' => 150, 'featured' => true, 'description' => 'A librarian discovers a shelf of books that rewrite themselves overnight.'],
            ['category' => 'Business', 'name' => 'Lean Retail Playbook', 'sku' => 'SKU-BOOK-0002', 'price' => 29.99, 'stock' => 75, 'featured' => false, 'description' => 'Practical inventory and fulfilment strategies for growing online stores.'],
        ];

        foreach ($definitions as $definition) {
            $product = Product::query()->firstOrCreate(
                ['sku' => $definition['sku']],
                [
                    'category_id' => $categories[$definition['category']]->getKey(),
                    'name' => $definition['name'],
                    'slug' => Str::slug($definition['name']),
                    'description' => $definition['description'],
                    'price' => $definition['price'],
                    'stock_quantity' => $definition['stock'],
                    'status' => ProductStatus::Active,
                    'featured' => $definition['featured'],
                ],
            );

            $this->ensureProductImage($product);
        }
    }

    /**
     * Ensure the product has a primary image record whose file exists on disk.
     *
     * Generated placeholders are simple SVG artwork written to the public
     * storage disk, so the demo catalog renders without any manual downloads.
     */
    private function ensureProductImage(Product $product): void
    {
        $image = $product->images()->first();

        if ($image === null) {
            $product->images()->create([
                'path' => 'products/'.$product->slug.'.svg',
                'position' => 0,
                'is_primary' => true,
            ]);

            $image = $product->images()->first();
        }

        if (! Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->put(
                'products/'.$product->slug.'.svg',
                $this->placeholderArtwork($product),
            );

            if ($image->path !== 'products/'.$product->slug.'.svg') {
                $image->update(['path' => 'products/'.$product->slug.'.svg']);
            }
        }
    }

    /**
     * Build a lightweight SVG placeholder coloured by the product's SKU.
     */
    private function placeholderArtwork(Product $product): string
    {
        $hue = crc32($product->sku) % 360;
        $initials = Str::of($product->name)
            ->explode(' ')
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->take(2)
            ->implode('');

        return <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" role="img" aria-label="{$product->name}">
              <rect width="400" height="400" fill="hsl({$hue}, 45%, 92%)"/>
              <rect x="130" y="110" width="140" height="160" rx="14" fill="hsl({$hue}, 40%, 80%)"/>
              <text x="200" y="212" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-size="64" font-weight="700" fill="hsl({$hue}, 55%, 32%)">{$initials}</text>
              <text x="200" y="330" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif" font-size="17" fill="hsl({$hue}, 30%, 42%)">{$product->name}</text>
            </svg>
            SVG;
    }

    /**
     * Seed a demo customer with a default shipping address and one paid order.
     */
    private function seedCustomerOrder(): void
    {
        $customer = User::query()->firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Demo Customer',
                'password' => 'password',
            ],
        );

        if ($customer->addresses()->doesntExist()) {
            $customer->addresses()->create([
                'type' => AddressType::Shipping,
                'recipient_name' => 'Demo Customer',
                'line1' => '742 Evergreen Terrace',
                'line2' => null,
                'city' => 'Springfield',
                'state' => 'OR',
                'postal_code' => '97403',
                'country' => 'US',
                'phone' => '555-0100',
                'is_default' => true,
            ]);
        }

        if ($customer->orders()->exists()) {
            return;
        }

        $products = Product::query()
            ->whereIn('sku', ['SKU-PHONE-0001', 'SKU-AUDIO-0001'])
            ->get()
            ->keyBy('sku');

        $phone = $products->get('SKU-PHONE-0001');
        $earbuds = $products->get('SKU-AUDIO-0001');

        if ($phone === null || $earbuds === null) {
            return;
        }

        $lines = [
            [$phone, 1],
            [$earbuds, 2],
        ];

        $order = Order::query()->create([
            'user_id' => $customer->getKey(),
            'status' => OrderStatus::Paid,
            'currency' => strtoupper(app(SettingsService::class)->get('store_currency')) ?: 'USD',
            'total' => number_format(
                collect($lines)->sum(fn (array $line): float => ((float) $line[0]->price) * $line[1]),
                2,
                '.',
                '',
            ),
        ]);

        foreach ($lines as [$product, $quantity]) {
            $order->items()->create([
                'product_id' => $product->getKey(),
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'line_total' => number_format(((float) $product->price) * $quantity, 2, '.', ''),
            ]);
        }

        $order->payments()->create([
            'provider' => 'demo',
            'provider_reference' => 'DEMO-PAYMENT-0001',
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
        ]);
    }
}
