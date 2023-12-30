<?php

declare(strict_types=1);

namespace App\Factory\Domain\Model;

use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Factory\Domain\Model\ValueObjects\ManufactureProductId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: 't_factoryManufactureProduct')]
#[ORM\Entity()]
#[ORM\HasLifecycleCallbacks]
class ManufactureProduct
{
    use ClockAwareTrait;

    #[Embedded(class: ManufactureProductId::class, columnPrefix: "manufactureProduct")]
    private ?ManufactureProductId $id = null;

    #[ORM\Column(name: 'specificProductModelId', type: 'specific_product_id', length: 255, nullable: false)]
    private SpecificProductId $specificProductModelId;

    #[ORM\Column(name: 'quantity', type: 'integer', nullable: false)]
    private int $quantity;

    #[ORM\Column(name: 'createdAt', type: 'datetime_immutable', updatable: false)]
    #[Ignore]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updatedAt', type: 'datetime_immutable', nullable: true)]
    #[Ignore]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(SpecificProductId $specificProductId, int $quantity)
    {
        if ($quantity <= 0) {
            throw new \LogicException('Quantity cannot be less or queal zero.');
        }

        $this->specificProductModelId = $specificProductId;
        $this->quantity = $quantity;
    }

    public function getId(): ManufactureProductId
    {
        return $this->id;
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
