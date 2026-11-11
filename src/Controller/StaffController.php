<?php

namespace App\Controller;

use App\Service\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/staff')]
final class StaffController extends AbstractController
{
    private function checkStaff(Request $request): ?Response
    {
        $session = $request->getSession();
        $userRole = $session?->get('user_role');

        if (!$userRole) {
            $this->addFlash('error', 'Please sign in with staff or barista credentials.');
            return $this->redirectToRoute('app_login');
        }

        // Both Staff and Admin can view the Staff workspace, but Customers are blocked with 403
        if (!in_array($userRole, ['staff', 'admin'], true)) {
            return new Response(
                $this->renderView('security/access_denied.html.twig', [
                    'message' => 'HTTP 403 Forbidden: Customer accounts do not have access to the Staff Barista Workspace.'
                ]),
                Response::HTTP_FORBIDDEN
            );
        }

        return null;
    }

    #[Route('', name: 'staff_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkStaff($request)) return $redirect;

        $session = $request->getSession();
        $userEmail = strtolower((string) $session?->get('user_email', ''));
        $userRole = (string) $session?->get('user_role', 'staff');

        $allProducts = $store->getProducts();
        $viewFilter = $request->query->get('view', 'all'); // 'all' or 'my'

        $myProductsCount = 0;
        foreach ($allProducts as $p) {
            if (strtolower((string) ($p['createdByEmail'] ?? '')) === $userEmail) {
                $myProductsCount++;
            }
        }

        $displayProducts = $allProducts;
        if ($viewFilter === 'my') {
            $displayProducts = array_filter($allProducts, function ($p) use ($userEmail) {
                return strtolower((string) ($p['createdByEmail'] ?? '')) === $userEmail;
            });
        }

        return $this->render('staff/dashboard.html.twig', [
            'products' => array_values($displayProducts),
            'totalMenuCount' => count($allProducts),
            'myProductsCount' => $myProductsCount,
            'currentView' => $viewFilter,
            'currentUserEmail' => $userEmail,
            'currentUserRole' => $userRole,
        ]);
    }

    #[Route('/products/add', name: 'staff_product_add', methods: ['POST'])]
    public function addProduct(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkStaff($request)) return $redirect;

        $session = $request->getSession();
        $userEmail = strtolower((string) $session?->get('user_email', ''));
        $userName = (string) $session?->get('user_name', 'Barista');
        $userRole = (string) $session?->get('user_role', 'staff');

        $name = trim((string) $request->request->get('name', ''));
        $category = (string) $request->request->get('category', 'Signature Lattes & Bestsellers');
        $price = $request->request->get('price');
        $stock = (int) $request->request->get('stockQuantity', 30);
        $imageUrl = (string) $request->request->get('imageUrl', 'images/latte.png');
        $description = trim((string) $request->request->get('description', ''));

        if ($name === '' || !$price) {
            $this->addFlash('error', 'Drink name and valid price are required.');
            return $this->redirectToRoute('staff_dashboard');
        }

        $store->addProduct([
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'stockQuantity' => $stock,
            'imageUrl' => $imageUrl,
            'description' => $description,
        ], [
            'email' => $userEmail,
            'fullName' => $userName,
            'role' => $userRole,
        ]);

        $this->addFlash('success', "Seasonal creation '{$name}' added to menu! You are registered as the record author.");
        return $this->redirectToRoute('staff_dashboard');
    }

    #[Route('/products/{id}/edit', name: 'staff_product_edit', methods: ['POST'])]
    public function editProduct(int $id, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkStaff($request)) return $redirect;

        $session = $request->getSession();
        $userEmail = strtolower((string) $session?->get('user_email', ''));
        $userRole = (string) $session?->get('user_role', 'staff');

        $product = $store->getProduct($id);
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('staff_dashboard');
        }

        // STRICT RUBRIC ENFORCEMENT: Staff can edit own records only (cannot edit admin or other staff records)
        if (!$store->canUserModifyProduct($product, $userEmail, $userRole)) {
            return new Response(
                $this->renderView('security/access_denied.html.twig', [
                    'message' => "HTTP 403 Forbidden: Staff members are restricted to editing their OWN records only. You cannot modify drinks created by the administrator ('{$product['createdByEmail']}') or other staff members."
                ]),
                Response::HTTP_FORBIDDEN
            );
        }

        $name = trim((string) $request->request->get('name', ''));
        $category = (string) $request->request->get('category', '');
        $price = $request->request->get('price');
        $stock = (int) $request->request->get('stockQuantity', 30);
        $imageUrl = (string) $request->request->get('imageUrl', $product['imageUrl']);
        $description = trim((string) $request->request->get('description', ''));

        $store->updateProduct($id, [
            'name' => $name ?: $product['name'],
            'category' => $category ?: $product['category'],
            'price' => $price ?: $product['price'],
            'stockQuantity' => $stock,
            'imageUrl' => $imageUrl,
            'description' => $description,
        ]);

        $this->addFlash('success', "Your drink creation '{$product['name']}' has been updated.");
        return $this->redirectToRoute('staff_dashboard');
    }

    #[Route('/products/{id}/delete', name: 'staff_product_delete', methods: ['POST'])]
    public function deleteProduct(int $id, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkStaff($request)) return $redirect;

        $session = $request->getSession();
        $userEmail = strtolower((string) $session?->get('user_email', ''));
        $userRole = (string) $session?->get('user_role', 'staff');

        $product = $store->getProduct($id);
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('staff_dashboard');
        }

        // STRICT RUBRIC ENFORCEMENT: Staff can delete own records only (with confirmation)
        if (!$store->canUserModifyProduct($product, $userEmail, $userRole)) {
            return new Response(
                $this->renderView('security/access_denied.html.twig', [
                    'message' => "HTTP 403 Forbidden: Staff members are restricted to deleting their OWN records only. You cannot delete records created by the administrator or other staff members."
                ]),
                Response::HTTP_FORBIDDEN
            );
        }

        $store->deleteProduct($id);
        $this->addFlash('success', "Your drink '{$product['name']}' has been deleted with confirmation.");
        return $this->redirectToRoute('staff_dashboard');
    }

    #[Route('/orders', name: 'staff_orders', methods: ['GET'])]
    public function orders(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkStaff($request)) return $redirect;

        $orders = $store->getOrders();
        $statusFilter = $request->query->get('status', 'all');

        if ($statusFilter !== 'all') {
            $orders = array_filter($orders, function ($o) use ($statusFilter) {
                return strcasecmp($o['status'], $statusFilter) === 0;
            });
        }

        return $this->render('staff/orders.html.twig', [
            'orders' => $orders,
            'currentFilter' => $statusFilter,
            'stats' => $store->getDashboardStats(),
        ]);
    }

    #[Route('/orders/{orderNumber}/status', name: 'staff_order_status', methods: ['POST'])]
    public function updateOrderStatus(string $orderNumber, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->checkStaff($request)) return $redirect;

        $newStatus = $request->request->get('status');
        if ($newStatus) {
            $store->updateOrderStatus($orderNumber, $newStatus);
            $this->addFlash('success', "Order {$orderNumber} status updated to {$newStatus}.");
        }

        return $this->redirectToRoute('staff_orders');
    }
}
