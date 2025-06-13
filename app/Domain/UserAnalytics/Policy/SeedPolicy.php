<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Policy;

class SeedPolicy
{
    private const int USERS_COUNT = 1_000;
    private const int EVENTS_COUNT = 10_000_000;
    private const int BATCH_SIZE = 10_000;

    private const array REFERRERS = [
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

        'user.password_reset_requested' => ['page' => '/password/reset'],
        'user.password_changed' => ['page' => '/password/change'],
        'user.two_factor_enabled' => ['page' => '/security/2fa'],
        'user.two_factor_disabled' => ['page' => '/security/2fa/disable'],
        'user.deleted' => ['page' => '/account/delete'],
        'user.suspended' => ['page' => '/account/suspend'],
        'user.reactivated' => ['page' => '/account/reactivate'],
        'user.subscription_started' => ['page' => '/subscription/start'],
        'user.subscription_cancelled' => ['page' => '/subscription/cancel'],
        'user.subscription_renewed' => ['page' => '/subscription/renew'],
        'user.invited' => ['page' => '/invite/send'],
        'user.invite_accepted' => ['page' => '/invite/accept'],
        'user.feedback_submitted' => ['page' => '/feedback'],
        'user.avatar_uploaded' => ['page' => '/profile/avatar'],
        'user.preferences_updated' => ['page' => '/profile/preferences'],
        'user.email_verified' => ['page' => '/email/verify'],
        'user.login_failed' => ['page' => '/login/failed'],
        'user.profile_viewed' => ['page' => '/profile/view'],
        'user.notification_preferences_updated' => ['page' => '/profile/notifications'],
        'user.newsletter_subscribed' => ['page' => '/newsletter/subscribe'],

        'order.cancelled' => ['page' => '/order/cancel'],
        'order.return_requested' => ['page' => '/order/return'],
        'order.return_approved' => ['page' => '/order/return/approved'],
        'order.return_rejected' => ['page' => '/order/return/rejected'],
        'order.review_submitted' => ['page' => '/order/review'],
        'order.invoice_generated' => ['page' => '/order/invoice'],

        'payment.pending' => ['page' => '/payment/pending'],
        'payment.disputed' => ['page' => '/payment/dispute'],
        'payment.settled' => ['page' => '/payment/settled'],

        'cart.viewed' => ['page' => '/cart/view'],
        'cart.updated' => ['page' => '/cart/update'],
        'cart.cleared' => ['page' => '/cart/clear'],
        'checkout.started' => ['page' => '/checkout/start'],
        'checkout.completed' => ['page' => '/checkout/complete'],

        'product.review_submitted' => ['page' => '/product/review'],
        'product.wishlisted' => ['page' => '/wishlist/add'],
        'product.unwishlisted' => ['page' => '/wishlist/remove'],
        'product.compared' => ['page' => '/product/compare'],
        'product.shared' => ['page' => '/product/share'],
        'product.restock_requested' => ['page' => '/product/restock'],
        'product.stock_low' => ['page' => '/product/stock'],

        'email.bounced' => ['page' => '/emails/bounced'],
        'email.unsubscribed' => ['page' => '/emails/unsubscribe'],

        'notification.dismissed' => ['page' => '/notifications/dismiss'],
        'notification.failed' => ['page' => '/notifications/failure'],

        'session.started' => ['page' => '/session/start'],
        'session.expired' => ['page' => '/session/expired'],
        'session.terminated' => ['page' => '/session/end'],

        'admin.login' => ['page' => '/admin/login'],
        'admin.logout' => ['page' => '/admin/logout'],
        'admin.updated_user' => ['page' => '/admin/user/edit'],
        'admin.deleted_user' => ['page' => '/admin/user/delete'],
        'admin.generated_report' => ['page' => '/admin/reports'],
        'admin.settings_updated' => ['page' => '/admin/settings'],

        'file.uploaded' => ['page' => '/files/upload'],
        'file.deleted' => ['page' => '/files/delete'],
        'file.downloaded' => ['page' => '/files/download'],
        'file.previewed' => ['page' => '/files/preview'],

        'support.ticket_created' => ['page' => '/support/create'],
        'support.ticket_closed' => ['page' => '/support/close'],
        'support.ticket_reopened' => ['page' => '/support/reopen'],
        'support.message_sent' => ['page' => '/support/message'],
        'support.rating_submitted' => ['page' => '/support/rating'],

        'search.performed' => ['page' => '/search'],
        'search.filtered' => ['page' => '/search/filter'],
        'search.sorted' => ['page' => '/search/sort'],

        'settings.updated' => ['page' => '/settings'],
        'language.changed' => ['page' => '/settings/language'],
        'timezone.changed' => ['page' => '/settings/timezone'],

        'api.token_generated' => ['page' => '/api/token'],
        'api.token_revoked' => ['page' => '/api/token/revoke'],
        'api.rate_limited' => ['page' => '/api/rate-limit'],

        'cron.job_started' => ['page' => '/cron/start'],
        'cron.job_finished' => ['page' => '/cron/end'],
        'cron.job_failed' => ['page' => '/cron/failure'],

        'webhook.received' => ['page' => '/webhooks/incoming'],
        'webhook.verified' => ['page' => '/webhooks/verified'],
        'webhook.failed' => ['page' => '/webhooks/failure'],
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
