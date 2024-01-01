<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Controller;

use App\Catalogue\Application\Model\ProductDTO;
use App\Catalogue\Application\Model\SpecificProductModelDTO;
use App\Catalogue\Application\Service\ProductManager;
use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/product')]
class ProductController extends AbstractController
{
    #[Route('', name: 'catalogue_product_index', methods: ['GET'],
        condition: "request.headers.get('Accept') matches '/text\\\\/html/'")]
    public function catalogueProductsIndex(): Response
    {
        return $this->render('catalogue/products/index.html.twig');
    }

    #[Route('', name: 'catalogue_product_list', methods: ['GET'],
        condition: "request.headers.get('Accept') matches '/application\\\\/json/'")]
    public function catalogueProductsList(ProductManager $productManager): Response
    {
        $allProducts = $productManager->getAllProducts();

        return $this->json(['products' => $allProducts]);
    }

    #[Route('', name: 'catalogue_product_create', methods: ['POST'],
        condition: "request.headers.get('Accept') matches '/application\\\\/json/'")]
    public function catalogueCreateProduct(
        #[MapRequestPayload('json')] ProductDTO $productDTO,
        ProductManager $productManager
    ): Response {
        $product = $productManager->createProduct($productDTO);

        return $this->json(['product' => $product]);
    }

    #[Route('/{productId}', name: 'catalogue_product_edit', methods: ['PUT'],
        condition: "request.headers.get('Accept') matches '/application\\\\/json/'")]
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

    #[Route('/{productId}/variant', name: 'catalogue_product_variants_list', methods: ['GET'],
        condition: "request.headers.get('Accept') matches '/application\\\\/json/'")]
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

    #[Route('/{productId}/variant', name: 'catalogue_product_variants_create', methods: ['POST'],
        condition: "request.headers.get('Accept') matches '/application\\\\/json/'")]
    public function catalogueCreateProductVariant(
        int $productId,
        #[MapRequestPayload('json')] SpecificProductModelDTO $specificProductModelDTO,
        ProductManager $productManager
    ): Response {
        if ($productId !== $specificProductModelDTO->productId) {
            throw new \RuntimeException('ID in path does not match product ID in DTO.');
        }
        $variant = $productManager->createSpecificProductModel($specificProductModelDTO);

        return $this->json(['variant' => $variant]);
    }

    #[Route('/{productId}/variant/{codeEan}/buy/{quantity}', name: 'catalogue_product_variants_create', methods: ['POST'],
        condition: "request.headers.get('Accept') matches '/application\\\\/json/'")]
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
