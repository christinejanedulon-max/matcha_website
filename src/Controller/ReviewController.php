<?php

namespace App\Controller;

use App\Service\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReviewController extends AbstractController
{
    #[Route('/reviews', name: 'app_reviews', methods: ['GET'])]
    public function index(StoreService $store): Response
    {
        $reviews = $store->getReviews();

        return $this->render('reviews/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/reviews/new', name: 'app_reviews_new', methods: ['POST'])]
    public function create(Request $request, StoreService $store): Response
    {
        $name = $request->request->get('customer_name');
        $rating = (int) $request->request->get('rating', 5);
        $comment = $request->request->get('comment');

        if (!empty($name) && !empty($comment)) {
            $store->addReview([
                'customerName' => $name,
                'rating' => $rating,
                'comment' => $comment
            ]);
            $this->addFlash('success', 'Thank you for sharing your matcha experience!');
        } else {
            $this->addFlash('error', 'Please provide both your name and review comment.');
        }

        return $this->redirectToRoute('app_reviews');
    }
}
