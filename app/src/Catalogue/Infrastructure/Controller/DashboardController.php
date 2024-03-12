<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Controller;

use App\Common\Domain\Model\ValueObject\Dimensions;
use App\Factory\Domain\Model\ManufactureProduct;
use App\Warehouse\Application\Service\WarehouseManager;
use App\Warehouse\Domain\Model\StorageSpace;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
   #[Route('/', name: 'catalogue_dashboard_index', methods: ['GET'])]
   public function catalogueIndex(WarehouseManager $warehouseManager): Response
   {
      $storageName = 'Test storage ' . random_int(1, 1000);
      $areaName = 'Test area name ' . random_int(1, 1000);
      $shelf = (string) random_int(1, 10);
      $dimensions = new Dimensions((string) random_int(50, 200), (string) random_int(100, 200), (string) random_int(10, 50));
      $warehouseManager->createStorageSpace($storageName);
      $warehouseManager->createProductStorageSpace($storageName, $areaName, $shelf, $dimensions);

      return $this->redirectToRoute('catalogue_product_index');
   }

   #[Route('/factory', name: 'factory_index', methods: ['GET'])]
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

   #[Route('/warehouse', name: 'warehouse_index', methods: ['GET'])]
   public function warehouseIndex(ManagerRegistry $registry): Response
   {
      /** @var ObjectRepository $repository */
      $repository = $registry->getManagerForClass(StorageSpace::class)->getRepository(StorageSpace::class);
      $all = $repository->findAll();

      return $this->json($all);
   }
}
