<?php

declare(strict_types=1);

namespace App\Warehouse\Infrastructure\MessageHandler;

use App\Catalogue\Domain\Model\Messages\ProductOrdered;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ProductHandler
{
    public function __construct(
    ) {
        
    }

    #[AsMessageHandler(fromTransport: 'manufacturing', handles: ProductOrdered::class)]
    public function handleProductOrdered(ProductOrdered $message): void
    {
        
    }
}
