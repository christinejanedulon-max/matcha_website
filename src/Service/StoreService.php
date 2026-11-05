<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\NewsletterSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class StoreService
{
    private const DEFAULT_PRODUCTS = [
        [
            'id' => 1,
            'name' => 'Tropical Matcha',
            'category' => 'Signature Lattes & Bestsellers',
            'price' => '185.00',
            'stockQuantity' => 50,
            'imageUrl' => 'images/tropical.png',
            'description' => 'Refreshing tropical fruit notes infused with ceremonial Japanese matcha.'
        ],
        [
            'id' => 2,
            'name' => 'Matcha Latte',
            'category' => 'Signature Lattes & Bestsellers',
            'price' => '170.00',
            'stockQuantity' => 50,
            'imageUrl' => 'images/latte.png',
            'description' => 'Classic ceremonial grade matcha layered with velvety steamed fresh milk.'
        ],
        [
            'id' => 3,
            'name' => 'Matcha Cloud',
            'category' => 'Signature Lattes & Bestsellers',
            'price' => '195.00',
            'stockQuantity' => 40,
            'imageUrl' => 'images/cloud.png',
            'description' => 'Silky matcha topped with a light, fluffy sea salt cheese foam cloud.'
        ],
        [
            'id' => 4,
            'name' => 'Choco Matcha Fusion',
            'category' => 'Signature Lattes & Bestsellers',
            'price' => '180.00',
            'stockQuantity' => 45,
            'imageUrl' => 'images/choco.png',
            'description' => 'Decadent dark chocolate melted beneath vibrant, whisked Uji matcha.'
        ],
        [
            'id' => 5,
            'name' => 'Vanilla Oat Matcha',
            'category' => 'Signature Lattes & Bestsellers',
            'price' => '185.00',
            'stockQuantity' => 30,
            'imageUrl' => 'images/vanilla.png',
            'description' => 'Creamy organic oat milk, real Madagascar vanilla, and pure matcha.'
        ],
        [
            'id' => 6,
            'name' => 'Strawberry Matcha',
            'category' => 'Fruit & Specialty Fusions',
            'price' => '190.00',
            'stockQuantity' => 35,
            'imageUrl' => 'images/strawberry.png',
            'description' => 'Handmade real strawberry compote topped with creamy iced matcha latte.'
        ],
        [
            'id' => 7,
            'name' => 'Mango Cloud Matcha',
            'category' => 'Fruit & Specialty Fusions',
            'price' => '195.00',
            'stockQuantity' => 30,
            'imageUrl' => 'images/mango.png',
            'description' => 'Sweet ripe Philippine mangoes blended with our signature matcha cloud.'
        ],
        [
            'id' => 8,
            'name' => 'Coconut Matcha',
            'category' => 'Fruit & Specialty Fusions',
            'price' => '180.00',
            'stockQuantity' => 25,
            'imageUrl' => 'images/coconut.png',
            'description' => 'Pure tender coconut water layered with earthy ceremonial matcha.'
        ],
        [
            'id' => 9,
            'name' => 'Peach Blossom Matcha',
            'category' => 'Fruit & Specialty Fusions',
            'price' => '185.00',
            'stockQuantity' => 25,
            'imageUrl' => 'images/peach.png',
            'description' => 'Delicate white peach puree perfectly balanced with fragrant green tea.'
        ],
        [
            'id' => 10,
            'name' => 'Sea Salt Matcha',
            'category' => 'Fruit & Specialty Fusions',
            'price' => '190.00',
            'stockQuantity' => 30,
            'imageUrl' => 'images/sea.png',
            'description' => 'Savory whipped sea salt foam elevated over rich iced matcha.'
        ],
        [
            'id' => 11,
            'name' => 'Ube Cake Matcha',
            'category' => 'Premium Cake Matcha Creations',
            'price' => '210.00',
            'stockQuantity' => 20,
            'imageUrl' => 'images/ube.png',
            'description' => 'Velvety purple yam ube cream crowned with premium matcha infusion.'
        ],
        [
            'id' => 12,
            'name' => 'Pistachio Matcha',
            'category' => 'Premium Cake Matcha Creations',
            'price' => '175.00',
            'stockQuantity' => 25,
            'imageUrl' => 'images/pis.png',
            'description' => 'Roasted Sicilian pistachio paste and velvety rich matcha cream.'
        ],
        [
            'id' => 13,
            'name' => 'Tiramisu Cake Matcha',
            'category' => 'Premium Cake Matcha Creations',
            'price' => '175.00',
            'stockQuantity' => 20,
            'imageUrl' => 'images/tira.png',
            'description' => 'Mascarpone cream, ladyfinger sponge notes, and dusted matcha powder.'
        ],
        [
            'id' => 14,
            'name' => 'Cheesecake Matcha',
            'category' => 'Premium Cake Matcha Creations',
            'price' => '160.00',
            'stockQuantity' => 25,
            'imageUrl' => 'images/cake.png',
            'description' => 'Creamy Japanese baked cheesecake flavor blended with authentic matcha.'
        ],
    ];

    private const DEFAULT_REVIEWS = [
        [
            'id' => 1,
            'customerName' => 'Jola Mae Nepomuceno',
            'rating' => 5,
            'comment' => 'The best matcha I\'ve ever had! Super creamy and not too sweet. The presentation is so aesthetic!',
            'createdAt' => '2026-10-15 14:22:00'
        ],
        [
            'id' => 2,
            'customerName' => 'Leslie Kate Ocsiano',
            'rating' => 5,
            'comment' => 'I love their Tropical Matcha! So refreshing and perfect for hot days. Definitely my daily comfort drink.',
            'createdAt' => '2026-10-14 11:05:00'
        ],
        [
            'id' => 3,
            'customerName' => 'Elairay Papeleras',
            'rating' => 5,
            'comment' => 'Highly recommended! Drinks do have premium and real matcha taste, not powdery or artificial.',
            'createdAt' => '2026-10-12 16:45:00'
        ],
        [
            'id' => 4,
            'customerName' => 'Chloe Anne Santos',
            'rating' => 5,
            'comment' => 'The Ube Cake Matcha blew my mind! Beautiful layers and the balance between ube and matcha is heavenly.',
            'createdAt' => '2026-10-10 09:15:00'
        ]
    ];

    private const DEFAULT_ORDERS = [
        [
            'id' => 101,
            'orderNumber' => 'MM-1024',
            'customerName' => 'Maria Kristina Gomez',
            'phone' => '09171234567',
            'totalAmount' => '380.00',
            'fulfillmentMethod' => 'Delivery',
            'deliveryAddress' => 'Unit 402 Jade Residences, Makati City',
            'paymentMethod' => 'GCash',
            'referenceNumber' => '9048291048',
            'status' => 'Preparing',
            'orderDate' => '2026-10-17 19:30:00',
            'items' => [
                ['name' => 'Tropical Matcha', 'quantity' => 1, 'unitPrice' => '185.00', 'options' => 'Regular • 50% Sugar • Less Ice'],
                ['name' => 'Matcha Cloud', 'quantity' => 1, 'unitPrice' => '195.00', 'options' => 'Large • 75% Sugar • Normal Ice']
            ]
        ],
        [
            'id' => 102,
            'orderNumber' => 'MM-1023',
            'customerName' => 'Dave Kevin Tan',
            'phone' => '09289876543',
            'totalAmount' => '170.00',
            'fulfillmentMethod' => 'Pickup',
            'deliveryAddress' => null,
            'paymentMethod' => 'Cash',
            'referenceNumber' => null,
            'status' => 'Ready',
            'orderDate' => '2026-10-17 19:15:00',
            'items' => [
                ['name' => 'Matcha Latte', 'quantity' => 1, 'unitPrice' => '170.00', 'options' => 'Regular • 50% Sugar • Normal Ice']
            ]
        ],
        [
            'id' => 103,
            'orderNumber' => 'MM-1022',
            'customerName' => 'Sarah Bea Alonzo',
            'phone' => '09154443322',
            'totalAmount' => '545.00',
            'fulfillmentMethod' => 'Delivery',
            'deliveryAddress' => '12 Acacia Street, San Antonio, Pasig City',
            'paymentMethod' => 'GCash',
            'referenceNumber' => '8491028472',
            'status' => 'Pending',
            'orderDate' => '2026-10-17 20:05:00',
            'items' => [
                ['name' => 'Choco Matcha Fusion', 'quantity' => 1, 'unitPrice' => '180.00', 'options' => 'Regular • 50% Sugar • Normal Ice'],
                ['name' => 'Strawberry Matcha', 'quantity' => 1, 'unitPrice' => '190.00', 'options' => 'Regular • 25% Sugar • Less Ice'],
                ['name' => 'Cheesecake Matcha', 'quantity' => 1, 'unitPrice' => '160.00', 'options' => 'Regular • Normal Sweetness']
            ]
        ],
        [
            'id' => 104,
            'orderNumber' => 'MM-1021',
            'customerName' => 'Joshua Cruz',
            'phone' => '09995551234',
            'totalAmount' => '210.00',
            'fulfillmentMethod' => 'Pickup',
            'deliveryAddress' => null,
            'paymentMethod' => 'Cash',
            'referenceNumber' => null,
            'status' => 'Completed',
            'orderDate' => '2026-10-17 18:20:00',
            'items' => [
                ['name' => 'Ube Cake Matcha', 'quantity' => 1, 'unitPrice' => '210.00', 'options' => 'Large • 50% Sugar • Less Ice']
            ]
        ]
    ];

    private ?array $inMemoryProducts = null;
    private ?array $inMemoryReviews = null;
    private ?array $inMemoryOrders = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ?EntityManagerInterface $em = null
    ) {
    }

    private function getSession(): ?\Symfony\Component\HttpFoundation\Session\SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Get all products with DB fallback to defaults
     */
    public function getProducts(): array
    {
        if ($this->inMemoryProducts !== null) {
            return $this->inMemoryProducts;
        }

        // Try Doctrine if connected and has data
        if ($this->em) {
            try {
                $dbProducts = $this->em->getRepository(Product::class)->findBy([], ['category' => 'ASC', 'name' => 'ASC']);
                if (!empty($dbProducts)) {
                    $list = [];
                    foreach ($dbProducts as $p) {
                        $list[] = [
                            'id' => $p->getId(),
                            'name' => $p->getName(),
                            'category' => $p->getCategory() ?? 'Drinks',
                            'price' => (string) $p->getPrice(),
                            'stockQuantity' => $p->getStockQuantity() ?? 0,
                            'imageUrl' => $p->getImageUrl() ?? 'images/latte.png',
                            'description' => 'Handcrafted premium matcha drink made with ceremonial grade ingredients.'
                        ];
                    }
                    return $this->inMemoryProducts = $list;
                }
            } catch (\Throwable) {
                // Database offline or table not present; safely fallback
            }
        }

        $session = $this->getSession();
        if ($session && $session->has('custom_products')) {
            return $this->inMemoryProducts = $session->get('custom_products');
        }

        $products = self::DEFAULT_PRODUCTS;
        return $this->inMemoryProducts = $products;
    }

    /**
     * Get single product by id
     */
    public function getProduct(int $id): ?array
    {
        foreach ($this->getProducts() as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }

    /**
     * Group products by category
     */
    public function getGroupedProducts(): array
    {
        $products = $this->getProducts();
        $grouped = [];
        foreach ($products as $p) {
            $cat = $p['category'] ?? 'Other';
            $grouped[$cat][] = $p;
        }

        $order = [
            'Signature Lattes & Bestsellers',
            'Fruit & Specialty Fusions',
            'Premium Cake Matcha Creations',
        ];

        $ordered = [];
        foreach ($order as $cat) {
            if (isset($grouped[$cat])) {
                $ordered[$cat] = $grouped[$cat];
            }
        }
        foreach ($grouped as $cat => $items) {
            if (!isset($ordered[$cat])) {
                $ordered[$cat] = $items;
            }
        }

        return $ordered;
    }

    /**
     * Update stock of a product
     */
    public function updateProductStock(int $id, int $newStock): bool
    {
        $products = $this->getProducts();
        $found = false;
        foreach ($products as &$p) {
            if ($p['id'] === $id) {
                $p['stockQuantity'] = max(0, $newStock);
                $found = true;
                break;
            }
        }
        if ($found) {
            $this->saveProducts($products);
        }
        return $found;
    }

    /**
     * Add a new product
     */
    public function addProduct(array $data): array
    {
        $products = $this->getProducts();
        $maxId = 0;
        foreach ($products as $p) {
            if ($p['id'] > $maxId) {
                $maxId = $p['id'];
            }
        }

        $newProduct = [
            'id' => $maxId + 1,
            'name' => $data['name'] ?? 'New Matcha Drink',
            'category' => $data['category'] ?? 'Signature Lattes & Bestsellers',
            'price' => number_format((float) ($data['price'] ?? 180), 2, '.', ''),
            'stockQuantity' => (int) ($data['stockQuantity'] ?? 30),
            'imageUrl' => $data['imageUrl'] ?? 'images/latte.png',
            'description' => $data['description'] ?? 'Handcrafted fresh matcha beverage.'
        ];

        $products[] = $newProduct;
        $this->saveProducts($products);

        return $newProduct;
    }

    /**
     * Update product details
     */
    public function updateProduct(int $id, array $data): bool
    {
        $products = $this->getProducts();
        $found = false;
        foreach ($products as &$p) {
            if ($p['id'] === $id) {
                if (isset($data['name'])) $p['name'] = $data['name'];
                if (isset($data['category'])) $p['category'] = $data['category'];
                if (isset($data['price'])) $p['price'] = number_format((float)$data['price'], 2, '.', '');
                if (isset($data['stockQuantity'])) $p['stockQuantity'] = (int)$data['stockQuantity'];
                if (isset($data['imageUrl'])) $p['imageUrl'] = $data['imageUrl'];
                if (isset($data['description'])) $p['description'] = $data['description'];
                $found = true;
                break;
            }
        }
        if ($found) {
            $this->saveProducts($products);
        }
        return $found;
    }

    /**
     * Delete product
     */
    public function deleteProduct(int $id): bool
    {
        $products = $this->getProducts();
        $filtered = array_values(array_filter($products, fn($p) => $p['id'] !== $id));
        $this->saveProducts($filtered);
        return true;
    }

    private function saveProducts(array $products): void
    {
        $this->inMemoryProducts = null;
        $session = $this->getSession();
        if ($session) {
            $session->set('custom_products', $products);
        }
    }

    /**
     * Get all orders
     */
    public function getOrders(): array
    {
        $session = $this->getSession();
        if ($session && $session->has('store_orders')) {
            return $session->get('store_orders');
        }

        $orders = self::DEFAULT_ORDERS;
        if ($session) {
            $session->set('store_orders', $orders);
        }
        return $orders;
    }

    /**
     * Get specific order by orderNumber
     */
    public function getOrder(string $orderNumber): ?array
    {
        foreach ($this->getOrders() as $order) {
            if (strcasecmp($order['orderNumber'], $orderNumber) === 0 || (string)$order['id'] === $orderNumber) {
                return $order;
            }
        }
        return null;
    }

    /**
     * Create a new order
     */
    public function createOrder(array $data): array
    {
        $this->inMemoryOrders = null;
        $orders = $this->getOrders();
        $orderNum = 'MM-' . rand(2000, 9999);

        $order = [
            'id' => count($orders) + 200,
            'orderNumber' => $orderNum,
            'customerName' => trim($data['customerName'] ?? 'Valued Customer'),
            'phone' => trim($data['phone'] ?? '09000000000'),
            'totalAmount' => number_format((float) ($data['totalAmount'] ?? 0), 2, '.', ''),
            'fulfillmentMethod' => $data['fulfillmentMethod'] ?? 'Pickup',
            'deliveryAddress' => $data['deliveryAddress'] ?? null,
            'paymentMethod' => $data['paymentMethod'] ?? 'Cash',
            'referenceNumber' => $data['referenceNumber'] ?? null,
            'notes' => $data['notes'] ?? '',
            'status' => 'Pending',
            'orderDate' => (new \DateTime())->format('Y-m-d H:i:s'),
            'items' => $data['items'] ?? []
        ];

        array_unshift($orders, $order);

        $session = $this->getSession();
        if ($session) {
            $session->set('store_orders', $orders);
        }

        return $order;
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(string $orderNumber, string $newStatus): bool
    {
        $this->inMemoryOrders = null;
        $orders = $this->getOrders();
        $found = false;
        foreach ($orders as &$order) {
            if ($order['orderNumber'] === $orderNumber || (string)$order['id'] === $orderNumber) {
                $order['status'] = $newStatus;
                $found = true;
                break;
            }
        }
        if ($found) {
            $session = $this->getSession();
            if ($session) {
                $session->set('store_orders', $orders);
            }
        }
        return $found;
    }

    /**
     * Get all reviews
     */
    public function getReviews(): array
    {
        if ($this->inMemoryReviews !== null) {
            return $this->inMemoryReviews;
        }

        if ($this->em) {
            try {
                $dbReviews = $this->em->getRepository(Review::class)->findBy([], ['createdAt' => 'DESC']);
                if (!empty($dbReviews)) {
                    $list = [];
                    foreach ($dbReviews as $r) {
                        $list[] = [
                            'id' => $r->getId(),
                            'customerName' => $r->getCustomerName() ?? 'Anonymous',
                            'rating' => $r->getRating() ?? 5,
                            'comment' => $r->getComment() ?? '',
                            'createdAt' => $r->getCreatedAt()?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s')
                        ];
                    }
                    return $this->inMemoryReviews = $list;
                }
            } catch (\Throwable) {
            }
        }

        $session = $this->getSession();
        if ($session && $session->has('store_reviews')) {
            return $this->inMemoryReviews = $session->get('store_reviews');
        }

        $reviews = self::DEFAULT_REVIEWS;
        return $this->inMemoryReviews = $reviews;
    }

    /**
     * Add a review
     */
    public function addReview(array $data): array
    {
        $this->inMemoryReviews = null;
        $reviews = $this->getReviews();
        $newReview = [
            'id' => count($reviews) + 1,
            'customerName' => trim($data['customerName'] ?? 'Anonymous Matcha Lover'),
            'rating' => max(1, min(5, (int) ($data['rating'] ?? 5))),
            'comment' => trim($data['comment'] ?? ''),
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s')
        ];

        array_unshift($reviews, $newReview);

        $session = $this->getSession();
        if ($session) {
            $session->set('store_reviews', $reviews);
        }

        return $newReview;
    }

    /**
     * Delete a review (Admin)
     */
    public function deleteReview(int $id): bool
    {
        $this->inMemoryReviews = null;
        $reviews = $this->getReviews();
        $filtered = array_values(array_filter($reviews, fn($r) => $r['id'] !== $id));
        $session = $this->getSession();
        if ($session) {
            $session->set('store_reviews', $filtered);
        }
        return true;
    }

    /**
     * Get subscribers
     */
    public function getSubscribers(): array
    {
        $session = $this->getSession();
        if ($session && $session->has('store_subscribers')) {
            return $session->get('store_subscribers');
        }

        $defaults = [
            ['id' => 1, 'email' => 'sarah.g@gmail.com', 'subscribedAt' => '2026-10-16 10:20:00', 'status' => 'Active'],
            ['id' => 2, 'email' => 'kevin_matcha@yahoo.com', 'subscribedAt' => '2026-10-15 15:45:00', 'status' => 'Active'],
            ['id' => 3, 'email' => 'jolamae.n@outlook.com', 'subscribedAt' => '2026-10-14 09:12:00', 'status' => 'Active']
        ];

        if ($session) {
            $session->set('store_subscribers', $defaults);
        }
        return $defaults;
    }

    /**
     * Add subscriber
     */
    public function addSubscriber(string $email): bool
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $subscribers = $this->getSubscribers();
        foreach ($subscribers as $s) {
            if ($s['email'] === $email) {
                return true; // already subscribed
            }
        }

        array_unshift($subscribers, [
            'id' => count($subscribers) + 1,
            'email' => $email,
            'subscribedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            'status' => 'Active'
        ]);

        $session = $this->getSession();
        if ($session) {
            $session->set('store_subscribers', $subscribers);
        }

        return true;
    }

    /**
     * Get summary metrics for Admin Dashboard
     */
    public function getDashboardStats(): array
    {
        $orders = $this->getOrders();
        $products = $this->getProducts();
        $reviews = $this->getReviews();
        $subscribers = $this->getSubscribers();

        $totalRevenue = 0;
        $pendingCount = 0;
        $preparingCount = 0;
        $readyCount = 0;
        $completedCount = 0;

        foreach ($orders as $order) {
            $totalRevenue += (float) ($order['totalAmount'] ?? 0);
            $st = strtolower($order['status'] ?? '');
            if ($st === 'pending') $pendingCount++;
            elseif ($st === 'preparing') $preparingCount++;
            elseif ($st === 'ready') $readyCount++;
            elseif ($st === 'completed') $completedCount++;
        }

        $lowStockProducts = array_filter($products, fn($p) => ($p['stockQuantity'] ?? 0) <= 25);

        return [
            'totalRevenue' => number_format($totalRevenue, 2),
            'totalOrders' => count($orders),
            'pendingOrders' => $pendingCount,
            'preparingOrders' => $preparingCount,
            'readyOrders' => $readyCount,
            'completedOrders' => $completedCount,
            'totalProducts' => count($products),
            'lowStockCount' => count($lowStockProducts),
            'lowStockProducts' => array_values($lowStockProducts),
            'totalReviews' => count($reviews),
            'totalSubscribers' => count($subscribers),
            'recentOrders' => array_slice($orders, 0, 5),
        ];
    }
}
