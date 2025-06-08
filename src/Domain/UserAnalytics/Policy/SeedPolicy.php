<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Policy;

final readonly class SeedPolicy
{
    private const int USERS_COUNT = 100;
    private const int EVENTS_COUNT = 10_000_000;
    private const int BATCH_SIZE = 10_000;

    private const REFERRERS = [
        'https://google.com',
        'https://facebook.com',
        'https://youtube.com',
        'https://twitter.com',
        'https://instagram.com',
        'https://tiktok.com',
        'https://linkedin.com',
        'https://reddit.com',
        'https://pinterest.com',
        'https://yahoo.com',
        'https://bing.com',
        'https://amazon.com',
        'https://ebay.com',
        'https://wikipedia.org',
        'https://medium.com',
        'https://quora.com',
        'https://stackoverflow.com',
        'https://github.com',
        'https://netflix.com',
        'https://news.ycombinator.com',
    ];

    private const array EVENT_TYPES = [
        'user.registered' => ['page' => '/registration'],
        'user.login' => ['page' => '/login'],
        'user.logout' => ['page' => '/logout'],
        'user.updated' => ['page' => '/profile/edit'],

        'order.created' => ['page' => '/order/create'],
        'order.paid' => ['page' => '/order/confirm'],
        'order.shipped' => ['page' => '/order/shipped'],
        'order.delivered' => ['page' => '/order/tracking'],

        'payment.processed' => ['page' => '/payment/complete'],
        'payment.failed' => ['page' => '/payment/failed'],
        'payment.refunded' => ['page' => '/payment/refund'],

        'product.viewed' => ['page' => '/product/view'],
        'product.added_to_cart' => ['page' => '/cart/add'],
        'product.removed_from_cart' => ['page' => '/cart/remove'],

        'email.sent' => ['page' => '/emails/sent'],
        'email.opened' => ['page' => '/emails/opened'],
        'email.clicked' => ['page' => '/emails/click'],

        'notification.sent' => ['page' => '/notifications/sent'],
        'notification.read' => ['page' => '/notifications/read'],

        'api.request' => ['page' => '/api/request'],
        'api.response' => ['page' => '/api/response'],
        'api.error' => ['page' => '/api/error'],
    ];

    public function getUsersCount(): int
    {
        return self::USERS_COUNT;
    }

    public function getEventsCount(): int
    {
        return self::EVENTS_COUNT;
    }

    public function getBatchSize(): int
    {
        return self::BATCH_SIZE;
    }

    public function getEventTypes(): array
    {
        return self::EVENT_TYPES;
    }

    public function getReferrers(): array
    {
        return self::REFERRERS;
    }
}
