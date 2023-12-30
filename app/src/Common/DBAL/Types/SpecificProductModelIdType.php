<?php

declare(strict_types=1);

namespace App\Common\DBAL\Types;

use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;

class SpecificProductModelIdType extends CustomBigIntId
{
    const CUSTOM_QUESTION_ID = 'specific_product_id';

    protected function castToClass(string $id)
    {
        return SpecificProductId::createFromString($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::CUSTOM_QUESTION_ID;
    }
}