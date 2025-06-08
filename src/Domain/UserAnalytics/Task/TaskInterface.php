<?php

declare(strict_types=1);

namespace App\Domain\UserAnalytics\Task;

interface TaskInterface
{
    public function run(array $data = []);
}
