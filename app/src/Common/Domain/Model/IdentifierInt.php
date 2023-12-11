<?php

declare(strict_types=1);

namespace App\Common\Domain\Model;

use App\Common\Domain\Model\Interfaces\IdentifierInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class IdentifierInt implements IdentifierInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'Id', type: 'integer', options: ["unsigned" => true])]
    private ?int $value = null;

    public function __construct(?int $value = null)
    {
        $this->value = $value;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
