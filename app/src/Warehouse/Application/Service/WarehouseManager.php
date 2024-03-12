<?php

declare(strict_types=1);

namespace App\Warehouse\Application\Service;

use App\Catalogue\Domain\Model\Exceptions\ProductNotFoundException;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Warehouse\Domain\Model\StorageSpace;
use App\Warehouse\Domain\Service\ReserveAnyProductStorageSpace;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class WarehouseManager
{
    public function __construct(
        private ManagerRegistry $registry,
        private ReserveAnyProductStorageSpace $reserveAnyProductStorageSpaceService,
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
    public function reserveAnyProductStorageSpace(
        SpecificProductId $specificProductId,
        int $quantity,
        Dimensions $dimensions,
    ): int {
        /** @var ObjectRepository $repository */
        $repository = $this->registry->getManagerForClass(StorageSpace::class)->getRepository(StorageSpace::class);
        /** @var StorageSpace[] $storageSpaces */
        $storageSpaces = $repository->findAll();

        if (empty($storageSpaces)) {
            throw new \DomainException('Storage space not found.');
        }

        return $this->reserveAnyProductStorageSpaceService->execute(
            $storageSpaces,
            $specificProductId,
            $quantity,
            $dimensions
        );
    }
}
