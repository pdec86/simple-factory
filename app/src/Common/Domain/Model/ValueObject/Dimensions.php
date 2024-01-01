<?php

declare(strict_types=1);

namespace App\Common\Domain\Model\ValueObject;

use App\Common\Domain\Model\Exceptions\InvalidDimensionException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class Dimensions
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

    private function checkDimensionalCorrectness(string $dimension): void
    {
        if (1 !== preg_match('/^\d+(\.\d{1,2})?$/', $dimension)) {
            throw new InvalidDimensionException('Dimension is not correct.');
        }
    }
}
