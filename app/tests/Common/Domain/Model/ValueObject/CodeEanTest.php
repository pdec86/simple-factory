<?php

declare(strict_types=1);

namespace App\Tests\Common\Domain\Model\ValueObject;

use App\Common\Domain\Model\Exceptions\InvalidEanException;
use App\Common\Domain\Model\ValueObject\CodeEan;
use PHPUnit\Framework\TestCase;

class CodeEanTest extends TestCase
{
    public function testValidEan()
    {
        $code1 = new CodeEan('73513537');
        self::assertEquals('73513537', $code1->getCode());

        $code2 = new CodeEan('0799439112766');
        self::assertEquals('0799439112766', $code2->getCode());
    }

    public function testInvalidEan8()
    {
        try {
            new CodeEan('73513536');
            self::fail('Should not occcur');
        } catch (\Throwable $ex) {
            self::assertInstanceOf(InvalidEanException::class, $ex);
        }

        try {
            new CodeEan('73513538');
            self::fail('Should not occcur');
        } catch (\Throwable $ex) {
            self::assertInstanceOf(InvalidEanException::class, $ex);
        }
    }

    public function testInvalidEan13()
    {
        try {
            new CodeEan('0799439112765');
            self::fail('Should not occcur');
        } catch (\Throwable $ex) {
            self::assertInstanceOf(InvalidEanException::class, $ex);
        }

        try {
            new CodeEan('0799439112767');
            self::fail('Should not occcur');
        } catch (\Throwable $ex) {
            self::assertInstanceOf(InvalidEanException::class, $ex);
        }
    }
}
