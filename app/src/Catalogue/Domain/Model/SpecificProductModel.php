<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Model;

use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: 't_salesSpecificProductModel')]
#[ORM\Entity()]
#[ORM\HasLifecycleCallbacks]
class SpecificProductModel
{
    use ClockAwareTrait;

    #[Embedded(class: SpecificProductId::class, columnPrefix: "specificProductModel")]
    private ?SpecificProductId $id = null;

    #[Embedded(class: CodeEan::class, columnPrefix: false)]
    private CodeEan $codeEan;

    #[ORM\ManyToOne(targetEntity: Product::class, cascade: ['persist', 'merge', 'remove'])]
    #[ORM\JoinColumn(name: 'productId', referencedColumnName: 'productId')]
    private Product $product;

    #[ORM\Column(name: 'discontinued', type: 'datetimetz', nullable: true)]
    private ?\DateTimeImmutable $discontinued = null;

    #[ORM\Column(name: 'createdAt', type: 'datetime_immutable', updatable: false)]
    #[Ignore]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updatedAt', type: 'datetime_immutable', nullable: true)]
    #[Ignore]
    private ?\DateTimeImmutable $updatedAt = null;

    private function __construct(Product $product, CodeEan $codeEan)
    {
        $this->product = $product;
        $this->codeEan = $codeEan;
    }

    public static function createWithBasicData(Product $product, CodeEan $codeEAN): self
    {
        $product = new self($product, $codeEAN);

        return $product;
    }

    public function getId(): ?SpecificProductId
    {
        return $this->id;
    }

    public function getCodeEan(): CodeEan
    {
        return $this->codeEan;
    }

    public function getProductId(): ProductId
    {
        return $this->product->getId();
    }

    public function getDiscontinuation(): ?\DateTimeImmutable
    {
        return $this->discontinued;
    }

    public function setDiscontinuation(\DateTimeImmutable $discontinuation): void
    {
        $currentTime = $this->now();

        if ($discontinuation < $currentTime) {
            throw new \DomainException('Product discontinuation cannot be set in past.');
        }

        if (null !== $this->discontinued && $this->discontinued <= $currentTime) {
            throw new \DomainException('Product discontinuation cannot be changed because it was already in force.');
        }

        $this->discontinued = $discontinuation;
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
