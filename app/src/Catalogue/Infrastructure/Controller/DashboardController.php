<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Controller;

use App\Catalogue\Application\Model\ProductDTO;
use App\Catalogue\Application\Model\SpecificProductModelDTO;
use App\Catalogue\Application\Service\ProductManager;
use App\Common\Domain\Model\ValueObject\CodeEan;
use App\Factory\Domain\Model\ManufactureProduct;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
   #[Route('/', name: 'catalogue_dashboard_index', methods: ['GET'])]
   public function catalogueIndex(string $fontsPath, string $ocrBLikeFontName, ProductManager $productManager): Response
   {
      $productDTO = $productManager->createProduct(new ProductDTO(null, 'Test product ' . random_int(1, 1000)));
      $specificProductId = $productManager->createSpecificProductModel(new SpecificProductModelDTO(
         null,
         $productDTO->id,
         new CodeEan('0799439112766'),
         '100',
         '20',
         '5.5'
      ));

      $quantity = random_int(1, 100);
      $productManager->orderSpecificProductModel($specificProductId, $quantity);

      return new Response("Created and ordered {$quantity} item(s) of product (#{$productDTO->id}) {$productDTO->name} and variant (#{$specificProductId->getValue()}).");
   }

   #[Route('/factory', name: 'factory_ordered_index', methods: ['GET'])]
   public function factoryIndex(ManagerRegistry $registry): Response
   {
      /** @var ObjectRepository $repository */
      $repository = $registry->getManagerForClass(ManufactureProduct::class)->getRepository(ManufactureProduct::class);
      $all = $repository->findAll();
      usort($all, function(ManufactureProduct $product1, ManufactureProduct $product2) {
         return bccomp($product1->getId()->getValue(), $product2->getId()->getValue());
      });

      return $this->json($all);
   }
}
