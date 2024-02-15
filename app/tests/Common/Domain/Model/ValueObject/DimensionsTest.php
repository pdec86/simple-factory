<?php

declare(strict_types=1);

namespace App\Tests\Common\Domain\Model\ValueObject;

use App\Common\Domain\Model\ValueObject\Dimensions;
use PHPUnit\Framework\TestCase;

class DimensionsTest extends TestCase
{
    public function testValidDimensions()
    {
        $dimensions = new Dimensions('1.10', '2.20', '3.30');
        
        self::assertEquals('1.10', $dimensions->getLength());
        self::assertEquals('2.20', $dimensions->getWidth());
        self::assertEquals('3.30', $dimensions->getHeight());
        self::assertNotEquals('1.1', $dimensions->getLength());
        self::assertNotEquals('2.2', $dimensions->getLength());
        self::assertNotEquals('3.3', $dimensions->getLength());
    }

    public function testDimensionsEquality()
    {
        $dimensions = new Dimensions('1.10', '2.20', '3.30');
        
        self::assertTrue($dimensions->equals(new Dimensions('1.10', '2.20', '3.30')));
        self::assertTrue($dimensions->equals(new Dimensions('1.1', '2.2', '3.3')));
        self::assertFalse($dimensions->equals(new Dimensions('1.11', '2.20', '3.30')));
        self::assertFalse($dimensions->equals(new Dimensions('1.10', '2.21', '3.30')));
        self::assertFalse($dimensions->equals(new Dimensions('1.10', '2.20', '3.31')));
    }
}
