<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Service;

use App\Catalogue\Domain\Model\Product;

class CreateProductService
{
    public function execute(string $name, ?string $description = null): Product
    {
        $product = Product::createWithBasicData($name, $description);

        return $product;
    }
}
