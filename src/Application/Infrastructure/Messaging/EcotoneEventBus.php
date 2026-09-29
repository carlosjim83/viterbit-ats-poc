<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Messaging;

use App\Application\Application\EventBus;
use Ecotone\Modelling\EventBus as EcotoneEventBusInterface;

final readonly class EcotoneEventBus implements EventBus
{
    public function __construct(
        private EcotoneEventBusInterface $eventBus,
    ) {
    }

    public function publish(object $event): void
    {
        $this->eventBus->publish($event);
    }
}
