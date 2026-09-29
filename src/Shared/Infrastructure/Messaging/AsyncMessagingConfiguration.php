<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Messaging;

use Ecotone\Messaging\Attribute\ServiceContext;
use Ecotone\Messaging\Channel\MessageChannelBuilder;
use Ecotone\SymfonyBundle\Messenger\SymfonyMessengerMessageChannelBuilder;

final class AsyncMessagingConfiguration
{
    #[ServiceContext]
    public function enrichmentChannel(): MessageChannelBuilder
    {
        return SymfonyMessengerMessageChannelBuilder::create('enrichment');
    }
}
