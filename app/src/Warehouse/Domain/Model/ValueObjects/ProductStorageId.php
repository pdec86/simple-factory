<?php

declare(strict_types=1);

namespace App\Warehouse\Domain\Model\ValueObjects;

use App\Common\Domain\Model\IdentifierInt;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ProductStorageId extends IdentifierInt
{
}
