<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Review;
use App\Entity\NewsletterSubscription;
use App\Entity\User;
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

    private const DEFAULT_USERS = [
        [
            'id' => 1,
            'fullName' => 'Cafe Administrator',
            'email' => 'admin@matchamoments.com',
            'role' => 'admin',
            'roles' => ['ROLE_ADMIN', 'ROLE_USER'],
            'status' => 'active',
            'password' => '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeH0iPq3vP.z1KzYy1Vv6p6Y1KzYy1Vv6', // admin123
            'createdAt' => '2026-11-01 08:00:00',
        ],
        [
            'id' => 2,
            'fullName' => 'Matcha Lead Barista',
            'email' => 'staff@matchamoments.com',
            'role' => 'staff',
            'roles' => ['ROLE_STAFF', 'ROLE_USER'],
            'status' => 'active',
            'password' => '$2y$12$vSculuY/ItX1pQdD1C3mc.5egegsdpv1Tdz7Kl1XD6vLopa0YetWW', // staff123
            'createdAt' => '2026-11-02 09:30:00',
        ],
        [
            'id' => 3,
            'fullName' => 'Jane Matcha Lover',
            'email' => 'customer@matchamoments.com',
            'role' => 'customer',
            'roles' => ['ROLE_USER'],
            'status' => 'active',
            'password' => '$2y$12$vSculuY/ItX1pQdD1C3mc.5egegsdpv1Tdz7Kl1XD6vLopa0YetWW',
            'createdAt' => '2026-11-03 14:15:00',
        ],
    ];

    private ?array $inMemoryProducts = null;
    private ?array $inMemoryReviews = null;
    private ?array $inMemoryOrders = null;
    private ?array $inMemoryUsers = null;

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
                        $creator = method_exists($p, 'getCreatedBy') ? $p->getCreatedBy() : null;
                        $list[] = [
                            'id' => $p->getId(),
                            'name' => $p->getName(),
                            'category' => $p->getCategory() ?? 'Drinks',
                            'price' => (string) $p->getPrice(),
                            'stockQuantity' => $p->getStockQuantity() ?? 0,
                            'imageUrl' => $p->getImageUrl() ?? 'images/latte.png',
                            'description' => 'Handcrafted premium matcha drink made with ceremonial grade ingredients.',
                            'createdByEmail' => $creator ? $creator->getEmail() : 'admin@matchamoments.com',
                            'createdByName' => $creator ? $creator->getFullName() : 'Cafe Administrator',
                            'createdByRole' => $creator ? $creator->getRole() : 'admin',
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
            $sessionList = $session->get('custom_products');
            foreach ($sessionList as &$sp) {
                if (!isset($sp['createdByEmail'])) {
                    $sp['createdByEmail'] = 'admin@matchamoments.com';
                    $sp['createdByName'] = 'Cafe Administrator';
                    $sp['createdByRole'] = 'admin';
                }
            }
            return $this->inMemoryProducts = $sessionList;
        }

        $products = self::DEFAULT_PRODUCTS;
        foreach ($products as &$dp) {
            $dp['createdByEmail'] = 'admin@matchamoments.com';
            $dp['createdByName'] = 'Cafe Administrator';
            $dp['createdByRole'] = 'admin';
        }
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
     * Add a new product (with creator tracking for Staff / Admin)
     */
    public function addProduct(array $data, ?array $creator = null): array
    {
        $products = $this->getProducts();
        $maxId = 0;
        foreach ($products as $p) {
            if ($p['id'] > $maxId) {
                $maxId = $p['id'];
            }
        }

        $creatorEmail = $creator['email'] ?? 'admin@matchamoments.com';
        $creatorName = $creator['fullName'] ?? 'Cafe Administrator';
        $creatorRole = $creator['role'] ?? 'admin';

        $newProduct = [
            'id' => $maxId + 1,
            'name' => $data['name'] ?? 'New Matcha Drink',
            'category' => $data['category'] ?? 'Signature Lattes & Bestsellers',
            'price' => number_format((float) ($data['price'] ?? 180), 2, '.', ''),
            'stockQuantity' => (int) ($data['stockQuantity'] ?? 30),
            'imageUrl' => $data['imageUrl'] ?? 'images/latte.png',
            'description' => $data['description'] ?? 'Handcrafted fresh matcha beverage.',
            'createdByEmail' => $creatorEmail,
            'createdByName' => $creatorName,
            'createdByRole' => $creatorRole,
        ];

        // Persist to DB if connected
        if ($this->em) {
            try {
                $pEntity = new Product();
                $pEntity->setName($newProduct['name']);
                $pEntity->setCategory($newProduct['category']);
                $pEntity->setPrice($newProduct['price']);
                $pEntity->setStockQuantity($newProduct['stockQuantity']);
                $pEntity->setImageUrl($newProduct['imageUrl']);
                if ($creatorEmail) {
                    $u = $this->em->getRepository(User::class)->findOneBy(['email' => $creatorEmail]);
                    if ($u) {
                        $pEntity->setCreatedBy($u);
                    }
                }
                $this->em->persist($pEntity);
                $this->em->flush();
                $newProduct['id'] = $pEntity->getId();
            } catch (\Throwable) {}
        }

        $products[] = $newProduct;
        $this->saveProducts($products);

        return $newProduct;
    }

    /**
     * Rubric Permission Check: Staff can only edit/delete their own records; Admin can edit/delete any record.
     */
    public function canUserModifyProduct(array $product, ?string $userEmail, ?string $userRole): bool
    {
        if ($userRole === 'admin') {
            return true;
        }
        if ($userRole === 'staff') {
            $productCreator = strtolower((string) ($product['createdByEmail'] ?? 'admin@matchamoments.com'));
            $currentUser = strtolower((string) $userEmail);
            return ($productCreator === $currentUser);
        }
        return false;
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
     * Helper to map Order entity to template-friendly array
     */
    private function mapOrderEntityToArray(Order $o): array
    {
        $items = [];
        foreach ($o->getOrderItems() as $item) {
            $items[] = [
                'name' => $item->getProductName() ?? $item->getProduct()?->getName() ?? 'Matcha Drink',
                'quantity' => $item->getQuantity(),
                'unitPrice' => (string) $item->getUnitPrice(),
                'options' => $item->getOptions() ?? 'Regular',
                'imageUrl' => $item->getProduct()?->getImageUrl() ?? 'images/latte.png',
            ];
        }

        return [
            'id' => $o->getId(),
            'orderNumber' => $o->getOrderNumber() ?? ('MM-' . $o->getId()),
            'customerName' => $o->getCustomerName() ?? 'Valued Customer',
            'customerEmail' => $o->getCustomerEmail() ?? ($o->getUser()?->getEmail()),
            'phone' => $o->getPhone() ?? '',
            'totalAmount' => (string) $o->getTotalAmount(),
            'fulfillmentMethod' => $o->getFulfillmentMethod() ?? 'Pickup',
            'deliveryAddress' => $o->getDeliveryAddress(),
            'paymentMethod' => $o->getPaymentMethod() ?? 'Cash',
            'referenceNumber' => $o->getReferenceNumber(),
            'notes' => $o->getNotes() ?? '',
            'status' => $o->getStatus() ?? 'Pending',
            'orderDate' => $o->getOrderDate()?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s'),
            'statusUpdatedAt' => $o->getStatusUpdatedAt()?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s'),
            'items' => $items,
        ];
    }

    /**
     * Get all orders
     */
    public function getOrders(): array
    {
        if ($this->em) {
            try {
                $dbOrders = $this->em->getRepository(Order::class)->findBy([], ['orderDate' => 'DESC', 'id' => 'DESC']);
                if (!empty($dbOrders)) {
                    $list = [];
                    foreach ($dbOrders as $o) {
                        $list[] = $this->mapOrderEntityToArray($o);
                    }
                    return $list;
                }
            } catch (\Throwable) {
            }
        }

        $session = $this->getSession();
        if ($session && $session->has('store_orders')) {
            return $session->get('store_orders');
        }

        return self::DEFAULT_ORDERS;
    }

    /**
     * Get specific order by orderNumber
     */
    public function getOrder(string $orderNumber): ?array
    {
        if ($this->em) {
            try {
                $orderRepo = $this->em->getRepository(Order::class);
                $entity = $orderRepo->findOneBy(['orderNumber' => $orderNumber]);
                if (!$entity && is_numeric($orderNumber)) {
                    $entity = $orderRepo->find((int)$orderNumber);
                }
                if ($entity) {
                    return $this->mapOrderEntityToArray($entity);
                }
            } catch (\Throwable) {
            }
        }

        foreach ($this->getOrders() as $order) {
            if (strcasecmp($order['orderNumber'] ?? '', $orderNumber) === 0 || (string)($order['id'] ?? '') === $orderNumber) {
                return $order;
            }
        }
        return null;
    }

    /**
     * Get all orders for a specific customer email
     */
    public function getOrdersForEmail(string $email): array
    {
        $email = strtolower(trim($email));
        if ($email === '') {
            return [];
        }

        if ($this->em) {
            try {
                $qb = $this->em->createQueryBuilder();
                $qb->select('o')
                   ->from(Order::class, 'o')
                   ->leftJoin('o.user', 'u')
                   ->where('LOWER(o.customerEmail) = :email OR LOWER(u.email) = :email')
                   ->setParameter('email', $email)
                   ->orderBy('o.orderDate', 'DESC')
                   ->addOrderBy('o.id', 'DESC');

                $dbOrders = $qb->getQuery()->getResult();
                $list = [];
                foreach ($dbOrders as $o) {
                    $list[] = $this->mapOrderEntityToArray($o);
                }
                if (!empty($list)) {
                    return $list;
                }
            } catch (\Throwable) {
            }
        }

        $filtered = [];
        foreach ($this->getOrders() as $o) {
            $orderEmail = strtolower(trim($o['customerEmail'] ?? ''));
            if ($orderEmail === $email) {
                $filtered[] = $o;
            }
        }
        return $filtered;
    }

    /**
     * Create a new order
     */
    public function createOrder(array $data): array
    {
        $session = $this->getSession();
        $userEmail = strtolower(trim($data['customerEmail'] ?? $session?->get('user_email', '') ?? ''));

        // Generate unique order number: MM-XXXX
        $orderNum = null;
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $candidate = 'MM-' . rand(2000, 9999);
            if ($this->em) {
                $existing = $this->em->getRepository(Order::class)->findOneBy(['orderNumber' => $candidate]);
                if (!$existing) {
                    $orderNum = $candidate;
                    break;
                }
            } else {
                $orderNum = $candidate;
                break;
            }
        }
        $orderNum = $orderNum ?: ('MM-' . uniqid());

        $customerName = trim($data['customerName'] ?? 'Valued Customer');
        $phone = trim($data['phone'] ?? '');
        $totalAmount = number_format((float) ($data['totalAmount'] ?? 0), 2, '.', '');
        $fulfillment = $data['fulfillmentMethod'] ?? 'Pickup';
        $deliveryAddress = ($fulfillment === 'Delivery') ? ($data['deliveryAddress'] ?? null) : null;
        $paymentMethod = $data['paymentMethod'] ?? 'Cash';
        $referenceNumber = ($paymentMethod === 'GCash') ? ($data['referenceNumber'] ?? null) : null;
        $notes = $data['notes'] ?? '';
        $items = $data['items'] ?? [];

        // Save to Database
        if ($this->em) {
            try {
                $orderEntity = new Order();
                $orderEntity->setOrderNumber($orderNum);
                $orderEntity->setCustomerName($customerName);
                $orderEntity->setCustomerEmail($userEmail ?: null);
                $orderEntity->setPhone($phone);
                $orderEntity->setTotalAmount($totalAmount);
                $orderEntity->setFulfillmentMethod($fulfillment);
                $orderEntity->setDeliveryAddress($deliveryAddress);
                $orderEntity->setPaymentMethod($paymentMethod);
                $orderEntity->setReferenceNumber($referenceNumber);
                $orderEntity->setNotes($notes);
                $orderEntity->setStatus('Pending');

                if ($userEmail) {
                    $user = $this->em->getRepository(User::class)->findOneBy(['email' => $userEmail]);
                    if ($user) {
                        $orderEntity->setUser($user);
                    }
                }

                $productRepo = $this->em->getRepository(Product::class);
                foreach ($items as $itemData) {
                    $orderItem = new OrderItem();
                    $orderItem->setProductName($itemData['name'] ?? 'Matcha Drink');
                    $orderItem->setQuantity((int)($itemData['quantity'] ?? 1));
                    $orderItem->setUnitPrice(number_format((float)($itemData['unitPrice'] ?? 0), 2, '.', ''));
                    $orderItem->setOptions($itemData['options'] ?? 'Regular');

                    if (!empty($itemData['id'])) {
                        $prod = $productRepo->find((int)$itemData['id']);
                        if ($prod) {
                            $orderItem->setProduct($prod);
                        }
                    }

                    $orderEntity->addOrderItem($orderItem);
                }

                $this->em->persist($orderEntity);
                $this->em->flush();

                return $this->mapOrderEntityToArray($orderEntity);
            } catch (\Throwable $e) {
                // Fallback to session
            }
        }

        // Session fallback
        $orders = $this->getOrders();
        $order = [
            'id' => count($orders) + 200,
            'orderNumber' => $orderNum,
            'customerName' => $customerName,
            'customerEmail' => $userEmail,
            'phone' => $phone,
            'totalAmount' => $totalAmount,
            'fulfillmentMethod' => $fulfillment,
            'deliveryAddress' => $deliveryAddress,
            'paymentMethod' => $paymentMethod,
            'referenceNumber' => $referenceNumber,
            'notes' => $notes,
            'status' => 'Pending',
            'orderDate' => (new \DateTime())->format('Y-m-d H:i:s'),
            'statusUpdatedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            'items' => $items
        ];
        array_unshift($orders, $order);
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
        $updated = false;
        if ($this->em) {
            try {
                $orderRepo = $this->em->getRepository(Order::class);
                $entity = $orderRepo->findOneBy(['orderNumber' => $orderNumber]);
                if (!$entity && is_numeric($orderNumber)) {
                    $entity = $orderRepo->find((int)$orderNumber);
                }
                if ($entity) {
                    $entity->setStatus($newStatus);
                    $this->em->flush();
                    $updated = true;
                }
            } catch (\Throwable) {
            }
        }

        $session = $this->getSession();
        if ($session && $session->has('store_orders')) {
            $orders = $session->get('store_orders');
            foreach ($orders as &$o) {
                if (($o['orderNumber'] ?? '') === $orderNumber || (string)($o['id'] ?? '') === $orderNumber) {
                    $o['status'] = $newStatus;
                    $o['statusUpdatedAt'] = (new \DateTime())->format('Y-m-d H:i:s');
                    $updated = true;
                    break;
                }
            }
            $session->set('store_orders', $orders);
        }

        return $updated;
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
     * Get all users (from Database with fallback)
     */
    public function getUsers(): array
    {
        if ($this->inMemoryUsers !== null) {
            return $this->inMemoryUsers;
        }

        if ($this->em) {
            try {
                $dbUsers = $this->em->getRepository(User::class)->findBy([], ['id' => 'ASC']);
                if (!empty($dbUsers)) {
                    $list = [];
                    foreach ($dbUsers as $u) {
                        $list[] = [
                            'id' => $u->getId(),
                            'fullName' => $u->getFullName(),
                            'email' => $u->getEmail(),
                            'role' => $u->getRole() ?? 'customer',
                            'roles' => $u->getRoles(),
                            'status' => method_exists($u, 'getStatus') ? $u->getStatus() : 'active',
                            'createdAt' => $u->getCreatedAt()?->format('Y-m-d H:i:s') ?? date('Y-m-d H:i:s'),
                        ];
                    }
                    return $this->inMemoryUsers = $list;
                }
            } catch (\Throwable) {}
        }

        $session = $this->getSession();
        if ($session && $session->has('store_users')) {
            return $this->inMemoryUsers = $session->get('store_users');
        }

        $users = self::DEFAULT_USERS;
        if ($session && $session->has('registered_users')) {
            $reg = $session->get('registered_users');
            $idx = 10;
            foreach ($reg as $email => $data) {
                $users[] = [
                    'id' => $idx++,
                    'fullName' => $data['fullName'] ?? 'Customer',
                    'email' => $email,
                    'role' => $data['role'] ?? 'customer',
                    'roles' => ['ROLE_USER'],
                    'status' => 'active',
                    'createdAt' => date('Y-m-d H:i:s'),
                ];
            }
        }

        if ($session) {
            $session->set('store_users', $users);
        }

        return $this->inMemoryUsers = $users;
    }

    public function getUser(int $id): ?array
    {
        foreach ($this->getUsers() as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));
        foreach ($this->getUsers() as $user) {
            if (strtolower($user['email']) === $email) {
                return $user;
            }
        }
        return null;
    }

    public function createUser(array $data): array
    {
        $users = $this->getUsers();
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $fullName = trim((string) ($data['fullName'] ?? 'Staff Member'));
        $role = in_array($data['role'] ?? '', ['admin', 'staff', 'customer'], true) ? $data['role'] : 'staff';
        $status = in_array($data['status'] ?? '', ['active', 'disabled', 'archived'], true) ? $data['status'] : 'active';
        $plainPass = $data['password'] ?? 'matcha123';
        $hashed = password_hash($plainPass, PASSWORD_BCRYPT);

        $maxId = 0;
        foreach ($users as $u) {
            if ($u['id'] > $maxId) $maxId = $u['id'];
        }

        $newUser = [
            'id' => $maxId + 1,
            'fullName' => $fullName,
            'email' => $email,
            'role' => $role,
            'roles' => ($role === 'admin') ? ['ROLE_ADMIN', 'ROLE_USER'] : (($role === 'staff') ? ['ROLE_STAFF', 'ROLE_USER'] : ['ROLE_USER']),
            'status' => $status,
            'password' => $hashed,
            'createdAt' => date('Y-m-d H:i:s'),
        ];

        if ($this->em) {
            try {
                $uEntity = new User();
                $uEntity->setFullName($fullName);
                $uEntity->setEmail($email);
                $uEntity->setRole($role);
                if (method_exists($uEntity, 'setStatus')) {
                    $uEntity->setStatus($status);
                }
                $uEntity->setPassword($hashed);
                $uEntity->setRoles($newUser['roles']);
                $this->em->persist($uEntity);
                $this->em->flush();
                $newUser['id'] = $uEntity->getId();
            } catch (\Throwable) {}
        }

        $users[] = $newUser;
        $this->saveUsers($users);

        return $newUser;
    }

    public function updateUser(int $id, array $data): bool
    {
        $users = $this->getUsers();
        $found = false;
        foreach ($users as &$u) {
            if ($u['id'] === $id) {
                if (isset($data['fullName'])) $u['fullName'] = trim((string)$data['fullName']);
                if (isset($data['email'])) $u['email'] = strtolower(trim((string)$data['email']));
                if (isset($data['role']) && in_array($data['role'], ['admin', 'staff', 'customer'], true)) {
                    $u['role'] = $data['role'];
                    $u['roles'] = ($data['role'] === 'admin') ? ['ROLE_ADMIN', 'ROLE_USER'] : (($data['role'] === 'staff') ? ['ROLE_STAFF', 'ROLE_USER'] : ['ROLE_USER']);
                }
                if (isset($data['status']) && in_array($data['status'], ['active', 'disabled', 'archived'], true)) {
                    $u['status'] = $data['status'];
                }
                if (!empty($data['password'])) {
                    $u['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
                }
                $found = true;
                break;
            }
        }

        if ($this->em) {
            try {
                $uEntity = $this->em->getRepository(User::class)->find($id);
                if ($uEntity) {
                    if (isset($data['fullName'])) $uEntity->setFullName(trim((string)$data['fullName']));
                    if (isset($data['email'])) $uEntity->setEmail(strtolower(trim((string)$data['email'])));
                    if (isset($data['role'])) {
                        $uEntity->setRole($data['role']);
                        $uEntity->setRoles(($data['role'] === 'admin') ? ['ROLE_ADMIN', 'ROLE_USER'] : (($data['role'] === 'staff') ? ['ROLE_STAFF', 'ROLE_USER'] : ['ROLE_USER']));
                    }
                    if (isset($data['status']) && method_exists($uEntity, 'setStatus')) {
                        $uEntity->setStatus($data['status']);
                    }
                    if (!empty($data['password'])) {
                        $uEntity->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
                    }
                    $this->em->flush();
                }
            } catch (\Throwable) {}
        }

        if ($found) {
            $this->saveUsers($users);
        }
        return $found;
    }

    public function deleteUser(int $id): bool
    {
        $users = $this->getUsers();
        // Master admin safeguard
        foreach ($users as $u) {
            if ($u['id'] === $id && strtolower($u['email']) === 'admin@matchamoments.com') {
                return false;
            }
        }

        $filtered = array_values(array_filter($users, fn($u) => $u['id'] !== $id));

        if ($this->em) {
            try {
                $uEntity = $this->em->getRepository(User::class)->find($id);
                if ($uEntity && strtolower($uEntity->getEmail()) !== 'admin@matchamoments.com') {
                    $this->em->remove($uEntity);
                    $this->em->flush();
                }
            } catch (\Throwable) {}
        }

        $this->saveUsers($filtered);
        return true;
    }

    public function updateUserPassword(string $email, string $currentPassword, string $newPassword): bool
    {
        $email = strtolower(trim($email));
        $users = $this->getUsers();
        $found = false;

        if ($this->em) {
            try {
                $uEntity = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($uEntity && password_verify($currentPassword, $uEntity->getPassword())) {
                    $uEntity->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));
                    $this->em->flush();
                    $found = true;
                }
            } catch (\Throwable) {}
        }

        foreach ($users as &$u) {
            if (strtolower($u['email']) === $email) {
                if (isset($u['password']) && !password_verify($currentPassword, $u['password'])) {
                    return false;
                }
                $u['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
                $found = true;
                break;
            }
        }

        if ($found) {
            $this->saveUsers($users);
        }

        return $found;
    }

    private function saveUsers(array $users): void
    {
        $this->inMemoryUsers = null;
        $session = $this->getSession();
        if ($session) {
            $session->set('store_users', $users);
        }
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
        $users = $this->getUsers();

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

        $totalUsers = count($users);
        $totalStaff = count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'staff'));
        $totalCustomers = count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'customer'));
        $totalAdmins = count(array_filter($users, fn($u) => ($u['role'] ?? '') === 'admin'));

        return [
            'totalRevenue' => number_format($totalRevenue, 2),
            'totalOrders' => count($orders),
            'pendingOrders' => $pendingCount,
            'preparingOrders' => $preparingCount,
            'readyOrders' => $readyCount,
            'completedOrders' => $completedCount,
            'totalProducts' => count($products),
            'totalRecords' => count($products),
            'totalUsers' => $totalUsers,
            'totalStaff' => $totalStaff,
            'totalCustomers' => $totalCustomers,
            'totalAdmins' => $totalAdmins,
            'lowStockCount' => count($lowStockProducts),
            'lowStockProducts' => array_values($lowStockProducts),
            'totalReviews' => count($reviews),
            'totalSubscribers' => count($subscribers),
            'recentOrders' => array_slice($orders, 0, 5),
        ];
    }
}
