<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Controller;

use App\Catalogue\Application\Model\ProductDTO;
use App\Catalogue\Application\Model\SpecificProductModelDTO;
use App\Catalogue\Application\Service\ProductManager;
use App\Catalogue\Domain\Model\Exceptions\VariantAlreadyExistsException;
use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/product')]
class ProductController extends AbstractController
{
    public function __construct(
        private string $fontsPath,
        private string $ocrBLikeFontName,
    ) {
    }

    #[Route('Index', name: 'catalogue_product_index', methods: ['GET'])]
    public function catalogueProductsIndex(): Response
    {
        return $this->render('catalogue/products/index.html.twig');
    }

    #[Route('.{_format}', name: 'catalogue_product_list', methods: ['GET'],
        format: 'json', requirements: ['_format' => 'json'])]
    public function catalogueProductsList(ProductManager $productManager): Response
    {
        $allProducts = $productManager->getAllProducts();

        return $this->json(['products' => $allProducts]);
    }

    #[Route('.{_format}', name: 'catalogue_product_create', methods: ['POST'],
        format: 'json', requirements: ['_format' => 'json'])]
    public function catalogueCreateProduct(
        #[MapRequestPayload('json')] ProductDTO $productDTO,
        ProductManager $productManager
    ): Response {
        $product = $productManager->createProduct($productDTO);

        return $this->json(['product' => $product]);
    }

    #[Route('/{productId}.{_format}', name: 'catalogue_product_edit', methods: ['PUT'],
        format: 'json', requirements: ['_format' => 'json'])]
    public function catalogueEditProduct(
        int $productId,
        #[MapRequestPayload('json')] ProductDTO $productDTO,
        ProductManager $productManager
    ): Response {
        if ($productId !== $productDTO->id) {
            throw new \RuntimeException('ID in path does not match ID in DTO.');
        }
        $product = $productManager->editProduct($productDTO);

        return $this->json(['product' => $product]);
    }

    #[Route('/{productId}/variant.{_format}', name: 'catalogue_product_variants_list', methods: ['GET'],
        format: 'json', requirements: ['_format' => 'json'])]
    public function catalogueProductVariantsList(
        int $productId,
        ProductManager $productManager
    ): Response {
        if (empty($productId)) {
            throw new \RuntimeException('ID in path is not correct');
        }
        $variants = $productManager->getProductVariants(new ProductId($productId));

        return $this->json(['variants' => $variants]);
    }

    #[Route('/{productId}/variant.{_format}', name: 'catalogue_product_variants_create', methods: ['POST'],
        format: 'json', requirements: ['_format' => 'json'])]
    public function catalogueCreateProductVariant(
        int $productId,
        #[MapRequestPayload('json')] SpecificProductModelDTO $specificProductModelDTO,
        ProductManager $productManager
    ): Response {
        if ($productId !== $specificProductModelDTO->productId) {
            throw new \RuntimeException('ID in path does not match product ID in DTO.');
        }

        try {
            $variant = $productManager->createSpecificProductModel($specificProductModelDTO);

            return $this->json(['variant' => $variant]);
        } catch (VariantAlreadyExistsException $variantExists) {
            return $this->json(['message' => 'Variant already exists'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{productId}/variant/{codeEan}/buy/{quantity}.{_format}', name: 'catalogue_product_variants_buy', methods: ['POST'],
        format: 'json', requirements: ['_format' => 'json'])]
    public function catalogueBuyProductVariant(
        int $productId,
        string $codeEan,
        int $quantity,
        ProductManager $productManager
    ): Response {
        if (empty($productId)) {
            throw new \RuntimeException('ID in path is not correct');
        }
        $codeEan = new CodeEan($codeEan);
        $productManager->orderSpecificProductModelByCodeEan($codeEan, $quantity);

        return $this->json('');
    }
}
