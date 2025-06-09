<?php

declare(strict_types=1);

namespace App\Application\Bootloader\Domain;

use App\Domain\UserAnalytics\Repository\CachedEventRepository;
use App\Domain\UserAnalytics\Repository\CachedEventTypeRepository;
use App\Domain\UserAnalytics\Repository\CachedUserRepository;
use App\Domain\UserAnalytics\Repository\EventRepository;
use App\Domain\UserAnalytics\Repository\EventTypeRepository;
use App\Domain\UserAnalytics\Repository\UserRepository;
use Spiral\Boot\Bootloader\Bootloader;

final class UserAnalyticsBootloader extends Bootloader
{
    protected const array SINGLETONS = [
        CachedUserRepository::class => CachedUserRepository::class,
        CachedEventTypeRepository::class => CachedEventTypeRepository::class,
        CachedEventRepository::class => CachedEventRepository::class,
        UserRepository::class => UserRepository::class,
        EventTypeRepository::class => EventTypeRepository::class,
        EventRepository::class => EventRepository::class,
    ];
}
