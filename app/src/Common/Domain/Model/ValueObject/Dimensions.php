<?php

declare(strict_types=1);

namespace App\Common\Domain\Model\ValueObject;

use App\Common\Domain\Model\Exceptions\InvalidDimensionException;

class Dimensions
{
    private string $length;
    private string $width;
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

    public function getLength(): string
    {
        return $this->length;
    }

    public function getWidth(): string
    {
        return $this->width;
    }

    public function getHeight(): string
    {
        return $this->height;
    }

    private function checkDimensionalCorrectness(string $dimension): void
    {
        if (1 !== preg_match('/^\d+(\.\d+)?$/', $dimension)) {
            throw new InvalidDimensionException('Dimension is not correct.');
        }
    }
}
