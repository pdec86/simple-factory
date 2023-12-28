<?php

declare(strict_types=1);

namespace App\Common\DBAL\Types;

use App\Catalogue\Domain\Model\ValueObjects\ProductId;

class ProductIdType extends CustomBigIntId
{
    const CUSTOM_QUESTION_ID = 'product_id';

    protected function castToClass(string $id)
    {
        return ProductId::createFromString($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::CUSTOM_QUESTION_ID;
    }
}