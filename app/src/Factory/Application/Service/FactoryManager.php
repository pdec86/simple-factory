<?php

declare(strict_types=1);

namespace App\Factory\Application\Service;

use App\Catalogue\Domain\Model\Exceptions\ProductNotFoundException;
use App\Catalogue\Domain\Model\Product;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Factory\Domain\Model\ManufactureProduct;
use Doctrine\Persistence\ManagerRegistry;

class FactoryManager
{
    public function __construct(
        private ManagerRegistry $registry
    ) {   
    }

    /**
     * @throws ProductNotFoundException
     */
    public function startManufacturingSpecificProductModel(
        SpecificProductId $specificProductId,
        int $quantity,
        Dimensions $dimensions,
    ): void {
        /** @var ProductRepository $repository */
        $repository = $this->registry->getManagerForClass(Product::class)
            ->getRepository(Product::class);

        $product = $repository->fetchSpecificProductModelId($specificProductId);

        if (null === $product || !$product->checkVariantExists($specificProductId)) {
            throw new ProductNotFoundException('Specific product model not found.');
        }

        $manufactureProduct = new ManufactureProduct($specificProductId, $quantity, $dimensions);
        $entityManager = $this->registry->getManagerForClass(ManufactureProduct::class);
        $entityManager->persist($manufactureProduct);
        $entityManager->flush();
    }
}
