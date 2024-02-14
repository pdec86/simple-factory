<?php

declare(strict_types=1);

namespace App\Tests\Catalogue\Domain\Model;

use App\Catalogue\Domain\Model\Product;
use App\Catalogue\Domain\Model\SpecificProductModel;
use App\Common\Domain\Model\ValueObject\CodeEan;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

class SpecificProductModelTest extends TestCase
{
    use ClockSensitiveTrait;

    public function testSetDiscontinuationInPast()
    {
        $product = Product::createWithBasicData('Guitar');
        $codeEan = new CodeEan('0799439112766');

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 11:30:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be set in past.', $ex->getMessage());
        }

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:29:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be set in past.', $ex->getMessage());
        }

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-11 12:30:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be set in past.', $ex->getMessage());
        }

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-11 12:29:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be set in past.', $ex->getMessage());
        }

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-09 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-10 12:30:00'), $clock);

            $clock = static::mockTime(new \DateTimeImmutable('2023-12-11 12:35:00'));
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-10 12:37:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be set in past.', $ex->getMessage());
        }

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:40:00'), $clock);

            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:35:00'));
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 11:37:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be set in past.', $ex->getMessage());
        }

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:40:00'), $clock);

            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:35:00'));
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:34:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be set in past.', $ex->getMessage());
        }
    }

    public function testSetDiscontinuationForTheSecondTimeBeforePreviousOne()
    {
        $product = Product::createWithBasicData('Guitar');
        $codeEan = new CodeEan('0799439112766');

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:40:00'), $clock);

            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:45:00'));
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:48:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be changed because it was already in force.', $ex->getMessage());
        }

        try {
            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
            $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:40:00'), $clock);

            $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:40:00'));
            $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:48:00'), $clock);
            self::fail('Domain Exception should occur!');
        } catch (\DomainException $ex) {
            self::assertEquals('Product discontinuation cannot be changed because it was already in force.', $ex->getMessage());
        }
    }

    public function testSetDiscontinuationProperly()
    {
        $product = Product::createWithBasicData('Guitar');
        $codeEan = new CodeEan('0799439112766');

        // 1.
        $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
        $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
        $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 13:40:00'), $clock);
        self::assertEquals(new \DateTimeImmutable('2023-12-12 13:40:00'), $specificProductModel->getDiscontinuation());

        $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:40:00'));
        $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 12:48:00'), $clock);
        self::assertEquals(new \DateTimeImmutable('2023-12-12 12:48:00'), $specificProductModel->getDiscontinuation());

        // 2.
        $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
        $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
        $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 13:45:00'), $clock);
        self::assertEquals(new \DateTimeImmutable('2023-12-12 13:45:00'), $specificProductModel->getDiscontinuation());

        $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:40:00'));
        $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 13:40:00'), $clock);
        self::assertEquals(new \DateTimeImmutable('2023-12-12 13:40:00'), $specificProductModel->getDiscontinuation());

        // 3.
        $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
        $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
        $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 13:45:00'), $clock);
        self::assertEquals(new \DateTimeImmutable('2023-12-12 13:45:00'), $specificProductModel->getDiscontinuation());

        $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:40:00'));
        $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-13 11:40:00'), $clock);
        self::assertEquals(new \DateTimeImmutable('2023-12-13 11:40:00'), $specificProductModel->getDiscontinuation());

        // 4.
        $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:30:00'));
        $specificProductModel = SpecificProductModel::createWithBasicData($product, $codeEan, 'Model name', '100', '20', '5.5');
        $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-12 13:45:00'), $clock);
        self::assertEquals(new \DateTimeImmutable('2023-12-12 13:45:00'), $specificProductModel->getDiscontinuation());

        $clock = static::mockTime(new \DateTimeImmutable('2023-12-12 12:40:00'));
        $specificProductModel->setDiscontinuation(new \DateTimeImmutable('2023-12-13 12:39:00'), $clock);
        self::assertEquals(new \DateTimeImmutable('2023-12-13 12:39:00'), $specificProductModel->getDiscontinuation());
    }
}
