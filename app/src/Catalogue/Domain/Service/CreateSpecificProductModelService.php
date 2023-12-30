<?php

declare(strict_types=1);

namespace App\Catalogue\Domain\Service;

use App\Catalogue\Domain\Model\Exceptions\ProductNotFoundException;
use App\Catalogue\Domain\Model\Product;
use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Catalogue\Infrastructure\Repository\ProductRepository;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Doctrine\Persistence\ManagerRegistry;

class CreateSpecificProductModelService
{
    public function __construct(private ManagerRegistry $registry)
    {
    }
    
    public function execute(
        ProductId $productId,
        CodeEan $codeEan,
        string $length,
        string $width,
        string $height
    ): SpecificProductId {
        $entityManager = $this->registry->getManagerForClass(Product::class);
        /** @var ProductRepository $productRepository */
        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->fetchById($productId);

        if (null === $product) {
            throw new ProductNotFoundException();
        }

        $product->addVariant($codeEan, $length, $width, $height);

        $entityManager->persist($product);
        $entityManager->flush();

        return $product->getVariantIdByCodeEAN($codeEan);
    }
}
