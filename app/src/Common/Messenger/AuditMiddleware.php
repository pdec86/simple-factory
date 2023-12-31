<?php

declare(strict_types=1);

namespace App\Common\Messenger;

use App\Catalogue\Domain\Model\Messages\ProductOrdered;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpReceivedStamp;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AuditMiddleware implements MiddlewareInterface
{
    const LIST_AUDIT_CLASSES = [
        ProductOrdered::class,
    ];

    private string $messengerLogsPath;

    public function __construct(private SerializerInterface $serializer, string $messengerLogsPath)
    {
        $this->messengerLogsPath = $messengerLogsPath;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if (in_array(get_class($message), self::LIST_AUDIT_CLASSES)) {
            $received = $envelope->all(AmqpReceivedStamp::class);
            $allAmqpStamps = $envelope->all(AmqpStamp::class);
            $routingKeys = [];
            /** @var AmqpStamp $amqpStamps s*/
            foreach ($allAmqpStamps as $amqpStamps) {
                $routingKeys[] = $amqpStamps->getRoutingKey();
            }

            $serialized = $this->serializer->serialize([
                'messageClass' => get_class($message),
                'message' => $message,
                'routing_keys' => $routingKeys,
            ], 'json');

            $filename = empty($received) ? 'logs_i.log' : 'received_logs_i.log';
            $fileNumber = 1;
            $fileMaxSize = 100 * 1024 * 1024; // 100 MB
            
            while (
                file_exists($this->getProperFileName($filename, $fileNumber))
                && filesize($this->getProperFileName($filename, $fileNumber)) > $fileMaxSize
            ) {
                $fileNumber++;
            }
            
            file_put_contents($this->getProperFileName($filename, $fileNumber), $serialized . "\n", FILE_APPEND);
        }
        
        return $stack->next()->handle($envelope, $stack);
    }

    private function getProperFileName(string $filename, int $fileNumber): string
    {
        return str_replace('_i', '_' . $fileNumber, $this->messengerLogsPath . $filename);
    }
}
