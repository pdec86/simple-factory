<?php

declare(strict_types=1);

namespace App\Common\Domain\Model\Interfaces;

interface IdentifierInterface
{
    public function getValue(): mixed;
}
