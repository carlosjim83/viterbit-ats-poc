<?php

declare(strict_types=1);

namespace App\Application\Application;

interface EventBus
{
    public function publish(object $event): void;
}
