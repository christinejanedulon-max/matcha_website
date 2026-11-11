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
        $userEmail = strtolower((string) $session?->get('user_email', ''));

        // Strict Single-Admin Enforcement: Only the designated single administrator can access
        if ($userRole !== 'admin' || $userEmail !== 'admin@matchamoments.com') {
            $this->addFlash('error', 'Access restricted. Only the single authorized store administrator can access the admin dashboard.');
            return $this->redirectToRoute('app_login');
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
            $store->addProduct([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'stockQuantity' => $stock,
                'imageUrl' => $imageUrl,
                'description' => $description,
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
}
