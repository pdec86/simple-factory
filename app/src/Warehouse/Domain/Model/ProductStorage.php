<?php

declare(strict_types=1);

namespace App\Warehouse\Domain\Model;

use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaOccupiedException;
use App\Warehouse\Domain\Model\Exceptions\StorageAreaTooSmallException;
use App\Warehouse\Domain\Model\ValueObjects\ProductStorageId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: 't_warehouseProductStorage')]
#[ORM\Entity()]
#[ORM\HasLifecycleCallbacks]
class ProductStorage
{
    use ClockAwareTrait;

    #[Embedded(class: ProductStorageId::class, columnPrefix: "storageSpace")]
    private ?ProductStorageId $id = null;

    #[ORM\Column(name: 'areaName', type: 'string', length: 100, nullable: false)]
    private string $areaName;

    #[ORM\Column(name: 'shelf', type: 'string', length: 100, nullable: false)]
    private string $shelf;

    #[Embedded(class: Dimensions::class, columnPrefix: 'dimensions_')]
    private Dimensions $dimensions;

    #[ORM\Column(name: 'specificProductDimensions', type: 'object', nullable: true)]
    private ?Dimensions $specificProductDimensions = null;

    #[ORM\Column(name: 'specificProductModelId', type: 'specific_product_id', length: 255, nullable: true)]
    private ?SpecificProductId $specificProductModelId = null;

    #[ORM\Column(name: 'maxQuantity', type: 'bigint', nullable: false)]
    private int $maxQuantity = 0;

    #[ORM\Column(name: 'quantity', type: 'bigint', nullable: false)]
    private int $quantity = 0;

    #[ORM\ManyToOne(targetEntity: StorageSpace::class)]
    #[ORM\JoinColumn(name: 'storageSpaceId', referencedColumnName: 'storageSpaceId')]
    #[Ignore]
    private StorageSpace $storageSpace;

    #[ORM\Column(name: 'createdAt', type: 'datetime_immutable', updatable: false)]
    #[Ignore]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updatedAt', type: 'datetime_immutable', nullable: true)]
    #[Ignore]
    private ?\DateTimeImmutable $updatedAt = null;

    private function __construct(StorageSpace $storageSpace)
    {
        $this->storageSpace = $storageSpace;
    }

    public static function createWithBasicData(
        StorageSpace $storageSpace,
        string $areaName,
        string $shelf,
        Dimensions $dimensions
    ): self {
        $productStorage = new self($storageSpace);
        $productStorage->changeAreaName($areaName);
        $productStorage->changeShelf($shelf);
        $productStorage->dimensions = $dimensions;

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

    public function isPossibleToReserveForSpecificProduct(SpecificProductId $specificProductId, Dimensions $specificProductDimensions): bool
    {
        if (
            (
                null === $this->specificProductModelId
                || $this->specificProductModelId->equals($specificProductId)
            )
            || (
                null !== $this->specificProductModelId
                && 0 == $this->quantity
            )
        ) {
           return $this->verifyDimensions($specificProductDimensions);
        }

        return false;
    }

    public function claimForSpecificProduct(SpecificProductId $specificProductId, Dimensions $specificProductDimensions): void
    {
        if (!$this->isPossibleToReserveForSpecificProduct($specificProductId, $specificProductDimensions)) {
            throw new StorageAreaOccupiedException('Area and shelf already occupied by other product.');
        }

        $this->specificProductModelId = $specificProductId;
        $this->specificProductDimensions = $specificProductDimensions;
        $this->changeMaxQuantity($specificProductDimensions);
    }

    private function changeMaxQuantity(Dimensions $specificProductDimensions): void
    {
        $maxQuantity = 0;

        if (!$this->verifyDimensions($specificProductDimensions)) {
            throw new StorageAreaTooSmallException();
        }

        $productLength = $specificProductDimensions->getLength();
        $productWidth = $specificProductDimensions->getWidth();

        // TODO: Optimize for better product storage handling
        if (
            $this->dimensions->getLength() > $specificProductDimensions->getLength()
            && $this->dimensions->getWidth() > $specificProductDimensions->getWidth()
        ) {
            $productLength = $specificProductDimensions->getLength();
            $productWidth = $specificProductDimensions->getWidth();
        } else if (
            $this->dimensions->getLength() > $specificProductDimensions->getWidth()
            && $this->dimensions->getWidth() > $specificProductDimensions->getLength()
        ) {
            $productLength = $specificProductDimensions->getWidth();
            $productWidth = $specificProductDimensions->getLength();
        }

        $maxQuantityLength = (int) bcdiv($this->dimensions->getLength(),
            0 == bccomp('0.0', $productLength, 2) ? '0.01' : $productLength
        );
        $maxQuantityWidth = (int) bcdiv($this->dimensions->getWidth(),
            0 == bccomp('0.0', $productWidth, 2) ? '0.01' : $productWidth
        );
        $maxQuantityHeight = (int) bcdiv($this->dimensions->getHeight(),
            0 == bccomp('0.0', $specificProductDimensions->getHeight(), 2) ? '0.01' : $specificProductDimensions->getHeight()
        );
        $maxQuantity = $maxQuantityLength * $maxQuantityWidth * $maxQuantityHeight;
        $maxQuantity = -1 == bccomp((string) PHP_INT_MAX, (string) $maxQuantity) ? PHP_INT_MAX : $maxQuantity;

        if (0 >= $maxQuantity) {
            throw new \DomainException('Maximum quantity cannot be less or equal 0.');
        }

        if ($this->quantity > $maxQuantity) {
            throw new \DomainException('Current quantity is larger. Free up occupied space.');
        }

        $this->maxQuantity = $maxQuantity;
    }

    private function verifyDimensions(Dimensions $specificProductDimensions): bool
    {
        if (
            (
                $this->dimensions->getLength() > $specificProductDimensions->getLength()
                && $this->dimensions->getWidth() > $specificProductDimensions->getWidth()
                && $this->dimensions->getHeight() > $specificProductDimensions->getHeight()
            ) || (
                $this->dimensions->getWidth() > $specificProductDimensions->getLength()
                && $this->dimensions->getLength() > $specificProductDimensions->getWidth()
                && $this->dimensions->getHeight() > $specificProductDimensions->getHeight()
            )
        ) {
            return null !== $this->specificProductDimensions ? $this->specificProductDimensions->equals($specificProductDimensions) : true;
        }

        return false;
    }

    public function getQuantityLeft(): int
    {
        return $this->maxQuantity - $this->quantity;
    }

    public function addQuantity(int $quantity): void
    {
        if (0 > $quantity) {
            throw new \DomainException('Quantity cannot be less than 0.');
        }

        if ($this->quantity + $quantity > $this->maxQuantity) {
            throw new \DomainException('Quantity cannot be greater than possible maximum quantity.');
        }

        $this->quantity += $quantity;
    }

    public function getSpecificProductModelId(): SpecificProductId
    {
        return $this->specificProductModelId;
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
        $this->createdAt = $this->now();
    }

    #[ORM\PreUpdate]
    public function doOnPreUpdate()
    {
        $this->updatedAt = $this->now();
    }
}
