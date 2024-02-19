<?php

declare(strict_types=1);

namespace App\Common\Domain\Model\ValueObject;

use App\Common\Domain\Model\Exceptions\InvalidDimensionException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class Dimensions implements \Serializable
{
    /**
     * @var string Length in centimeters.
     */
    #[ORM\Column(name: 'length', type: 'string', length: 20, nullable: false)]
    private string $length;

    /**
     * @var string Width in centimeters.
     */
    #[ORM\Column(name: 'width', type: 'string', length: 20, nullable: false)]
    private string $width;

    /**
     * @var string Height in centimeters.
     */
    #[ORM\Column(name: 'height', type: 'string', length: 20, nullable: false)]
    private string $height;

    public function __construct(string $length, string $width, string $height)
    {
        $length = str_replace(',', '.', trim($length));
        $this->checkDimensionalCorrectness($length);
        $width = str_replace(',', '.', trim($width));
        $this->checkDimensionalCorrectness($width);
        $height = str_replace(',', '.', trim($height));
        $this->checkDimensionalCorrectness($height);

        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return string Length in centimeters
     */
    public function getLength(): string
    {
        return $this->length;
    }

    /**
     * @return string Width in centimeters.
     */
    public function getWidth(): string
    {
        return $this->width;
    }

    /**
     * @return string Height in centimeters.
     */
    public function getHeight(): string
    {
        return $this->height;
    }

    public function equals(Dimensions $other): bool
    {
        return bccomp($this->length, $other->length, 2) === 0
            && bccomp($this->width, $other->width, 2) === 0
            && bccomp($this->height, $other->height, 2) === 0;
    }

    private function checkDimensionalCorrectness(string $dimension): void
    {
        if (1 !== preg_match('/^\d+(\.\d{1,2})?$/', $dimension)) {
            throw new InvalidDimensionException('Dimension is not correct.');
        }
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
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
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
        if (null === $data['length']) {
            throw new \LogicException('Length must be provided.');
        }
        $this->checkDimensionalCorrectness($data['length']);

        if (null === $data['width']) {
            throw new \LogicException('Width must be provided.');
        }
        $this->checkDimensionalCorrectness($data['width']);

        if (null === $data['height']) {
            throw new \LogicException('Height must be provided.');
        }
        $this->checkDimensionalCorrectness($data['height']);

        $this->length = (string) $data['length'];
        $this->width = (string) $data['width'];
        $this->height = (string) $data['height'];

        $this->checkDimensionalCorrectness($this->length);
        $this->checkDimensionalCorrectness($this->width);
        $this->checkDimensionalCorrectness($this->height);
    }
}
