<?php

declare(strict_types=1);

namespace App\Shared\Domain\Model;

trait WithEvents
{
    /** @var list<object> */
    private array $recordedEvents = [];

    protected function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * @return list<object>
     */
    public function events(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }
}
