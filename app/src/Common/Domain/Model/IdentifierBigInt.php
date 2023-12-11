<?php

declare(strict_types=1);

namespace App\Common\Domain\Model;

use App\Common\Domain\Model\Interfaces\IdentifierInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class IdentifierBigInt implements IdentifierInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'Id', type: 'bigint', options: ["unsigned" => true])]
    private ?string $value = null;

    public function __construct(?string $value = null)
    {
        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
