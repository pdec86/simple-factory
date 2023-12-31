<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Model\Messages;

interface AmqpMessageInterface
{
    public function getRoutingKey(): string;
}
