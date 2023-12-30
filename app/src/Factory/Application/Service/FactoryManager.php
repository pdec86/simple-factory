<?php

declare(strict_types=1);

namespace App\Factory\Application\Service;

use App\Catalogue\Domain\Model\Exceptions\ProductNotFoundException;
use App\Catalogue\Domain\Model\SpecificProductModel;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Catalogue\Infrastructure\Repository\SpecificProductModelRepository;
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
    public function startManufacturingSpecificProductModel(SpecificProductId $specificProductId, int $quantity): void
    {
        /** @var SpecificProductModelRepository $repository */
        $repository = $this->registry->getManagerForClass(SpecificProductModel::class)
            ->getRepository(SpecificProductModel::class);

        $sepcificProductModel = $repository->fetchById($specificProductId);

        if (null === $sepcificProductModel) {
            throw new ProductNotFoundException('Specific product model not found.');
        }

        $manufactureProduct = new ManufactureProduct($specificProductId, $quantity);
        $entityManager = $this->registry->getManagerForClass(ManufactureProduct::class);
        $entityManager->persist($manufactureProduct);
        $entityManager->flush();
    }
}
