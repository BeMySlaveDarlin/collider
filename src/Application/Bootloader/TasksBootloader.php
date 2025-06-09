<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Domain\UserAnalytics\Task\BatchCreateEventsTask;
use App\Domain\UserAnalytics\Task\DeleteOldEventsTask;
use Spiral\Boot\Bootloader\Bootloader;

final class TasksBootloader extends Bootloader
{
    protected const array SINGLETONS = [
        DeleteOldEventsTask::class => DeleteOldEventsTask::class,
        BatchCreateEventsTask::class => BatchCreateEventsTask::class,
    ];
}
