<?php

namespace App\Controller;

use App\Service\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MenuController extends AbstractController
{
    #[Route('/menu', name: 'app_menu')]
    public function index(StoreService $store): Response
    {
        $products = $store->getProducts();
        $grouped = $store->getGroupedProducts();

        return $this->render('menu/index.html.twig', [
            'grouped'       => $grouped,
            'products'      => $products,
            'totalProducts' => count($products),
        ]);
    }
}
