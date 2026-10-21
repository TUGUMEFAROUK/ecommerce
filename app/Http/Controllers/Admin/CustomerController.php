<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StoreStatisticsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * List customer accounts (role: customer) with order counts.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', UserRole::Customer)
            ->withCount('orders')
            ->latest();

        $search = trim((string) $request->query('search'));

        if ($search !== '') {
            $like = '%'.$search.'%';

            $query->where(fn (Builder $builder) => $builder
                ->where('name', 'like', $like)
                ->orWhere('email', 'like', $like));
        }

        return view('admin.customers.index', [
            'customers' => $query->paginate(10)->withQueryString(),
        ]);
    }

    /**
     * Show one customer with their order history.
     *
     * Only accounts with the customer role are reachable here; anything
     * else is reported as missing so admin accounts cannot be enumerated
     * through this module.
     */
    public function show(
        User $customer,
        StoreStatisticsService $statistics,
    ): View {
        abort_unless($customer->role === UserRole::Customer, 404);

        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $customer->orders()->latest()->limit(10)->get(),
            'orderCount' => $customer->orders()->count(),
            'totalSpent' => $statistics->totalSpent($customer),
        ]);
    }
}
