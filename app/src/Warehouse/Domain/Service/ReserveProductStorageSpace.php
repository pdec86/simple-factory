<?php

declare(strict_types=1);

namespace App\Warehouse\Domain\Service;

use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaNotFoundException;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaOccupiedException;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaTooSmallException;
use App\Warehouse\Domain\Model\ProductStorage;
use App\Warehouse\Domain\Model\StorageSpace;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

class ReserveProductStorageSpace
{
    private ?EntityManager $entityManager = null;

    public function __construct(
        private ManagerRegistry $registry,
    ) {   
    }

    /**
     * @param StorageSpace[] $storageSpaces
     * 
     * @return int Quantity of products for which storage space was not reserved.
     */
    public function execute(
        array $storageSpaces,
        SpecificProductId $specificProductId,
        int $quantity,
        Dimensions $dimensions,
        string $areaName,
        string $shelf,
    ): int {
        foreach ($storageSpaces as $storageSpace) {
            $productStorageSpace = $storageSpace->findProductStorageSpace($areaName, $shelf);

            if (null !== $productStorageSpace) {
                $entityManager = $this->getEntityManager();
                $entityManager->beginTransaction();
                $entityManager->refresh($productStorageSpace, LockMode::PESSIMISTIC_WRITE);

                $this->claimProductStorageSpace(
                    $productStorageSpace,
                    $specificProductId,
                    $dimensions,
                );

                $quantityLeft = $this->reserveProductStorageSpace(
                    $storageSpace,
                    $productStorageSpace,
                    $specificProductId,
                    $quantity
                );

                $entityManager->persist($productStorageSpace);
                $entityManager->commit();
                $entityManager->flush();

                return $quantityLeft;
            }
        }

        throw new StorageAreaNotFoundException();
    }

    public function claimProductStorageSpace(
        ProductStorage $productStorageSpace,
        SpecificProductId $specificProductModelId,
        Dimensions $specificProductModeDimensions,
    ): void {
        if (null !== $productStorageSpace->getSpecificProductModelId()
            && !$specificProductModelId->equals($productStorageSpace->getSpecificProductModelId())) {
            throw new StorageAreaOccupiedException('Area and shelf already occupied by other product.');
        }

        if (!$productStorageSpace->isPossibleToReserveForSpecificProduct($specificProductModelId, $specificProductModeDimensions)) {
            throw new StorageAreaTooSmallException();
        }

        $productStorageSpace->claimForSpecificProduct($specificProductModelId, $specificProductModeDimensions);
    }

    public function reserveProductStorageSpace(
        StorageSpace $storageSpace,
        ProductStorage $productStorageSpace,
        SpecificProductId $specificProductModelId,
        int $quantity,
    ): int {
        $storageQuantityLeft = $storageSpace->getProductStorageSpaceQuantityLeft($specificProductModelId, $productStorageSpace);
            
        $occupyStorageQuantity = min($quantity, $storageQuantityLeft);
        $quantityLeft = $quantity - $occupyStorageQuantity;

        $storageSpace->reserveProductStorageSpaceQuantity($specificProductModelId, $productStorageSpace, $occupyStorageQuantity);

        return $quantityLeft;
    }

    private function getEntityManager(): EntityManager
    {
        if (null === $this->entityManager) {
            $this->entityManager = $this->registry->getManagerForClass(StorageSpace::class);
        }

        return $this->entityManager;
    }
}
