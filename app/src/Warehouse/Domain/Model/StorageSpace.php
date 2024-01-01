<?php

declare(strict_types=1);

namespace App\Warehouse\Domain\Model;

use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaNotFoundException;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaOccupiedException;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaTooSmallException;
use App\Warehouse\Domain\Model\ValueObjects\StorageSpaceId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: 't_warehouseStorageSpace')]
#[ORM\Entity()]
#[ORM\HasLifecycleCallbacks]
class StorageSpace
{
    use ClockAwareTrait;

    #[Embedded(class: StorageSpaceId::class, columnPrefix: "storageSpace")]
    private ?StorageSpaceId $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false, unique: true)]
    private string $name;

    /**
     * @var Collection<ProductStorage>|ProductStorage[]
     */
    #[ORM\OneToMany(targetEntity: ProductStorage::class, mappedBy: 'storageSpace', cascade: ['persist', 'merge'])]
    private Collection $productsStorageSpaces;

    #[ORM\Column(name: 'createdAt', type: 'datetime_immutable', updatable: false)]
    #[Ignore]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updatedAt', type: 'datetime_immutable', nullable: true)]
    #[Ignore]
    private ?\DateTimeImmutable $updatedAt = null;

    private function __construct()
    {
        $this->productsStorageSpaces = new ArrayCollection();
    }

    public static function createWithBasicData(string $name): self
    {
        $product = new self();
        $product->changeName($name);

        return $product;
    }

    public function getId(): ?StorageSpaceId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function changeName(string $name): void
    {
        $name = trim($name);

        if (0 === mb_strlen($name, 'UTF-8')) {
            throw new \DomainException('Name must not be blank.');
        }

        $this->name = $name;
    }

    public function addProductStorage(
        string $areaName,
        string $shelf,
        Dimensions $dimensions,
    ): void {
        foreach ($this->productsStorageSpaces as $productStorageSpace) {
            if (
                $areaName === $productStorageSpace->getAreaName()
                && $shelf === $productStorageSpace->getShelf()
            ) {
                throw new StorageAreaOccupiedException('Area and shelf already occupied.');
            }
        }

        $this->productsStorageSpaces->add(ProductStorage::createWithBasicData($this, $areaName, $shelf, $dimensions));
    }

    public function reserveAnyroductStorageSpace(
        SpecificProductId $specificProductModelId,
        Dimensions $specificProductModeDimensions,
    ): array {
        foreach ($this->productsStorageSpaces as $productStorageSpace) {
            if ($productStorageSpace->isPossibleToReserveForSpecificProduct($specificProductModelId, $specificProductModeDimensions)) {
                $productStorageSpace->reserveForSpecificProduct($specificProductModelId, $specificProductModeDimensions);
                return [$productStorageSpace->getAreaName(), $productStorageSpace->getShelf()];
            }
        }

        return [null, null];
    }

    public function reserveProductStorageSpace(
        SpecificProductId $specificProductModelId,
        Dimensions $specificProductModeDimensions,
        string $areaName,
        string $shelf,
    ): void {
        foreach ($this->productsStorageSpaces as $productStorageSpace) {
            if (
                $areaName === $productStorageSpace->getAreaName()
                && $shelf === $productStorageSpace->getShelf()
            ) {
                if (!$specificProductModelId->equals($productStorageSpace->getSpecificProductModelId())) {
                    throw new StorageAreaOccupiedException('Area and shelf already occupied by other product.');
                }

                if (!$productStorageSpace->isPossibleToReserveForSpecificProduct($specificProductModelId, $specificProductModeDimensions)) {
                    throw new StorageAreaTooSmallException();
                }

                $productStorageSpace->reserveForSpecificProduct($specificProductModelId, $specificProductModeDimensions);
                return;
            }
        }

        throw new StorageAreaNotFoundException();
    }

    public function getProductStorageSpaceQuantityLeft(
        SpecificProductId $specificProductModelId,
        string $areaName,
        string $shelf,
    ): int {
        foreach ($this->productsStorageSpaces as $productStorageSpace) {
            if (
                $areaName === $productStorageSpace->getAreaName()
                && $shelf === $productStorageSpace->getShelf()
            ) {
                if (!$specificProductModelId->equals($productStorageSpace->getSpecificProductModelId())) {
                    throw new StorageAreaOccupiedException('Area and shelf already occupied by other product.');
                }
                
                return $productStorageSpace->getQuantityLeft();
            }
        }

        throw new StorageAreaNotFoundException();
    }

    public function reserveProductStorageSpaceQuantity(
        SpecificProductId $specificProductModelId,
        string $areaName,
        string $shelf,
        int $quantity,
    ): void {
        foreach ($this->productsStorageSpaces as $productStorageSpace) {
            if (
                $areaName === $productStorageSpace->getAreaName()
                && $shelf === $productStorageSpace->getShelf()
            ) {
                if (!$specificProductModelId->equals($productStorageSpace->getSpecificProductModelId())) {
                    throw new StorageAreaOccupiedException('Area and shelf already occupied by other product.');
                }
                
                $productStorageSpace->addQuantity($quantity);
                return;
            }
        }

        throw new StorageAreaNotFoundException();
    }

    #[ORM\PrePersist]
    public function doOnPrePersist()
    {
        $this->createdAt = $this->now();
    }

    #[ORM\PreUpdate]
    public function doOnPreUpdate()
    {
        $this->updatedAt = $this->now();
    }
}
