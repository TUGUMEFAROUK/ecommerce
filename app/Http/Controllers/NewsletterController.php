<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscriptionRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    /**
     * Subscribe an address to the store journal.
     *
     * The address is stored so the store keeps a real audience list; the
     * visitor gets an explicit confirmation, and a duplicate address is
     * reported back to the form instead of failing silently.
     */
    public function store(NewsletterSubscriptionRequest $request): RedirectResponse
    {
        NewsletterSubscriber::query()->create([
            'email' => $request->string('newsletter_email')->value(),
        ]);

        return back()
            ->with('newsletter_status', 'Thank you — you are on the list. The next journal will land in your inbox.');
    }
}
