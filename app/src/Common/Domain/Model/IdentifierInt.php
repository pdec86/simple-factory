<?php

declare(strict_types=1);

namespace App\Common\Domain\Model;

use App\Common\Domain\Model\Interfaces\IdentifierInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
class IdentifierInt implements IdentifierInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'Id', type: 'integer', options: ["unsigned" => true])]
    protected ?int $value = null;

    public function __construct(?int $value = null)
    {
        $this->value = $value;
    }

    public static function createFromString(string $value): static
    {
        if (false == preg_match('/^\d+$/', $value)) {
            throw new \RuntimeException('ID value must be a numeric string.');
        }

        if (-1 == bccomp((string) PHP_INT_MAX, $value)) {
            throw new \RuntimeException('ID is outside of PHP max integer range.');
        }

        return new static((int) $value);
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function changeId(int $id): static
    {
        return new static($id);
    }

    public function equals(IdentifierInterface $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
