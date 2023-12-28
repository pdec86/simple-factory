<?php

declare(strict_types=1);

namespace App\Common\Domain\Model\Interfaces;

interface IdentifierInterface
{
    public static function createFromString(string $value): static;

    public function getValue(): mixed;
}
