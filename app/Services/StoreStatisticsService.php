<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StoreStatisticsService
{
    /**
     * Statuses whose totals count towards recognized sales. Pending,
     * cancelled, and refunded orders never contribute revenue.
     *
     * @var list<OrderStatus>
     */
    private const SALES_STATUSES = [
        OrderStatus::Paid,
        OrderStatus::Processing,
        OrderStatus::Shipped,
        OrderStatus::Delivered,
    ];

    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Dashboard figures computed from real records only.
     *
     * @return array{
     *     total_orders: int,
     *     total_sales: float,
     *     month_sales: float,
     *     customers: int,
     *     products: int,
     *     low_stock_count: int,
     *     orders_by_status: array<string, int>,
     *     recent_orders: Collection<int, Order>,
     *     low_stock_products: Collection<int, Product>
     * }
     */
    public function summary(): array
    {
        $threshold = $this->settings->lowStockThreshold();

        $countsByStatus = Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $ordersByStatus = [];

        foreach (OrderStatus::cases() as $status) {
            $ordersByStatus[$status->value] = (int) ($countsByStatus[$status->value] ?? 0);
        }

        return [
            'total_orders' => Order::query()->count(),
            'total_sales' => $this->salesTotal(
                Order::query()->whereIn('status', self::SALES_STATUSES)
            ),
            'month_sales' => $this->salesTotal(
                Order::query()
                    ->whereIn('status', self::SALES_STATUSES)
                    ->where('created_at', '>=', now()->startOfMonth())
            ),
            'customers' => User::query()->where('role', UserRole::Customer)->count(),
            'products' => Product::query()->count(),
            'low_stock_count' => $this->lowStockQuery($threshold)->count(),
            'orders_by_status' => $ordersByStatus,
            'recent_orders' => Order::query()->with('user')->latest()->limit(10)->get(),
            'low_stock_products' => $this->lowStockQuery($threshold)
                ->orderBy('stock_quantity')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Recognized sales total for one customer's orders.
     */
    public function totalSpent(User $customer): float
    {
        return round(
            (float) $customer->orders()->whereIn('status', self::SALES_STATUSES)->sum('total'),
            2,
        );
    }

    /**
     * Daily recognized revenue for the last number of days.
     *
     * The series is built from real orders and always covers the whole
     * window, so days without sales appear as zero instead of vanishing
     * and making the chart misleading.
     *
     * @return list<array{date: string, label: string, total: float, orders: int}>
     */
    public function salesSeries(int $days = 30): array
    {
        $days = max(1, min($days, 365));

        $totals = [];
        $orders = [];

        Order::query()
            ->whereIn('status', self::SALES_STATUSES)
            ->where('created_at', '>=', now()->startOfDay()->subDays($days - 1))
            ->get(['created_at', 'total'])
            ->each(function (Order $order) use (&$totals, &$orders): void {
                $key = $order->created_at->format('Y-m-d');

                $totals[$key] = ($totals[$key] ?? 0) + (float) $order->total;
                $orders[$key] = ($orders[$key] ?? 0) + 1;
            });

        $series = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = now()->startOfDay()->subDays($offset);
            $key = $date->format('Y-m-d');

            $series[] = [
                'date' => $key,
                'label' => $date->format('M j'),
                'total' => round($totals[$key] ?? 0, 2),
                'orders' => $orders[$key] ?? 0,
            ];
        }

        return $series;
    }

    /**
     * The best selling products, measured by units on recognized sales.
     *
     * Product names are read from the order item snapshot so a product that
     * was renamed or removed still shows what was actually sold.
     *
     * @return list<array{product: ?Product, name: string, units: int, revenue: float}>
     */
    public function topSellingProducts(int $limit = 5): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', self::SALES_STATUSES)
            ->whereNotNull('order_items.product_id')
            ->groupBy('order_items.product_id')
            ->selectRaw(
                'order_items.product_id,'
                .' MIN(order_items.product_name) as product_name,'
                .' SUM(order_items.quantity) as units,'
                .' SUM(order_items.line_total) as revenue'
            )
            ->orderByDesc('units')
            ->limit(max(1, $limit))
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $products = Product::query()
            ->whereIn('id', $rows->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return $rows->map(fn ($row) => [
            'product' => $products->get($row->product_id),
            'name' => (string) $row->product_name,
            'units' => (int) $row->units,
            'revenue' => round((float) $row->revenue, 2),
        ])->all();
    }

    /**
     * Active products at or below the configured low-stock threshold.
     *
     * @return Builder<Product>
     */
    private function lowStockQuery(int $threshold): Builder
    {
        return Product::query()
            ->where('status', ProductStatus::Active)
            ->where('stock_quantity', '<=', $threshold);
    }

    /**
     * Sum the totals of an order query, rounded for display.
     *
     * @param  Builder<Order>  $query
     */
    private function salesTotal(Builder $query): float
    {
        return round((float) $query->sum('total'), 2);
    }
}
