<?php

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a visitor can subscribe to the store journal', function () {
    $this->post(route('newsletter.subscribe'), [
        'newsletter_email' => 'Ada@Example.com',
    ])
        ->assertRedirect()
        ->assertSessionHas('newsletter_status');

    expect(NewsletterSubscriber::query()->count())->toBe(1)
        ->and(NewsletterSubscriber::query()->sole()->email)->toBe('ada@example.com');
});

test('the homepage confirms a successful subscription', function () {
    $this->followingRedirects()
        ->post(route('newsletter.subscribe'), [
            'newsletter_email' => 'grace@example.com',
        ])
        ->assertOk()
        ->assertSee('you are on the list', false);
});

test('an invalid newsletter address is rejected', function () {
    $this->post(route('newsletter.subscribe'), [
        'newsletter_email' => 'not-an-email',
    ])
        ->assertSessionHasErrors('newsletter_email');

    expect(NewsletterSubscriber::query()->count())->toBe(0);
});

test('a missing newsletter address is rejected', function () {
    $this->post(route('newsletter.subscribe'), [])
        ->assertSessionHasErrors('newsletter_email');

    expect(NewsletterSubscriber::query()->count())->toBe(0);
});

test('a duplicate newsletter address is rejected with a friendly message', function () {
    NewsletterSubscriber::factory()->create(['email' => 'ada@example.com']);

    $this->post(route('newsletter.subscribe'), [
        'newsletter_email' => 'ADA@example.com',
    ])
        ->assertSessionHasErrors('newsletter_email');

    expect(NewsletterSubscriber::query()->count())->toBe(1);
});

test('the newsletter form posts to the subscription route', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('action="'.route('newsletter.subscribe').'"', false)
        ->assertSee('name="newsletter_email"', false);
});
