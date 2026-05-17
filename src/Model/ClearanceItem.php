<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model;

class ClearanceItem
{
    public function __construct(
        private readonly string $descriptionOfGoods,
        private readonly string $unitQuantity,
        private readonly string $unitValue,
        private readonly ?string $commodityCode = null,
    ) {}

    public function getDescriptionOfGoods(): string
    {
        return $this->descriptionOfGoods;
    }

    public function getUnitQuantity(): string
    {
        return $this->unitQuantity;
    }

    public function getUnitValue(): string
    {
        return $this->unitValue;
    }

    public function getCommodityCode(): ?string
    {
        return $this->commodityCode;
    }

    public function toArray(): array
    {
        return [
            'descriptionOfGoods' => $this->getDescriptionOfGoods(),
            'unitQuantity' => $this->getUnitQuantity(),
            'unitValue' => $this->getUnitValue(),
            'commodityCode' => $this->getCommodityCode() ?? '',
        ];
    }
}
