<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Model;

use App\Catalogue\Domain\Model\Exceptions\NoVariantExistsException;
use App\Catalogue\Domain\Model\Exceptions\ProductDiscontinuedException;
use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductModelData;
use App\Catalogue\Infrastructure\Repository\ProductRepository;
use App\Common\Domain\Model\ValueObject\CodeEan;
use App\Common\Domain\Model\ValueObject\Dimensions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: 't_salesProduct')]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    use ClockAwareTrait;

    #[Embedded(class: ProductId::class, columnPrefix: "product")]
    private ?ProductId $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(name: 'discontinued', type: 'datetimetz', nullable: true)]
    private ?\DateTimeImmutable $discontinued = null;

    /**
     * @var Collection<SpecificProductModel>|SpecificProductModel[]
     */
    #[ORM\OneToMany(targetEntity: SpecificProductModel::class, mappedBy: 'product', cascade: ['persist', 'merge'])]
    private Collection $variants;

    #[ORM\Column(name: 'createdAt', type: 'datetime_immutable', updatable: false)]
    #[Ignore]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updatedAt', type: 'datetime_immutable', nullable: true)]
    #[Ignore]
    private ?\DateTimeImmutable $updatedAt = null;

    private function __construct()
    {
        $this->variants = new ArrayCollection();
    }

    public static function createWithBasicData(string $name, ?string $description = null): self
    {
        $product = new self();
        $product->changeName($name);
        $product->changeDescription($description);

        return $product;
    }

    public function getId(): ?ProductId
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function changeDescription(?string $description): void
    {
        $description = trim($description ?? '');
        $this->description = 0 === mb_strlen($description, 'UTF-8') ? null : $description;
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

        foreach ($this->variants as $variant) {
            if (null === $variant->getDiscontinuation() || ($variant->getDiscontinuation() > $currentTime)) {
                $variant->setDiscontinuation($this->discontinued);
            }
        }
    }

    public function addVariant(CodeEan $codeEan, string $length, string $witdh, string $height): void
    {
        if (null === $this->discontinued) {
            $this->variants->add(SpecificProductModel::createWithBasicData($this, $codeEan, $length, $witdh, $height));
        } else {
            throw new ProductDiscontinuedException('Product has been discontinued.');
        }
    }

    public function getAllVariants(callable $callback): array
    {
        $variants = $this->variants->toArray();
        array_walk($variants, $callback);

        return $variants;
    }

    public function checkVariantExists(SpecificProductId $specificProductId): bool
    {
        foreach ($this->variants as $variant)
        { 
            if ($variant->getId()->equals($specificProductId)) {
                return true;
            }
        }

        return false;
    }

    public function getVariantIdByCodeEAN(CodeEan $codeEan): SpecificProductId
    {
        foreach ($this->variants as $variant)
        { 
            if ($variant->getCodeEan()->getCode() === $codeEan->getCode()) {
                return $variant->getId();
            }
        }

        throw new NoVariantExistsException('Variant does not exists in product.');
    }

    public function getVariantDimensions(SpecificProductId $specificProductId): Dimensions
    {
        foreach ($this->variants as $variant)
        { 
            if ($variant->getId()->equals($specificProductId)) {
                return $variant->getDimensions();
            }
        }

        throw new NoVariantExistsException('Variant does not exists in product.');
    }

    public function getVariantDiscontinuation(CodeEan $codeEan): ?\DateTimeImmutable
    {
        foreach ($this->variants as $variant) {
            if ($variant->getCodeEan()->getCode() === $codeEan->getCode()) {
                return $variant->getDiscontinuation();
            }
        }

        throw new NoVariantExistsException('Variant does not exists in product.');
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
