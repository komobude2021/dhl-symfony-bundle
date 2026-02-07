<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model;

class ShipmentData
{
    private string $pickupAccount;
    private string $dropoffType;
    private ConsigneeAddress $consigneeAddress;
    private PickupData $pickupData;
    private SenderAddress $senderAddress;
    private ShipmentDetails $shipmentDetails;

    public function __construct(
        string $pickupAccount,
        string $dropoffType,
        ConsigneeAddress $consigneeAddress,
        PickupData $pickupData,
        SenderAddress $senderAddress,
        ShipmentDetails $shipmentDetails,
    ) {
        $this->pickupAccount = $pickupAccount;
        $this->dropoffType = $dropoffType;
        $this->consigneeAddress = $consigneeAddress;
        $this->pickupData = $pickupData;
        $this->senderAddress = $senderAddress;
        $this->shipmentDetails = $shipmentDetails;
    }

    public function getPickupAccount(): string
    {
        return $this->pickupAccount;
    }

    public function getDropoffType(): string
    {
        return $this->dropoffType;
    }

    public function getConsigneeAddress(): ConsigneeAddress
    {
        return $this->consigneeAddress;
    }

    public function getPickupData(): PickupData
    {
        return $this->pickupData;
    }

    public function getSenderAddress(): SenderAddress
    {
        return $this->senderAddress;
    }

    public function getShipmentDetails(): ShipmentDetails
    {
        return $this->shipmentDetails;
    }

    public function toArray()
    {
        return [
            "pickupAccount" => $this->getPickupAccount(),
            "dropoffType" => $this->getDropoffType(),
            "pickup" => $this->getPickupData()->toArray(),
            "senderAddress" => $this->getSenderAddress()->toArray(),
            "shipments" => [
                [
                    "consigneeAddress" => $this->getConsigneeAddress()->toArray(),
                    "shipmentDetails" => $this->getShipmentDetails()->toArray(),
                ],
            ],
        ];
    }
}
