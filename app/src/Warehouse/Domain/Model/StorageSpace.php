<?php

declare(strict_types=1);

namespace App\Warehouse\Domain\Model;

use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaOccupiedException;
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

    /**
     * @return ProductStorage[] List of product storage spaces.
     */
    public function getProductStorageSpaces()
    {
        return $this->productsStorageSpaces;
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

    public function findProductStorageSpace(string $areaName, string $shelf): ?ProductStorage
    {
        foreach ($this->productsStorageSpaces as $productStorageSpace) {
            if (
                $areaName === $productStorageSpace->getAreaName()
                && $shelf === $productStorageSpace->getShelf()
            ) {
                return $productStorageSpace;
            }
        }

        return null;
    }

    public function getProductStorageSpaceQuantityLeft(
        SpecificProductId $specificProductModelId,
        ProductStorage $productStorageSpace,
    ): int {
        if (!$specificProductModelId->equals($productStorageSpace->getSpecificProductModelId())) {
            throw new StorageAreaOccupiedException('Area and shelf already occupied by other product.');
        }
        
        return $productStorageSpace->getQuantityLeft();
    }

    public function reserveProductStorageSpaceQuantity(
        SpecificProductId $specificProductModelId,
        ProductStorage $productStorageSpace,
        int $quantity,
    ): void {
        if (!$specificProductModelId->equals($productStorageSpace->getSpecificProductModelId())) {
            throw new StorageAreaOccupiedException('Area and shelf already occupied by other product.');
        }
        
        $productStorageSpace->addQuantity($quantity);
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
