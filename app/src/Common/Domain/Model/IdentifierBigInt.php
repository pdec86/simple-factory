<?php

declare(strict_types=1);

namespace App\Common\Domain\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class IdentifierBigInt extends CustomBigInt
{
}
