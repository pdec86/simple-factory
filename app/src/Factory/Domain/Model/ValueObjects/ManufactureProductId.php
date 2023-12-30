<?php

declare(strict_types=1);

namespace App\Factory\Domain\Model\ValueObjects;

use App\Common\Domain\Model\IdentifierBigInt;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ManufactureProductId extends IdentifierBigInt
{
}
