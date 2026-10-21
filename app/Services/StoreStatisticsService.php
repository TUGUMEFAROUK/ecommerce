<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Order;
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
