<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Model;

use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Warehouse\Domain\Model\ValueObjects\ProductStorageId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: 't_warehouseStorageSpace')]
#[ORM\Entity()]
#[ORM\HasLifecycleCallbacks]
class ProductStorage
{
    use ClockAwareTrait;

    #[Embedded(class: ProductStorageId::class, columnPrefix: "storageSpace")]
    private ?ProductStorageId $id = null;

    #[ORM\Column(name: 'productId', type: 'product_id', length: 255, nullable: false)]
    private ProductId $productId;

    #[ORM\Column(name: 'areaName', type: 'string', length: 100, nullable: false)]
    private string $areaName;

    #[ORM\Column(name: 'shelf', type: 'string', length: 100, nullable: false)]
    private string $shelf;

    #[ORM\Column(name: 'shelf', type: 'integer', nullable: false)]
    private int $quantity = 0;

    #[ORM\ManyToOne(targetEntity: StorageSpace::class, cascade: ['persist', 'merge', 'remove'])]
    #[ORM\JoinColumn(name: 'storageSpaceId', referencedColumnName: 'storageSpaceId')]
    private StorageSpace $storageSpace;

    #[ORM\Column(name: 'createdAt', type: 'datetime_immutable', updatable: false)]
    #[Ignore]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updatedAt', type: 'datetime_immutable', nullable: true)]
    #[Ignore]
    private ?\DateTimeImmutable $updatedAt = null;

    private function __construct()
    {
    }

    public static function createWithBasicData(ProductId $product, string $areaName, string $shelf, int $quantity): self
    {
        $productStorage = new self();
        $productStorage->productId = $product;
        $productStorage->changeAreaName($areaName);
        $productStorage->changeShelf($shelf);
        $productStorage->changeQuantity($quantity);

        return $productStorage;
    }

    public function changeAreaName(string $name): void
    {
        $name = trim($name);

        if (0 === mb_strlen($name, 'UTF-8')) {
            throw new \DomainException('Area name must not be blank.');
        }

        $this->areaName = $name;
    }

    public function changeShelf(string $name): void
    {
        $name = trim($name);

        if (0 === mb_strlen($name, 'UTF-8')) {
            throw new \DomainException('Shelf name must not be blank.');
        }

        $this->shelf = $name;
    }

    public function changeQuantity(int $quantity): void
    {
        if (0 > $quantity) {
            throw new \DomainException('Quantity cannot be less then 0.');
        }

        $this->quantity = $quantity;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getAreaName(): string
    {
        return $this->areaName;
    }

    public function getShelf(): string
    {
        return $this->shelf;
    }

    #[ORM\PrePersist]
    public function doOnPrePersist()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function doOnPreUpdate()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
