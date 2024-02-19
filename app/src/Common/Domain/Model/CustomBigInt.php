<?php

declare(strict_types=1);

namespace App\Common\Domain\Model;

use App\Common\Domain\Model\Interfaces\IdentifierInterface;
use App\Common\ORM\Generator\CustomBigIntGenerator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class CustomBigInt implements IdentifierInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: CustomBigIntGenerator::class)]
    #[ORM\Column(name: 'Id', type: 'string', length: 255, nullable: false)]
    protected string $value;

    public function __construct(string $id)
    {
        if (0 == preg_match('/^\d+$/', $id)) {
            throw new \LogicException('ID should be a valid numeric string');
        }
        $this->value = $id;
    }

    public static function createFromString(string $value): static
    {
        return new static($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function changeId(string $id): static
    {
        return new static($id);
    }

    public function equals(IdentifierInterface $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return string
     *
     * @final
     */
    public function serialize()
    {
        return serialize($this->__serialize());
    }

    /** @return array<string, mixed> */
    public function __serialize(): array
    {
        return [
            'value' => $this->value,
        ];
    }

    /**
     * @param string $serialized
     *
     * @return void
     *
     * @final
     */
    public function unserialize($serialized)
    {
        $this->__unserialize(unserialize($serialized));
    }

    /** @param array<string, mixed> $data */
    public function __unserialize(array $data): void
    {
        if (null !== $data['value'] && 0 == preg_match('/^\d+$/', $data['value'])) { // matches 0 or false
            throw new \LogicException('ID, if not null, then should be a valid numeric string.');
        }

        $this->value = (string) $data['value'];
    }
}
