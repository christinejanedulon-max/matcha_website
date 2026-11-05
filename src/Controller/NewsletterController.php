<?php

namespace App\Controller;

use App\Service\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsletterController extends AbstractController
{
    #[Route('/newsletter/subscribe', name: 'app_newsletter_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, StoreService $store): Response
    {
        $email = $request->request->get('email');
        $success = false;

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $success = $store->addSubscriber($email);
        }

        if ($request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json')) {
            return new JsonResponse([
                'success' => $success,
                'message' => $success ? 'Thank you for subscribing to Matcha Moments!' : 'Please enter a valid email address.'
            ]);
        }

        if ($success) {
            $this->addFlash('success', 'Thank you for subscribing to Matcha Moments updates!');
        } else {
            $this->addFlash('error', 'Please enter a valid email address.');
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_home'));
    }
}
