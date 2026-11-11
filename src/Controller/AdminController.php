<?php

namespace App\Controller;

use App\Service\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    private function checkAdmin(Request $request): ?Response
    {
        $session = $request->getSession();
        $userRole = $session?->get('user_role');

        if (!$userRole) {
            $this->addFlash('error', 'Please sign in with administrator credentials.');
            return $this->redirectToRoute('app_login');
        }

        // Strict 403 Forbidden Access Denial for non-admin users (including Staff)
        if ($userRole !== 'admin') {
            return new Response(
                $this->renderView('security/access_denied.html.twig', [
                    'message' => 'HTTP 403 Forbidden: Staff members and customers are strictly restricted from accessing the Administrator Dashboard and User Management systems.'
                ]),
                Response::HTTP_FORBIDDEN
            );
        }

        return null;
    }

    #[Route('', name: 'admin_dashboard')]
    public function dashboard(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $stats = $store->getDashboardStats();
        $products = $store->getProducts();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'products' => $products,
        ]);
    }

    #[Route('/orders', name: 'admin_orders')]
    public function orders(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $orders = $store->getOrders();
        $statusFilter = $request->query->get('status', 'all');

        if ($statusFilter !== 'all') {
            $orders = array_filter($orders, function ($o) use ($statusFilter) {
                return strcasecmp($o['status'], $statusFilter) === 0;
            });
        }

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
            'currentFilter' => $statusFilter,
            'stats' => $store->getDashboardStats(),
        ]);
    }

    #[Route('/orders/{orderNumber}/status', name: 'admin_order_status', methods: ['POST'])]
    public function updateOrderStatus(string $orderNumber, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $newStatus = $request->request->get('status');
        if ($newStatus) {
            $store->updateOrderStatus($orderNumber, $newStatus);
            $this->addFlash('success', "Order {$orderNumber} status updated to {$newStatus}.");
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('admin_orders'));
    }

    #[Route('/products', name: 'admin_products')]
    public function products(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $products = $store->getProducts();

        return $this->render('admin/products.html.twig', [
            'products' => $products,
            'stats' => $store->getDashboardStats(),
        ]);
    }

    #[Route('/products/add', name: 'admin_product_add', methods: ['POST'])]
    public function addProduct(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $name = $request->request->get('name');
        $category = $request->request->get('category');
        $price = $request->request->get('price');
        $stock = (int) $request->request->get('stockQuantity', 30);
        $imageUrl = $request->request->get('imageUrl', 'images/latte.png');
        $description = $request->request->get('description', '');

        if ($name && $price) {
            $session = $request->getSession();
            $store->addProduct([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'stockQuantity' => $stock,
                'imageUrl' => $imageUrl,
                'description' => $description,
            ], [
                'email' => $session?->get('user_email', 'admin@matchamoments.com'),
                'fullName' => $session?->get('user_name', 'Cafe Administrator'),
                'role' => 'admin',
            ]);
            $this->addFlash('success', "New drink '{$name}' successfully added to the menu.");
        } else {
            $this->addFlash('error', "Please provide a drink name and valid price.");
        }

        return $this->redirectToRoute('admin_products');
    }

    #[Route('/products/{id}/stock', name: 'admin_product_stock', methods: ['POST'])]
    public function updateStock(int $id, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $newStock = (int) $request->request->get('stock');
        $store->updateProductStock($id, $newStock);
        $this->addFlash('success', "Product stock updated.");

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('admin_products'));
    }

    #[Route('/products/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function deleteProduct(int $id, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $store->deleteProduct($id);
        $this->addFlash('success', "Product removed from menu.");

        return $this->redirectToRoute('admin_products');
    }

    #[Route('/reviews', name: 'admin_reviews')]
    public function reviews(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $reviews = $store->getReviews();

        return $this->render('admin/reviews.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/reviews/{id}/delete', name: 'admin_review_delete', methods: ['POST'])]
    public function deleteReview(int $id, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $store->deleteReview($id);
        $this->addFlash('success', "Review deleted.");

        return $this->redirectToRoute('admin_reviews');
    }

    #[Route('/subscribers', name: 'admin_subscribers')]
    public function subscribers(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $subscribers = $store->getSubscribers();

        return $this->render('admin/subscribers.html.twig', [
            'subscribers' => $subscribers,
        ]);
    }

    // =====================================================
    // USER & STAFF MANAGEMENT (CRUD) — RUBRIC CATEGORY 3
    // =====================================================

    #[Route('/users', name: 'admin_users')]
    public function users(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $allUsers = $store->getUsers();
        $search = strtolower(trim((string) $request->query->get('q', '')));
        $roleFilter = strtolower(trim((string) $request->query->get('role', 'all')));

        $filteredUsers = array_filter($allUsers, function ($u) use ($search, $roleFilter) {
            $matchesSearch = true;
            if ($search !== '') {
                $name = strtolower($u['fullName'] ?? '');
                $email = strtolower($u['email'] ?? '');
                $matchesSearch = (str_contains($name, $search) || str_contains($email, $search));
            }

            $matchesRole = true;
            if ($roleFilter !== 'all') {
                $matchesRole = (($u['role'] ?? '') === $roleFilter);
            }

            return $matchesSearch && $matchesRole;
        });

        return $this->render('admin/users.html.twig', [
            'users' => array_values($filteredUsers),
            'stats' => $store->getDashboardStats(),
            'currentSearch' => $search,
            'currentRole' => $roleFilter,
        ]);
    }

    #[Route('/users/create', name: 'admin_user_create', methods: ['POST'])]
    public function createUser(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $fullName = trim((string) $request->request->get('fullName', ''));
        $email = strtolower(trim((string) $request->request->get('email', '')));
        $role = $request->request->get('role', 'staff');
        $status = $request->request->get('status', 'active');
        $password = (string) $request->request->get('password', '');

        if ($fullName === '' || $email === '' || $password === '') {
            $this->addFlash('error', 'All fields (Full Name, Email, Password) are required.');
            return $this->redirectToRoute('admin_users');
        }

        if (strlen($password) < 6) {
            $this->addFlash('error', 'Password must be at least 6 characters.');
            return $this->redirectToRoute('admin_users');
        }

        // Duplicate check
        if ($store->getUserByEmail($email)) {
            $this->addFlash('error', "An account with email {$email} already exists.");
            return $this->redirectToRoute('admin_users');
        }

        $store->createUser([
            'fullName' => $fullName,
            'email' => $email,
            'role' => $role,
            'status' => $status,
            'password' => $password,
        ]);

        $this->addFlash('success', "New user account '{$fullName}' ({$role}) successfully created.");
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/edit', name: 'admin_user_edit', methods: ['POST'])]
    public function editUser(int $id, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $fullName = trim((string) $request->request->get('fullName', ''));
        $email = strtolower(trim((string) $request->request->get('email', '')));
        $role = $request->request->get('role', 'staff');
        $status = $request->request->get('status', 'active');
        $newPassword = (string) $request->request->get('password', '');

        $data = [
            'fullName' => $fullName,
            'email' => $email,
            'role' => $role,
            'status' => $status,
        ];

        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                $this->addFlash('error', 'Reset password must be at least 6 characters.');
                return $this->redirectToRoute('admin_users');
            }
            $data['password'] = $newPassword;
        }

        $updated = $store->updateUser($id, $data);
        if ($updated) {
            $this->addFlash('success', "User account updated successfully.");
        } else {
            $this->addFlash('error', "User not found.");
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(int $id, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkAdmin($request)) return $redirect;

        $user = $store->getUser($id);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users');
        }

        if (strtolower($user['email']) === 'admin@matchamoments.com') {
            $this->addFlash('error', 'The primary administrator account cannot be deleted.');
            return $this->redirectToRoute('admin_users');
        }

        $deleted = $store->deleteUser($id);
        if ($deleted) {
            $this->addFlash('success', "User '{$user['fullName']}' ({$user['email']}) deleted with confirmation.");
        } else {
            $this->addFlash('error', "Could not delete user.");
        }

        return $this->redirectToRoute('admin_users');
    }
}
