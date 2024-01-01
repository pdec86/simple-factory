<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Repository;

use App\Catalogue\Domain\Model\Product;
use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    /**
     * @return Product|null
     */
    public function fetchById(ProductId $id, bool $includeDiscontinued = true): ?Product
    {
        $queryBuilder = $this->createQueryBuilder('product');
        $expr = $queryBuilder->expr();

        $queryBuilder->select('product')
            ->andWhere($expr->eq('product.id.value', ':productId'))
            ->setParameter('productId', $id->getValue());
        
        if (!$includeDiscontinued) {
            $queryBuilder->andWhere($expr->isNull('product.discontinued'));
        }
        
        $query = $queryBuilder->getQuery();
        
        return $query->getOneOrNullResult();
    }

    /**
     * @return Product|null
     */
    public function fetchSpecificProductModelId(SpecificProductId $specificProductId, bool $includeDiscontinued = true): ?Product
    {
        $queryBuilder = $this->createQueryBuilder('product');
        $expr = $queryBuilder->expr();

        $queryBuilder->select('product')
            ->innerJoin('product.variants', 'variant')
            ->andWhere($expr->eq('variant.id.value', ':specificProductId'))
            ->setParameter('specificProductId', $specificProductId->getValue());
        
        if (!$includeDiscontinued) {
            $queryBuilder->andWhere($expr->isNull('product.discontinued'));
        }
        
        $query = $queryBuilder->getQuery();
        
        return $query->getOneOrNullResult();
    }

    /**
     * @return Product[]
     */
    public function fetchByCodeEan(CodeEan $codeEan): ?Product
    {
        $queryBuilder = $this->createQueryBuilder('product');
        $expr = $queryBuilder->expr();

        $queryBuilder->select('product')
            ->innerJoin('product.variants', 'variant')
            ->andWhere($expr->eq('variant.codeEan.code', ':codeEan'))
            ->setParameter('codeEan', $codeEan->getCode());
        
        $query = $queryBuilder->getQuery();
        
        return $query->getOneOrNullResult();
    }
}
