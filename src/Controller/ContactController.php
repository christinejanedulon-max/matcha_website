<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/contact/send', name: 'app_contact_send', methods: ['POST'])]
    public function send(Request $request): Response
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $message = $request->request->get('message');

        if (!empty($name) && !empty($email) && !empty($message)) {
            $this->addFlash('success', 'Thank you, ' . htmlspecialchars($name) . '! Your message has been sent. Our team will get back to you shortly.');
        } else {
            $this->addFlash('error', 'Please fill in all required fields.');
        }

        return $this->redirectToRoute('app_contact');
    }
}
