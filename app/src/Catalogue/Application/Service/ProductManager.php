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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
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

    public function createSpecificProductModel(SpecificProductModelDTO $specificProductModelDTO): SpecificProductId
    {
        $specificProductModelId = $this->createSpecificProductModelService->execute(
            new ProductId($specificProductModelDTO->productId),
            $specificProductModelDTO->codeEan,
            $specificProductModelDTO->length,
            $specificProductModelDTO->width,
            $specificProductModelDTO->height,
        );

        return $specificProductModelId;
    }

    public function orderSpecificProductModel(SpecificProductId $id, int $quantity): void
    {
        $this->bus->dispatch((new Envelope(new ProductOrdered($id->getValue(), $quantity)))->with(
            new AmqpStamp('product.ordered')
        ));
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
