<?php

namespace Omobude\DhlBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DhlApiClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $apiSecret,
        private string $accountNumber,
        private bool $sandbox,
        private string $apiUrl
    ) {
    }

    public function createShipment(array $shipmentData): array
    {
        // TODO: Implement DHL API call
        $response = $this->httpClient->request('POST', $this->apiUrl . '/shipments', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
                'Content-Type' => 'application/json',
            ],
            'json' => $shipmentData,
        ]);

        return $response->toArray();
    }

    public function getLabel(string $shipmentId): string
    {
        // TODO: Implement label retrieval
        return '';
    }

    public function trackShipment(string $trackingNumber): array
    {
        // TODO: Implement tracking
        return [];
    }
}
