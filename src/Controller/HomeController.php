<?php

namespace App\Controller;

use App\Service\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(StoreService $store): Response
    {
        $products = $store->getProducts();
        $grouped = $store->getGroupedProducts();
        $reviews = $store->getReviews();

        return $this->render('home/index.html.twig', [
            'products'    => $products,
            'grouped'     => $grouped,
            'bestsellers' => array_slice($products, 0, 4), // Exactly 4 bestsellers as requested
            'reviews'     => array_slice($reviews, 0, 3), // Top 3 reviews matching the mockup
        ]);
    }
}
