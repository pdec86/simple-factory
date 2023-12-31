<?php

declare(strict_types=1);

namespace App\Common\Messenger;

use App\Catalogue\Domain\Model\Messages\AmqpMessageInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class AmqpMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof AmqpMessageInterface) {
            $envelope = $envelope->with(new AmqpStamp($message->getRoutingKey()));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
