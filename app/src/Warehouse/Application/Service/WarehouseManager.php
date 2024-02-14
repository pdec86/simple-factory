<?php

declare(strict_types=1);

namespace App\Warehouse\Application\Service;

use App\Catalogue\Application\Service\ProductManager;
use App\Catalogue\Domain\Model\Exceptions\ProductNotFoundException;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Warehouse\Domain\Model\StorageSpace;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class WarehouseManager
{
    public function __construct(
        private ManagerRegistry $registry,
        private ProductManager $productManager,
    ) {   
    }

    /**
     * @throws ProductNotFoundException
     */
    public function createStorageSpace(string $name): void
    {
        $entityManager = $this->registry->getManagerForClass(StorageSpace::class);

        $storageSpace = StorageSpace::createWithBasicData($name);

        $entityManager->persist($storageSpace);
        $entityManager->flush();
    }

    /**
     * @throws ProductNotFoundException
     */
    public function createProductStorageSpace(string $name, string $areaName, string $shelf, Dimensions $dimensions): void
    {
        /** @var ObjectRepository $repository */
        $repository = $this->registry->getManagerForClass(StorageSpace::class)->getRepository(StorageSpace::class);
        /** @var StorageSpace $storageSpace */
        $storageSpace = $repository->findOneBy(['name' => $name]);

        if (null === $storageSpace) {
            throw new \DomainException('Storage space not found.');
        }

        $storageSpace->addProductStorage($areaName, $shelf, $dimensions);

        $entityManager = $this->registry->getManagerForClass(StorageSpace::class);
        $entityManager->persist($storageSpace);
        $entityManager->flush();
    }

    /**
     * @return int Quantity not reserved anywhere in the warehouse.
     * 
     * @throws ProductNotFoundException
     */
    public function reserveAnyProductStorageSpace(SpecificProductId $specificProductId, int $quantity): int
    {
        /** @var ObjectRepository $repository */
        $repository = $this->registry->getManagerForClass(StorageSpace::class)->getRepository(StorageSpace::class);
        /** @var StorageSpace[] $storageSpaces */
        $storageSpaces = $repository->findAll();

        if (empty($storageSpaces)) {
            throw new \DomainException('Storage space not found.');
        }

        $product = $this->productManager->getProductBySpecificProductModelId($specificProductId);
        if (null === $product) {
            throw new \DomainException('Product not found.');
        }

        $entityManager = $this->registry->getManagerForClass(StorageSpace::class);
        $quantityLeft = $quantity;

        foreach ($storageSpaces as $storageSpace) {
            list($areaName, $shelf) = $storageSpace->reserveAnyProductStorageSpace($specificProductId, $product->getVariantDimensions($specificProductId));
            
            if (null !== $areaName && null !== $shelf) {
                $storageQuantityLeft = $storageSpace->getProductStorageSpaceQuantityLeft($specificProductId, $areaName, $shelf);
                
                $occupyStorageQuantity = min($quantityLeft, $storageQuantityLeft);
                $quantityLeft = $quantityLeft - $occupyStorageQuantity;
                
                $storageSpace->reserveProductStorageSpaceQuantity($specificProductId, $areaName, $shelf, $occupyStorageQuantity);
                
                $entityManager->persist($storageSpace);
            }

            if (0 >= $quantityLeft) {
                break;
            }
        }

        $entityManager->flush();

        return $quantityLeft;
    }
}
