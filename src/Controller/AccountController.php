<?php

namespace App\Controller;

use App\Service\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/account')]
final class AccountController extends AbstractController
{
    private function requireCustomerUser(Request $request): ?Response
    {
        $session = $request->getSession();
        $userRole = $session?->get('user_role');

        if (!$userRole && !$this->isGranted('ROLE_USER')) {
            $session?->set('redirect_after_login', $request->getRequestUri());
            $this->addFlash('error', 'Please sign in to view your order history and tracking.');
            return $this->redirectToRoute('app_login');
        }

        return null;
    }

    #[Route('/orders', name: 'app_account_orders')]
    public function orders(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->requireCustomerUser($request)) {
            return $redirect;
        }

        $session = $request->getSession();
        $userEmail = (string) $session?->get('user_email', '');
        $isAdmin = ($session?->get('user_role') === 'admin');

        $allOrders = $isAdmin ? $store->getOrders() : $store->getOrdersForEmail($userEmail);

        $activeOrders = [];
        $pastOrders = [];

        foreach ($allOrders as $order) {
            $status = strtolower($order['status'] ?? 'pending');
            if (in_array($status, ['completed', 'cancelled'], true)) {
                $pastOrders[] = $order;
            } else {
                $activeOrders[] = $order;
            }
        }

        return $this->render('account/orders.html.twig', [
            'activeOrders' => $activeOrders,
            'pastOrders' => $pastOrders,
            'totalCount' => count($allOrders),
        ]);
    }

    #[Route('/orders/{orderNumber}', name: 'app_account_order_track')]
    public function track(string $orderNumber, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->requireCustomerUser($request)) {
            return $redirect;
        }

        $session = $request->getSession();
        $userEmail = strtolower((string) $session?->get('user_email', ''));
        $isAdmin = ($session?->get('user_role') === 'admin');

        $order = $store->getOrder($orderNumber);
        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        // Ownership check
        $orderEmail = strtolower((string) ($order['customerEmail'] ?? ''));
        if (!$isAdmin && $orderEmail !== '' && $orderEmail !== $userEmail) {
            throw $this->createNotFoundException('Order not found.');
        }

        $fulfillment = $order['fulfillmentMethod'] ?? 'Pickup';
        $status = strtolower($order['status'] ?? 'pending');

        $steps = [
            ['key' => 'pending',   'title' => 'Order Received',     'desc' => 'We received your matcha order', 'icon' => 'bi-receipt'],
            ['key' => 'preparing', 'title' => 'Handcrafting Drinks', 'desc' => 'Freshly stone-ground & whisked at 80°C', 'icon' => 'bi-cup-hot'],
            [
                'key' => 'ready',
                'title' => ($fulfillment === 'Delivery') ? 'Out for Delivery' : 'Ready for Pickup',
                'desc' => ($fulfillment === 'Delivery') ? 'Cold-locked and en route' : 'Packaged and waiting on the counter',
                'icon' => ($fulfillment === 'Delivery') ? 'bi-truck' : 'bi-bag-check'
            ],
            ['key' => 'completed', 'title' => 'Order Completed',   'desc' => 'Savor your pure matcha moments', 'icon' => 'bi-check2-circle'],
        ];

        $currentStepIndex = match ($status) {
            'pending' => 0,
            'preparing' => 1,
            'ready' => 2,
            'completed' => 3,
            default => -1, // Cancelled or other
        };

        return $this->render('account/track.html.twig', [
            'order' => $order,
            'steps' => $steps,
            'currentStepIndex' => $currentStepIndex,
            'isCancelled' => ($status === 'cancelled'),
        ]);
    }

    #[Route('/orders/{orderNumber}/status', name: 'app_account_order_status')]
    public function status(string $orderNumber, Request $request, StoreService $store): JsonResponse
    {
        $session = $request->getSession();
        $userEmail = strtolower((string) $session?->get('user_email', ''));
        $isAdmin = ($session?->get('user_role') === 'admin');

        $order = $store->getOrder($orderNumber);
        if (!$order) {
            return $this->json(['error' => 'Not found'], 404);
        }

        $orderEmail = strtolower((string) ($order['customerEmail'] ?? ''));
        if (!$isAdmin && $orderEmail !== '' && $orderEmail !== $userEmail) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $fulfillment = $order['fulfillmentMethod'] ?? 'Pickup';
        $status = $order['status'] ?? 'Pending';
        $stLower = strtolower($status);

        $statusLabel = match ($stLower) {
            'pending' => 'Order Placed',
            'preparing' => 'Preparing Your Matcha',
            'ready' => ($fulfillment === 'Delivery' ? 'Out for Delivery' : 'Ready for Pickup'),
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => $status,
        };

        $isTerminal = in_array($stLower, ['completed', 'cancelled'], true);

        return $this->json([
            'orderNumber' => $order['orderNumber'],
            'status' => $status,
            'statusLabel' => $statusLabel,
            'statusUpdatedAt' => $order['statusUpdatedAt'] ?? $order['orderDate'],
            'isTerminal' => $isTerminal,
        ]);
    }

    #[Route('/profile', name: 'app_account_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, StoreService $store): Response
    {
        $session = $request->getSession();
        $userRole = $session?->get('user_role');
        if (!$userRole) {
            $this->addFlash('error', 'Please sign in to view your profile.');
            return $this->redirectToRoute('app_login');
        }

        $userEmail = (string) $session?->get('user_email', '');
        $user = $store->getUserByEmail($userEmail) ?? [
            'id' => 0,
            'fullName' => $session?->get('user_name', 'Member'),
            'email' => $userEmail,
            'role' => $userRole,
            'status' => 'active',
            'createdAt' => date('Y-m-d H:i:s'),
        ];

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            if ($action === 'update_profile') {
                $fullName = trim((string) $request->request->get('full_name', ''));
                if ($fullName !== '') {
                    $session->set('user_name', $fullName);
                    if (!empty($user['id'])) {
                        $store->updateUser($user['id'], ['fullName' => $fullName]);
                    }
                    $this->addFlash('success', 'Profile information updated successfully.');
                } else {
                    $this->addFlash('error', 'Full name cannot be blank.');
                }
                return $this->redirectToRoute('app_account_profile');
            }

            if ($action === 'change_password') {
                $currentPassword = (string) $request->request->get('current_password', '');
                $newPassword = (string) $request->request->get('new_password', '');
                $confirmPassword = (string) $request->request->get('confirm_password', '');

                if (strlen($newPassword) < 6) {
                    $this->addFlash('error', 'New password must be at least 6 characters long.');
                    return $this->redirectToRoute('app_account_profile');
                }

                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', 'New passwords do not match. Please re-enter.');
                    return $this->redirectToRoute('app_account_profile');
                }

                $updated = $store->updateUserPassword($userEmail, $currentPassword, $newPassword);
                if ($updated) {
                    $this->addFlash('success', 'Your password has been changed securely.');
                } else {
                    $this->addFlash('error', 'Current password is incorrect. Please verify and try again.');
                }
                return $this->redirectToRoute('app_account_profile');
            }
        }

        return $this->render('account/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
