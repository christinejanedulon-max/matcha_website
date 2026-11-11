<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\StoreService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, UserRepository $userRepository, StoreService $store): Response
    {
        $session = $request->getSession();

        if ($request->isMethod('POST')) {
            $email = trim(strtolower((string) $request->request->get('email', '')));
            $password = (string) $request->request->get('password', '');
            $demoRole = $request->request->get('demo_role');

            // 1. One-Click Demo Admin
            if ($demoRole === 'admin' || ($email === 'admin@matchamoments.com' && ($password === 'admin123' || $demoRole === 'admin'))) {
                $session->set('user_role', 'admin');
                $session->set('user_name', 'Cafe Administrator');
                $session->set('user_email', 'admin@matchamoments.com');
                $session->set('user', [
                    'fullName' => 'Cafe Administrator',
                    'email' => 'admin@matchamoments.com',
                    'role' => 'admin',
                ]);

                $this->addFlash('success', 'Welcome back, Administrator! Admin Dashboard is now unlocked.');
                return $this->redirectToRoute('admin_dashboard');
            }

            // 2. One-Click Demo Staff (Barista)
            if ($demoRole === 'staff' || ($email === 'staff@matchamoments.com' && ($password === 'staff123' || $demoRole === 'staff'))) {
                $session->set('user_role', 'staff');
                $session->set('user_name', 'Matcha Lead Barista');
                $session->set('user_email', 'staff@matchamoments.com');
                $session->set('user', [
                    'fullName' => 'Matcha Lead Barista',
                    'email' => 'staff@matchamoments.com',
                    'role' => 'staff',
                ]);

                $this->addFlash('success', 'Welcome to the Staff Workspace, Barista! Ready to curate seasonal drinks.');
                return $this->redirectToRoute('staff_dashboard');
            }

            // 3. One-Click Demo Customer
            if ($demoRole === 'customer') {
                $name = $request->request->get('full_name') ?: 'Valued Customer';
                $session->set('user_role', 'customer');
                $session->set('user_name', $name);
                $session->set('user_email', 'customer@matchamoments.com');
                $session->set('user', [
                    'fullName' => $name,
                    'email' => 'customer@matchamoments.com',
                    'role' => 'customer',
                ]);

                $this->addFlash('success', "Welcome, {$name}! You can now customize and order your favorite matcha drinks.");
                return $this->redirectAfterLogin($session);
            }

            // 4. Database / Store User Lookup
            $authenticatedUser = null;
            try {
                $dbUser = $userRepository->findOneBy(['email' => $email]);
                if ($dbUser && password_verify($password, $dbUser->getPassword())) {
                    if (method_exists($dbUser, 'getStatus') && in_array($dbUser->getStatus(), ['disabled', 'archived'], true)) {
                        $this->addFlash('error', 'Your account has been deactivated. Please contact the cafe administrator.');
                        return $this->redirectToRoute('app_login');
                    }

                    $role = $dbUser->getRole() ?: 'customer';
                    $authenticatedUser = [
                        'fullName' => $dbUser->getFullName(),
                        'email' => $dbUser->getEmail(),
                        'role' => $role,
                    ];
                }
            } catch (\Throwable) {
                // Fallback to StoreService if DB is unreachable
            }

            if (!$authenticatedUser) {
                $storeUser = $store->getUserByEmail($email);
                if ($storeUser && isset($storeUser['password']) && password_verify($password, $storeUser['password'])) {
                    if (($storeUser['status'] ?? 'active') !== 'active') {
                        $this->addFlash('error', 'Your account has been deactivated. Please contact the cafe administrator.');
                        return $this->redirectToRoute('app_login');
                    }
                    $authenticatedUser = [
                        'fullName' => $storeUser['fullName'],
                        'email' => $storeUser['email'],
                        'role' => $storeUser['role'] ?? 'customer',
                    ];
                }
            }

            if ($authenticatedUser) {
                $role = $authenticatedUser['role'] ?? 'customer';
                $session->set('user_role', $role);
                $session->set('user_name', $authenticatedUser['fullName']);
                $session->set('user_email', $authenticatedUser['email']);
                $session->set('user', $authenticatedUser);
                $session->remove('registered_email');

                if ($role === 'admin') {
                    $this->addFlash('success', 'Welcome back, Administrator!');
                    return $this->redirectToRoute('admin_dashboard');
                }

                if ($role === 'staff') {
                    $this->addFlash('success', 'Welcome back, ' . $authenticatedUser['fullName'] . '! Staff Portal unlocked.');
                    return $this->redirectToRoute('staff_dashboard');
                }

                $this->addFlash('success', 'Signed in as ' . $authenticatedUser['fullName'] . '. Enjoy ordering your matcha moments!');
                return $this->redirectAfterLogin($session);
            }

            $this->addFlash('error', 'Invalid email or password. Please try again or create a new account.');
            return $this->redirectToRoute('app_login');
        }

        $prefillEmail = $session->get('registered_email', '');

        return $this->render('auth/login.html.twig', [
            'activeTab' => $request->query->get('tab', 'login'),
            'prefillEmail' => $prefillEmail,
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $session = $request->getSession();

        if ($request->isMethod('POST')) {
            $fullName = trim((string) $request->request->get('full_name', ''));
            $email = trim(strtolower((string) $request->request->get('email', '')));
            $password = (string) $request->request->get('password', '');
            $confirmPassword = (string) $request->request->get('confirm_password', '');

            if ($fullName === '' || $email === '' || $password === '') {
                $this->addFlash('error', 'Please fill in all required fields to register.');
                return $this->redirectToRoute('app_register');
            }

            if (strlen($password) < 6) {
                $this->addFlash('error', 'Password must be at least 6 characters long.');
                return $this->redirectToRoute('app_register');
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match. Please re-enter your password.');
                return $this->redirectToRoute('app_register');
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Save to database (with session fallback)
            try {
                $existing = $userRepository->findOneBy(['email' => $email]);
                if ($existing) {
                    $this->addFlash('error', 'An account with this email already exists. Please sign in instead.');
                    $session->set('registered_email', $email);
                    return $this->redirectToRoute('app_login');
                }

                $user = new User();
                $user->setFullName($fullName);
                $user->setEmail($email);
                $user->setRole('customer');
                $user->setRoles(['ROLE_USER']);
                $user->setPassword($hashedPassword);

                $em->persist($user);
                $em->flush();
            } catch (\Throwable) {
                $sessionUsers = $session->get('registered_users', []);
                if (isset($sessionUsers[$email])) {
                    $this->addFlash('error', 'An account with this email already exists. Please sign in.');
                    $session->set('registered_email', $email);
                    return $this->redirectToRoute('app_login');
                }
                $sessionUsers[$email] = [
                    'fullName' => $fullName,
                    'email' => $email,
                    'role' => 'customer',
                    'password' => $hashedPassword,
                ];
                $session->set('registered_users', $sessionUsers);
            }

            // Store registered email so the login form pre-fills it automatically
            $session->set('registered_email', $email);
            $this->addFlash('success', "Account created for {$fullName}! Please sign in below to start ordering.");

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/login.html.twig', [
            'activeTab' => 'register',
            'prefillEmail' => '',
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->remove('user_role');
        $session->remove('user_name');
        $session->remove('user_email');
        $session->remove('user');

        $this->addFlash('success', 'You have been signed out successfully. Please sign in to place a new order.');
        return $this->redirectToRoute('app_login');
    }

    private function redirectAfterLogin($session): Response
    {
        $target = $session->get('redirect_after_login');
        if ($target) {
            $session->remove('redirect_after_login');
            return $this->redirect($target);
        }

        return $this->redirectToRoute('app_menu');
    }
}
