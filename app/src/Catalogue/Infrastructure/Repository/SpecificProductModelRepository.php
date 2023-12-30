<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Repository;

use App\Catalogue\Domain\Model\SpecificProductModel;
use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use Doctrine\ORM\EntityRepository;

class SpecificProductModelRepository extends EntityRepository
{
    /**
     * @return SpecificProductModel[]
     */
    public function fetchAllByProductId(ProductId $id, bool $includeDiscontinued = true): array
    {
        $queryBuilder = $this->createQueryBuilder('productVariant');
        $expr = $queryBuilder->expr();

        $queryBuilder->select('productVariant, product')
            ->innerJoin('productVariant.productId', 'product')
            ->andWhere($expr->eq('product.id.value', ':productId'))
            ->setParameter('productId', $id->getValue());
        
        if (!$includeDiscontinued) {
            $queryBuilder->andWhere($expr->isNull('productVariant.discontinued'));
        }
        
        $query = $queryBuilder->getQuery();
        
        return $query->getResult();
    }

    /**
     * @return SpecificProductModel|null
     */
    public function fetchById(SpecificProductId $id, bool $includeDiscontinued = true): ?SpecificProductModel
    {
        $queryBuilder = $this->createQueryBuilder('productVariant');
        $expr = $queryBuilder->expr();

        $queryBuilder->select('productVariant, product')
            ->innerJoin('productVariant.product', 'product')
            ->andWhere($expr->eq('productVariant.id.value', ':productId'))
            ->setParameter('productId', $id->getValue());
        
        if (!$includeDiscontinued) {
            $queryBuilder->andWhere($expr->isNull('productVariant.discontinued'));
        }
        
        $query = $queryBuilder->getQuery();
        
        return $query->getOneOrNullResult();
    }
}
