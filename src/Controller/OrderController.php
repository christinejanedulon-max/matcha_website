<?php

namespace App\Controller;

use App\Service\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    private function requireAuthenticatedUser(Request $request): ?Response
    {
        $session = $request->getSession();
        $userRole = $session?->get('user_role');

        if (!$userRole && !$this->isGranted('ROLE_USER')) {
            $session?->set('redirect_after_login', $this->generateUrl('app_order'));
            $this->addFlash('error', 'Please sign in or register an account to place your order.');
            return $this->redirectToRoute('app_login');
        }

        return null;
    }

    #[Route('/order', name: 'app_order')]
    public function index(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->requireAuthenticatedUser($request)) {
            return $redirect;
        }

        $products = $store->getProducts();

        return $this->render('order/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/order/place', name: 'app_order_place', methods: ['POST'])]
    public function place(Request $request, StoreService $store): Response
    {
        if ($redirect = $this->requireAuthenticatedUser($request)) {
            return $redirect;
        }

        $session = $request->getSession();
        $customerName = $request->request->get('customer_name') ?: $session->get('user_name', 'Valued Customer');
        $phone = $request->request->get('phone');
        $fulfillment = $request->request->get('fulfillment_method', 'Pickup');
        $deliveryAddress = $request->request->get('delivery_address');
        $paymentMethod = $request->request->get('payment_method', 'Cash');
        $referenceNumber = $request->request->get('reference_number');
        $notes = $request->request->get('notes');
        $itemsJson = $request->request->get('items_json', '[]');

        $items = json_decode($itemsJson, true);
        if (!is_array($items) || empty($items)) {
            // Fallback default single item if submitted via basic form
            $productId = (int) $request->request->get('product_id', 1);
            $product = $store->getProduct($productId) ?? $store->getProducts()[0];
            $items = [
                [
                    'name' => $product['name'],
                    'quantity' => (int) $request->request->get('quantity', 1),
                    'unitPrice' => $product['price'],
                    'options' => $request->request->get('options', 'Regular • 50% Sugar • Normal Ice'),
                ],
            ];
        }

        // Calculate total
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ((float) ($item['unitPrice'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }
        $deliveryFee = ($fulfillment === 'Delivery') ? 50.00 : 0.00;
        $totalAmount = $subtotal + $deliveryFee;

        $order = $store->createOrder([
            'customerName' => $customerName,
            'phone' => $phone,
            'totalAmount' => $totalAmount,
            'fulfillmentMethod' => $fulfillment,
            'deliveryAddress' => ($fulfillment === 'Delivery') ? $deliveryAddress : null,
            'paymentMethod' => $paymentMethod,
            'referenceNumber' => ($paymentMethod === 'GCash') ? $referenceNumber : null,
            'notes' => $notes,
            'items' => $items,
        ]);

        $this->addFlash('success', 'Your order ' . $order['orderNumber'] . ' has been received successfully!');

        return $this->redirectToRoute('app_order_confirmation', [
            'orderNumber' => $order['orderNumber'],
        ]);
    }

    #[Route('/order/confirmation/{orderNumber}', name: 'app_order_confirmation')]
    public function confirmation(string $orderNumber, Request $request, StoreService $store): Response
    {
        if ($redirect = $this->requireAuthenticatedUser($request)) {
            return $redirect;
        }

        $order = $store->getOrder($orderNumber);
        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('app_menu');
        }

        return $this->render('order/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}
