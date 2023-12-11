<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Model;

use App\Common\Domain\Model\IdentifierBigInt;
use App\Common\Domain\Model\Interfaces\IdentifierInterface;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Symfony\Component\Clock\ClockAwareTrait;

#[ORM\Table(name: 't_salesSpecificProductModel')]
#[ORM\Entity()]
class SpecificProductModel
{
    use ClockAwareTrait;

    #[Embedded(class: IdentifierBigInt::class, columnPrefix: "specificProductModel")]
    private ?IdentifierInterface $id = null;

    #[Embedded(class: CodeEan::class, columnPrefix: false)]
    private CodeEan $codeEan;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'productId', referencedColumnName: 'productId')]
    private Product $product;

    #[ORM\Column(name: 'discontinued', type: 'datetimetz', nullable: true)]
    private ?\DateTimeImmutable $discontinued = null;

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

    public function getId(): IdentifierInterface
    {
        return $this->id;
    }

    public function getCodeEan(): CodeEan
    {
        return $this->codeEan;
    }

    public function getProductId(): IdentifierInterface
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
}
