<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $session = $request->getSession();

        if ($request->isMethod('POST')) {
            $email = trim(strtolower($request->request->get('email', '')));
            $password = $request->request->get('password', '');
            $demoRole = $request->request->get('demo_role');

            if ($demoRole === 'admin' || str_contains($email, 'admin')) {
                $session->set('user_role', 'admin');
                $session->set('user_name', 'Cafe Administrator');
                $session->set('user_email', $email ?: 'admin@matchamoments.com');
                $session->set('user', [
                    'fullName' => 'Cafe Administrator',
                    'email' => $email ?: 'admin@matchamoments.com',
                    'role' => 'admin'
                ]);

                $this->addFlash('success', 'Welcome back, Administrator! Admin Dashboard is now unlocked.');
                return $this->redirectToRoute('admin_dashboard');
            } else {
                // Logged in as regular customer / user
                $name = $request->request->get('full_name') ?: 'Matcha Lover';
                $session->set('user_role', 'customer');
                $session->set('user_name', $name);
                $session->set('user_email', $email ?: 'customer@matchamoments.com');
                $session->set('user', [
                    'fullName' => $name,
                    'email' => $email ?: 'customer@matchamoments.com',
                    'role' => 'customer'
                ]);

                $this->addFlash('success', 'Logged in successfully as Customer. Enjoy your matcha moments!');
                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('auth/login.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->remove('user_role');
        $session->remove('user_name');
        $session->remove('user_email');
        $session->remove('user');

        $this->addFlash('success', 'You have been signed out.');
        return $this->redirectToRoute('app_home');
    }
}
