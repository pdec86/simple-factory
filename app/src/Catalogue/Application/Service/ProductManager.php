<?php

declare(strict_types=1);

namespace App\Catalogue\Application\Service;

use App\Catalogue\Application\Model\ProductDTO;
use App\Catalogue\Application\Model\SpecificProductModelDTO;
use App\Catalogue\Domain\Model\Exceptions\ProductNotFoundException;
use App\Catalogue\Domain\Model\Messages\ProductOrdered;
use App\Catalogue\Domain\Model\Product;
use App\Catalogue\Domain\Model\SpecificProductModel;
use App\Catalogue\Domain\Model\ValueObjects\ProductId;
use App\Catalogue\Domain\Model\ValueObjects\SpecificProductId;
use App\Catalogue\Domain\Service\CreateProductService;
use App\Catalogue\Domain\Service\CreateSpecificProductModelService;
use App\Catalogue\Infrastructure\Repository\ProductRepository;
use App\Common\Domain\Model\ValueObject\CodeEan;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductManager
{
    public function __construct(
        private ManagerRegistry $registry,
        private MessageBusInterface $bus,
        private CreateProductService $createProductService,
        private CreateSpecificProductModelService $createSpecificProductModelService,
    ) {
    }

    public function createProduct(ProductDTO $productDTO): ProductDTO
    {
        $product = $this->createProductService->execute($productDTO->name, $productDTO->description);

        $entityManager = $this->registry->getManagerForClass(Product::class);
        $entityManager->persist($product);
        $entityManager->flush();

        return new ProductDTO(
            $product->getId()->getValue(),
            $product->getName(),
            $product->getDescription(),
        );
    }

    /**
     * @return ProductDTO[]
     */
    public function getAllProducts(): array
    {
        /** @var ProductRepository $repository */
        $repository = $this->registry->getManagerForClass(Product::class)->getRepository(Product::class);
        $products = $repository->findAll();

        return array_map(fn (Product $product) => new ProductDTO(
            $product->getId()->getValue(),
            $product->getName(),
            $product->getDescription()
        ), $products);
    }

    /**
     * @return SpecificProductModelDTO[]
     */
    public function getProductVariants(ProductId $productId): array
    {
        /** @var ProductRepository $repository */
        $repository = $this->registry->getManagerForClass(Product::class)->getRepository(Product::class);
        $product = $repository->fetchById($productId);

        return $product->getAllVariants(fn (SpecificProductModel $variant) => new SpecificProductModelDTO(
            $variant->getId()->getValue(),
            $product->getId()->getValue(),
            $variant->getCodeEan(),
            $variant->getName(),
            $variant->getDimensions()->getLength(),
            $variant->getDimensions()->getWidth(),
            $variant->getDimensions()->getHeight(),
        ));
    }

    public function editProduct(ProductDTO $productDTO): ProductDTO
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->registry->getManagerForClass(Product::class)->getRepository(Product::class);
        $product = $productRepository->fetchById(new ProductId($productDTO->id));

        if (null === $product) {
            throw new ProductNotFoundException();
        }

        $product->changeName($productDTO->name);
        $product->changeDescription($productDTO->description);

        $entityManager = $this->registry->getManagerForClass(Product::class);
        $entityManager->persist($product);
        $entityManager->flush();

        return new ProductDTO(
            $product->getId()->getValue(),
            $product->getName(),
            $product->getDescription(),
        );
    }

    public function getProductBySpecificProductModelId(SpecificProductId $specificProductId): Product
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->registry->getManagerForClass(Product::class)->getRepository(Product::class);
        $product = $productRepository->fetchSpecificProductModelId($specificProductId);

        if (null === $product) {
            throw new ProductNotFoundException();
        }

        return $product;
    }

    public function createSpecificProductModel(SpecificProductModelDTO $specificProductModelDTO): SpecificProductId
    {
        $specificProductModelId = $this->createSpecificProductModelService->execute(
            new ProductId($specificProductModelDTO->productId),
            $specificProductModelDTO->codeEan,
            $specificProductModelDTO->name,
            $specificProductModelDTO->length,
            $specificProductModelDTO->width,
            $specificProductModelDTO->height,
        );

        return $specificProductModelId;
    }

    public function orderSpecificProductModelByCodeEan(CodeEan $codeEan, int $quantity): void
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->registry->getManagerForClass(Product::class)->getRepository(Product::class);
        $product = $productRepository->fetchByCodeEan($codeEan);

        if (null === $product) {
            throw new ProductNotFoundException();
        }
        
        $productOrdered = new ProductOrdered($product->getVariantIdByCodeEAN($codeEan)->getValue(), $quantity);

        $this->bus->dispatch($productOrdered);
    }

    /**
     * @throws ProductNotFoundException
     */
    public function discontinueProduct(ProductDTO $productDTO, ?\DateTimeImmutable $discontinuationDate): void
    {
        /** @var ProductRepository $productRepository */
        $productRepository = $this->registry->getManagerForClass(Product::class)->getRepository(Product::class);
        $product = $productRepository->fetchById(new ProductId($productDTO->id), false);

        if (null === $product) {
            throw new ProductNotFoundException();
        }

        $product->setDiscontinuation($discontinuationDate);
    }
}
