<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model;

use Omobude\DhlBundle\Util\PostcodeHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ShipmentData
{
    private string $pickupAccount;
    private string $dropoffType;
    private ConsigneeAddress $consigneeAddress;
    private PickupData $pickupData;
    private SenderAddress $senderAddress;
    private ShipmentDetails $shipmentDetails;
    private ?ClearanceDeclaration $clearanceDeclaration;

    public function __construct(
        string $pickupAccount,
        string $dropoffType,
        ConsigneeAddress $consigneeAddress,
        PickupData $pickupData,
        SenderAddress $senderAddress,
        ShipmentDetails $shipmentDetails,
        ?ClearanceDeclaration $clearanceDeclaration = null,
    ) {
        $this->pickupAccount = $pickupAccount;
        $this->dropoffType = $dropoffType;
        $this->consigneeAddress = $consigneeAddress;
        $this->pickupData = $pickupData;
        $this->senderAddress = $senderAddress;
        $this->shipmentDetails = $shipmentDetails;
        $this->clearanceDeclaration = $clearanceDeclaration;
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

    public function getClearanceDeclaration(): ?ClearanceDeclaration
    {
        return $this->clearanceDeclaration;
    }

    public function toArray(): array
    {
        $deliveryPostCode = $this->getConsigneeAddress()->getPostalCode();
        $postCodeHelper = new PostcodeHelper($deliveryPostCode);

        $isNorthernIreland = $postCodeHelper->isNorthernIreland();
        if ($isNorthernIreland && $this->getClearanceDeclaration() === null) {
            throw new BadRequestHttpException(sprintf('Clearance declaration is required for Northern Ireland deliveries (postcode: %s).', $deliveryPostCode));
        }

        $shipment = [
            'consigneeAddress' => $this->getConsigneeAddress()->toArray(),
            'shipmentDetails'  => $this->getShipmentDetails()->toArray(),
        ];

        if ($this->getClearanceDeclaration() !== null) {
            $shipment['clearanceDeclaration'] = $this->getClearanceDeclaration()->toArray();
        }

        return [
            'pickupAccount' => $this->getPickupAccount(),
            'dropoffType'   => $this->getDropoffType(),
            'pickup'        => $this->getPickupData()->toArray(),
            'senderAddress' => $this->getSenderAddress()->toArray(),
            'shipments'     => [$shipment],
        ];
    }
}
