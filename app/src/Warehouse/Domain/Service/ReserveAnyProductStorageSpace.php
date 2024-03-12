<?php

declare(strict_types=1);

namespace App\Warehouse\Domain\Service;

use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Warehouse\Domain\Model\ProductStorage;
use App\Warehouse\Domain\Model\StorageSpace;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

class ReserveAnyProductStorageSpace
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
        SpecificProductId $specificProductModelId,
        int $quantity,
        Dimensions $specificProductModeDimensions,
    ): int {
        $entityManager = $this->getEntityManager();
        $quantityLeft = $quantity;

        foreach ($storageSpaces as $storageSpace) {
            foreach ($storageSpace->getProductStorageSpaces() as $productStorageSpace) {
                if ($productStorageSpace->isPossibleToReserveForSpecificProduct($specificProductModelId, $specificProductModeDimensions)) {
                    $entityManager->beginTransaction();
                    $entityManager->refresh($productStorageSpace, LockMode::PESSIMISTIC_WRITE);
    
                    $productStorageSpace->claimForSpecificProduct($specificProductModelId, $specificProductModeDimensions);

                    $quantityLeft = $this->reserveProductStorageSpace($storageSpace, $productStorageSpace, $specificProductModelId, $quantityLeft);
                    
                    $entityManager->persist($productStorageSpace);
                    $entityManager->commit();
                    $entityManager->flush();
    
                    if (0 >= $quantityLeft) {
                        break;
                    }
                }
            }
        }

        return $quantityLeft;
    }

    public function reserveProductStorageSpace(
        StorageSpace $storageSpace,
        ProductStorage $productStorageSpace,
        SpecificProductId $specificProductModelId,
        int $quantityLeft,
    ): int {
        $storageQuantityLeft = $storageSpace->getProductStorageSpaceQuantityLeft($specificProductModelId, $productStorageSpace);

        $occupyStorageQuantity = min($quantityLeft, $storageQuantityLeft);
        $quantityLeft = $quantityLeft - $occupyStorageQuantity;

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
