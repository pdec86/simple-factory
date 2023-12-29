<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
   #[Route('/', name: 'catalogue_dashboard_index', methods: ['GET'])]
   public function index(string $fontsPath, string $ocrBLikeFontName): Response
   {
      return new Response('');
   }
}
