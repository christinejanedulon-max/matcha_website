<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[ORM\Index(name: 'idx_orders_customer_email', columns: ['customer_email'])]
#[ORM\Index(name: 'idx_orders_order_number', columns: ['order_number'])]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $id = null;

    #[ORM\Column(name: 'order_number', length: 50, unique: true, nullable: true)]
    private ?string $orderNumber = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(name: 'customer_name', length: 100)]
    private ?string $customerName = null;

    #[ORM\Column(name: 'customer_email', length: 180, nullable: true)]
    private ?string $customerEmail = null;

    #[ORM\Column(name: 'phone', length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'total_amount', type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '0.00'])]
    private ?string $totalAmount = '0.00';

    #[ORM\Column(name: 'fulfillment_method', length: 50, nullable: true, options: ['default' => 'Pickup'])]
    private ?string $fulfillmentMethod = 'Pickup';

    #[ORM\Column(name: 'delivery_address', type: Types::TEXT, nullable: true)]
    private ?string $deliveryAddress = null;

    #[ORM\Column(name: 'payment_method', length: 50, nullable: true, options: ['default' => 'Cash'])]
    private ?string $paymentMethod = 'Cash';

    #[ORM\Column(name: 'reference_number', length: 100, nullable: true)]
    private ?string $referenceNumber = null;

    #[ORM\Column(name: 'notes', type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'product_name', length: 255, nullable: true)]
    private ?string $productName = null;

    #[ORM\Column(name: 'item_name', length: 255, nullable: true)]
    private ?string $itemName = null;

    #[ORM\Column(name: 'status', length: 50, nullable: true, options: ['default' => 'Pending'])]
    private ?string $status = 'Pending';

    #[ORM\Column(name: 'status_updated_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $statusUpdatedAt = null;

    #[ORM\Column(name: 'order_date', type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $orderDate = null;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist', 'remove'])]
    private Collection $orderItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->orderDate = new \DateTime();
        $this->statusUpdatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(?string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getFulfillmentMethod(): ?string
    {
        return $this->fulfillmentMethod;
    }

    public function setFulfillmentMethod(?string $fulfillmentMethod): static
    {
        $this->fulfillmentMethod = $fulfillmentMethod;

        return $this;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?string $deliveryAddress): static
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getReferenceNumber(): ?string
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(?string $referenceNumber): static
    {
        $this->referenceNumber = $referenceNumber;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): static
    {
        $this->productName = $productName;

        return $this;
    }

    public function getItemName(): ?string
    {
        return $this->itemName;
    }

    public function setItemName(?string $itemName): static
    {
        $this->itemName = $itemName;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        $this->statusUpdatedAt = new \DateTime();

        return $this;
    }

    public function getStatusUpdatedAt(): ?\DateTimeInterface
    {
        return $this->statusUpdatedAt;
    }

    public function setStatusUpdatedAt(?\DateTimeInterface $statusUpdatedAt): static
    {
        $this->statusUpdatedAt = $statusUpdatedAt;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface $orderDate): static
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }
}
