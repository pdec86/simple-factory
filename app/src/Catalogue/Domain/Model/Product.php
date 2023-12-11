<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Model;

use App\Common\Domain\Model\IdentifierInt;
use App\Common\Domain\Model\Interfaces\IdentifierInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;

#[ORM\Table(name: 't_salesProduct')]
#[ORM\Entity()]
class Product
{
    #[Embedded(class: IdentifierInt::class, columnPrefix: "product")]
    private ?IdentifierInterface $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description;

    private function __construct()
    {
    }

    public static function createWithBasicData(string $name, ?string $description = null): self
    {
        $product = new self();
        $product->changeName($name);
        $product->changeDescription($description);

        return $product;
    }

    public function getId(): ?IdentifierInterface
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
}
