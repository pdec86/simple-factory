<?php

declare(strict_types=1);

namespace App\Tests\Catalogue\Domain\Model\Messages;

use App\Catalogue\Domain\Model\Messages\ProductOrdered;
use App\Common\Domain\Model\ValueObject\Dimensions;
use PHPUnit\Framework\TestCase;

class ProductOrderedTest extends TestCase
{
    public function testProperProductMessageCreation()
    {
        $dimensions = new Dimensions('1.10', '2.20', '3.30');
        $productOrdered = new ProductOrdered('21', 5, $dimensions);

        self::assertEquals('21', $productOrdered->getSpecificProductId());
        self::assertEquals(5, $productOrdered->getQuantity());
        self::assertInstanceOf(Dimensions::class, $productOrdered->getDimensions());
        self::assertEquals('1.10', $productOrdered->getDimensions()->getLength());
        self::assertEquals('2.20', $productOrdered->getDimensions()->getWidth());
        self::assertEquals('3.30', $productOrdered->getDimensions()->getHeight());
    }

    public function testProperProductMessageSerialization()
    {
        $dimensions = new Dimensions('1.1', '2.2', '3.3');
        $productOrdered = new ProductOrdered('21', 5, $dimensions);

        $serialized = serialize($productOrdered);
        self::assertIsString($serialized);

        $unserialized = unserialize($serialized);
        self::assertInstanceOf(ProductOrdered::class, $unserialized);
        self::assertEquals('21', $unserialized->getSpecificProductId());
        self::assertEquals(5, $unserialized->getQuantity());
        self::assertInstanceOf(Dimensions::class, $productOrdered->getDimensions());
        self::assertEquals('1.1', $unserialized->getDimensions()->getLength());
        self::assertEquals('2.2', $unserialized->getDimensions()->getWidth());
        self::assertEquals('3.3', $unserialized->getDimensions()->getHeight());

    }
}
