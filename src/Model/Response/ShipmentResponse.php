<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model\Response;

class ShipmentResponse
{
    public function __construct(
        private readonly string $shipmentId,
        private readonly array $labels,
    ) {}

    /**
     * @return string
     */
    public function getShipmentId(): string
    {
        return $this->shipmentId;
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Create from DHL API response array.
     *
     * @param array $response The API response
     * @return self
     */
    public static function fromArray(array $response): self
    {
        return new self(
            shipmentId: $response['shipments'][0]['shipmentId'] ?? '',
            labels: $response['shipments'][0]['labels'] ?? [],
        );
    }
}
