<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Model\Messages;

use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;

class ProductOrdered implements \Serializable
{
    private ?string $productId;

    private ?int $quantity;

    public function __construct(?string $productId = null, ?int $quantity = 0)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function getSpecificProductId(): SpecificProductId
    {
        return new SpecificProductId($this->productId);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
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
            'productId' => $this->productId,
            'quantity' => $this->quantity,
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
        if (null === $data['productId'] ||
            (null !== $data['productId'] && 0 == preg_match('/\d+/', $data['productId']))
        ) { // matches 0 or false
            throw new \LogicException('ID must be not null and should be a valid numeric string.');
        }

        if (null === $data['quantity'] || 0 >= (int) $data['quantity']) {
            throw new \LogicException('Quantity must be a positive integer.');
        }

        $this->productId = (string) $data['productId'];
        $this->quantity = (int) $data['quantity'];
    }
}
