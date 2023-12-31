<?php

declare(strict_types=1);

namespace App\Factory\Infrastructure\MessageHandler;

use App\Catalogue\Domain\Model\Exceptions\ProductNotFoundException;
use App\Catalogue\Domain\Model\Messages\ProductOrdered;
use App\Factory\Application\Service\FactoryManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ProductHandler
{
    public function __construct(
        private FactoryManager $factoryManager
    ) {
    }

    /**
     * @throws ProductNotFoundException
     */
    #[AsMessageHandler(fromTransport: 'manufacturing', handles: ProductOrdered::class)]
    public function handleProductOrdered(ProductOrdered $message): void
    {
        $this->factoryManager->startManufacturingSpecificProductModel(
            $message->getSpecificProductId(),
            $message->getQuantity()
        );
    }
}
