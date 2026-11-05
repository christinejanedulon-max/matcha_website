<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test-db', name: 'app_test_db')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Use Doctrine to fetch all Products from your MySQL database
        $products = $entityManager->getRepository(Product::class)->findAll();

        if (!$products) {
            return new Response('<h1>No products found in the database!</h1>');
        }

        // Format the products into a simple HTML list
        $html = '<h1>🍵 Matcha Moments Menu (Fetched via Doctrine)</h1><ul>';
        foreach ($products as $product) {
            $html .= sprintf(
                '<li><strong>%s</strong> (%s) - ₱%s</li>',
                $product->getName(),
                $product->getCategory(),
                $product->getPrice()
            );
        }
        $html .= '</ul>';

        return new Response("<html><body>$html</body></html>");
    }
}
