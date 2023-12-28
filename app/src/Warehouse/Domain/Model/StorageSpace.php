<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Model;

use App\Catalogue\Domain\Model\ValueObjects\ProductId;
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

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private string $name;

    /**
     * @var Collection<ProductStorage>|ProductStorage[]
     */
    #[ORM\OneToMany(targetEntity: ProductStorage::class, mappedBy: 'storageSpace')]
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

    public function addProductStorage(ProductId $product, string $areaName, string $shelf, int $quantity = 0)
    {
        foreach ($this->productsStorageSpaces as $productStorageSpace) {
            if (
                $areaName === $productStorageSpace->getAreaName()
                && $shelf === $productStorageSpace->getShelf()
                && !$product->equals($productStorageSpace->getProductId())
            ) {
                throw new StorageAreaOccupiedException('Area and shelf already occupied by other product');
            }
        }

        $this->productsStorageSpaces->add(ProductStorage::createWithBasicData($product, $areaName, $shelf, $quantity));
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
