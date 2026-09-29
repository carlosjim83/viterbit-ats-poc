<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Messaging;

use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Channel\SimpleMessageChannelBuilder;

final class MessagingConfiguration
{
    #[ServiceContext]
    public function enrichmentChannel(): SimpleMessageChannelBuilder
    {
        return SimpleMessageChannelBuilder::createQueueChannel('enrichment');
    }
}
