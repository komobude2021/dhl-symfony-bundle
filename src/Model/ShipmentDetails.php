<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model;

class ShipmentDetails
{
    private string $customerRef1;
    private string $customerRef2;
    private string $orderedProduct;
    private int $totalPieces;
    private float $totalWeight;

    public function __construct(
        string $customerRef1,
        string $customerRef2,
        string $orderedProduct,
        int $totalPieces,
        float $totalWeight
    ) {
        $this->customerRef1 = $customerRef1;
        $this->customerRef2 = $customerRef2;
        $this->orderedProduct = $orderedProduct;
        $this->totalPieces = $totalPieces;
        $this->totalWeight = $totalWeight;
    }

    public function getCustomerRef1(): string
    {
        return $this->customerRef1;
    }

    public function getCustomerRef2(): string
    {
        return $this->customerRef2;
    }

    public function getOrderedProduct(): string
    {
        return $this->orderedProduct;
    }

    public function getTotalPieces(): int
    {
        return $this->totalPieces;
    }

    public function getTotalWeight(): float
    {
        return $this->totalWeight;
    }

    public function setCustomerRef1(string $customerRef1): self
    {
        $this->customerRef1 = $customerRef1;
        return $this;
    }

    public function setCustomerRef2(string $customerRef2): self
    {
        $this->customerRef2 = $customerRef2;
        return $this;
    }

    public function setOrderedProduct(string $orderedProduct): self
    {
        $this->orderedProduct = $orderedProduct;
        return $this;
    }

    public function setTotalPieces(int $totalPieces): self
    {
        $this->totalPieces = $totalPieces;
        return $this;
    }

    public function setTotalWeight(float $totalWeight): self
    {
        $this->totalWeight = $totalWeight;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'customerRef1' => $this->getCustomerRef1(),
            'customerRef2' => $this->getCustomerRef2(),
            'orderedProduct' => $this->getOrderedProduct(),
            'totalPieces' => $this->getTotalPieces(),
            'totalWeight' => $this->getTotalWeight(),
        ];
    }
}
