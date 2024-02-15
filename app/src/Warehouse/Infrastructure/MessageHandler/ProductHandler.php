<?php

declare(strict_types=1);

namespace App\Warehouse\Infrastructure\MessageHandler;

use App\Catalogue\Domain\Model\Messages\ProductOrdered;
use App\Warehouse\Application\Service\WarehouseManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ProductHandler
{
    public function __construct(
        private WarehouseManager $warehouseManager
    ) {
        
    }

    #[AsMessageHandler(fromTransport: 'manufacturing', handles: ProductOrdered::class)]
    public function handleProductOrdered(ProductOrdered $message): void
    {
        $this->warehouseManager->reserveAnyProductStorageSpace(
            $message->getSpecificProductId(),
            $message->getQuantity(),
            $message->getDimensions(),
        );
    }
}
