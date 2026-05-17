<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model;

class ClearanceItem
{
    public function __construct(
        private readonly string $descriptionOfGoods,
        private readonly int $unitQuantity,
        private readonly ?string $commodityCode = null,
    ) {}

    public function getDescriptionOfGoods(): string
    {
        return $this->descriptionOfGoods;
    }

    public function getUnitQuantity(): int
    {
        return $this->unitQuantity;
    }

    public function getCommodityCode(): ?string
    {
        return $this->commodityCode;
    }

    public function toArray(): array
    {
        return [
            'descriptionOfGoods' => $this->descriptionOfGoods,
            'unitQuantity' => $this->unitQuantity,
            'commodityCode' => $this->commodityCode ?? '',
        ];
    }
}