<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model;

class ClearanceDeclaration
{
    public function __construct(
        private readonly string $shipmentMovementType, // e.g. "B2C"
        private readonly float $totalValue,
        private readonly int $numberOfItems,
        private readonly array $items,
        private readonly ?string $sendersEORINumber = null,
        private readonly ?string $sendersUKIMSNumber = null,
    ) {}

    public function getShipmentMovementType(): string
    {
        return $this->shipmentMovementType;
    }

    public function getTotalValue(): float
    {
        return $this->totalValue;
    }

    public function getNumberOfItems(): int
    {
        return $this->numberOfItems;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getSendersEORINumber(): ?string
    {
        return $this->sendersEORINumber;
    }

    public function getSendersUKIMSNumber(): ?string
    {
        return $this->sendersUKIMSNumber;
    }

    public function toArray(): array
    {
        return [
            'shipmentMovementType' => $this->getShipmentMovementType(),
            'sendersEORINumber'    => $this->getSendersEORINumber() ?? '',
            'sendersUKIMSNumber'   => $this->getSendersUKIMSNumber() ?? '',
            'totalValue'           => $this->getTotalValue(),
            'numberOfItems'        => $this->getNumberOfItems(),
            'items'                => array_map(fn(ClearanceItem $i) => $i->toArray(), $this->items),
        ];
    }
}
