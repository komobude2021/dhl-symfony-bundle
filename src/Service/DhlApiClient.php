<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Service;

use Omobude\DhlBundle\Constant\DhlEndpoints;
use Omobude\DhlBundle\Exception\DhlApiException;
use Omobude\DhlBundle\Exception\DhlAuthenticationException;
use Omobude\DhlBundle\Exception\DhlDownloadLabelException;
use Omobude\DhlBundle\Model\ShipmentData;
use Omobude\DhlBundle\Model\Response\ShipmentResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class DhlApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly AuthenticationService $authService,
        private readonly SaveShippingLabel $saveShippingLabel,
        private readonly bool $sandbox,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * Creates a DHL shipment and returns the shipment details.
     *
     * @param ShipmentData $shipmentData The shipment data to send to DHL
     * @param string $format The label format (PDF, PNG, ZPL)
     * @return ShipmentResponse A response object containing:
     *                          - Shipment ID (for label retrieval and cancellation)
     *                          - Label data (base64 encoded)
     * @throws DhlApiException
     * @throws DhlAuthenticationException
     */
    public function createShipment(ShipmentData $shipmentData, string $format = 'PDF'): ShipmentResponse
    {
        try {
            $this->logger?->info('Creating DHL shipment', [
                'shipment_data' => $shipmentData->toArray(),
                'format' => $format,
            ]);

            $response = $this->getHttpClient()->request('POST', DhlEndpoints::getCreateShipmentEndpoint($format), [
                'json' => $shipmentData->toArray(),
            ]);

            $statusCode = $response->getStatusCode();
            if (!in_array($statusCode, [200, 201, 202], true)) {
                $this->handleErrorResponse($response, 'Failed to create shipment');
            }

            $data = $response->toArray();

            $this->logger?->info('DHL shipment created successfully', [
                'shipment_id' => $data['shipmentId'] ?? 'unknown',
            ]);

            return ShipmentResponse::fromArray($data);

        } catch (TransportExceptionInterface $ex) {
            $this->logger?->error('DHL API transport error', [
                'error' => $ex->getMessage(),
            ]);
            throw new DhlApiException(sprintf('Transport error: %s', $ex->getMessage()), $ex->getCode(), $ex);
        } catch (DhlAuthenticationException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            $this->logger?->error('DHL API error', [
                'error' => $ex->getMessage(),
            ]);
            throw new DhlApiException(sprintf('Failed to create shipment: %s', $ex->getMessage()), $ex->getCode(), $ex);
        }
    }

    /**
     * Retrieves the shipping label for a given shipment ID and returns as BinaryFileResponse.
     *
     * @param string $shipmentId The DHL shipment ID
     * @param string $format The label format (PDF, PNG, ZPL)
     * @return BinaryFileResponse The label file response
     * @throws DhlApiException
     * @throws DhlAuthenticationException
     * @throws DhlDownloadLabelException
     */
    public function getLabel(string $shipmentId, string $format = 'PDF'): BinaryFileResponse
    {
        try {
            $this->logger?->info('Retrieving DHL label', [
                'shipment_id' => $shipmentId,
                'format' => $format,
            ]);

            $response = $this->getHttpClient()->request('GET', DhlEndpoints::getGetLabelEndpoint($shipmentId, $format));

            if ($response->getStatusCode() !== 200) {
                throw new DhlApiException(
                    sprintf('DHL get label API request failed with status code: %d', $response->getStatusCode()),
                    $response->getStatusCode()
                );
            }

            $dhlResponse = $response->toArray();

            if (!isset($dhlResponse['labels'][0]['label'])) {
                throw new DhlDownloadLabelException('Label data not found in DHL API response');
            }

            $base64Label = $dhlResponse['labels'][0]['label'];

            $fileName = sprintf('%s.%s', $shipmentId, strtolower($format));
            $filePath = $this->saveShippingLabel->saveShippingLabel($base64Label, $fileName);

            $this->logger?->info('DHL label retrieved successfully', [
                'shipment_id' => $shipmentId,
                'file_path' => $filePath,
            ]);

            $contentType = match (strtoupper($format)) {
                'PDF' => 'application/pdf',
                'PNG' => 'image/png',
                'ZPL' => 'application/zpl',
                default => 'application/octet-stream',
            };

            $response = new BinaryFileResponse($filePath, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => sprintf('inline; filename="%s"', $fileName),
            ]);

            $response->deleteFileAfterSend(true);

            return $response;

        } catch (TransportExceptionInterface $ex) {
            $this->logger?->error('DHL API transport error', [
                'error' => $ex->getMessage(),
                'shipment_id' => $shipmentId,
            ]);
            throw new DhlApiException(sprintf('Transport error: %s', $ex->getMessage()), $ex->getCode(), $ex);
        } catch (DhlAuthenticationException | DhlDownloadLabelException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            $this->logger?->error('DHL API error', [
                'error' => $ex->getMessage(),
                'shipment_id' => $shipmentId,
            ]);
            throw new DhlApiException(sprintf('Failed to retrieve label: %s', $ex->getMessage()), $ex->getCode(), $ex);
        }
    }

    /**
     * Retrieves the shipping label content as a string (base64 decoded).
     * Use this when you want to handle the label content directly.
     *
     * @param string $shipmentId The DHL shipment ID
     * @param string $format The label format (PDF, PNG, ZPL)
     * @return string The label content (binary)
     * @throws DhlApiException
     * @throws DhlAuthenticationException
     * @throws DhlDownloadLabelException
     */
    public function getLabelContent(string $shipmentId, string $format = 'PDF'): string
    {
        try {
            $this->logger?->info('Retrieving DHL label content', [
                'shipment_id' => $shipmentId,
                'format' => $format,
            ]);

            $response = $this->getHttpClient()->request('GET', DhlEndpoints::getGetLabelEndpoint($shipmentId, $format));

            if ($response->getStatusCode() !== 200) {
                throw new DhlApiException(
                    sprintf('DHL get label API request failed with status code: %d', $response->getStatusCode()),
                    $response->getStatusCode()
                );
            }

            $dhlResponse = $response->toArray();

            if (!isset($dhlResponse['labels'][0]['label'])) {
                throw new DhlDownloadLabelException('Label data not found in DHL API response');
            }

            $base64Label = $dhlResponse['labels'][0]['label'];
            $decodedLabel = base64_decode($base64Label, true);

            if ($decodedLabel === false) {
                throw new DhlDownloadLabelException('Failed to decode label content');
            }

            $this->logger?->info('DHL label content retrieved successfully', [
                'shipment_id' => $shipmentId,
                'size_bytes' => strlen($decodedLabel),
            ]);

            return $decodedLabel;

        } catch (DhlAuthenticationException | DhlDownloadLabelException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            $this->logger?->error('DHL API error', [
                'error' => $ex->getMessage(),
                'shipment_id' => $shipmentId,
            ]);
            throw new DhlApiException(sprintf('Failed to retrieve label content: %s', $ex->getMessage()), $ex->getCode(), $ex);
        }
    }

    /**
     * Gets the configured HTTP client with proper headers and base URI.
     * Uses OAuth Bearer token for authentication.
     *
     * @return HttpClientInterface
     * @throws DhlAuthenticationException
     */
    private function getHttpClient(): HttpClientInterface
    {
        $accessToken = $this->authService->getAccessToken();

        return $this->httpClient->withOptions([
            'base_uri' => DhlEndpoints::getApiUrl($this->sandbox),
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $accessToken),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'timeout' => 60,
        ]);
    }

    /**
     * Checks if the client is in sandbox mode.
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * Handles error responses from the DHL API.
     *
     * @param \Symfony\Contracts\HttpClient\ResponseInterface $response
     * @param string $message
     * @throws DhlApiException
     */
    private function handleErrorResponse($response, string $message): void
    {
        try {
            $errorBody = $response->toArray(false);

            $errorMessages = [];
            if (isset($errorBody['errors']) && is_array($errorBody['errors'])) {
                foreach ($errorBody['errors'] as $error) {
                    if (isset($error['title'], $error['detail'])) {
                        $errorMessages[] = sprintf('%s: %s', $error['title'], $error['detail']);
                    }
                }
            } elseif (isset($errorBody['message'])) {
                $errorMessages[] = $errorBody['message'];
            }

            $formattedError = !empty($errorMessages)
                ? implode(' | ', $errorMessages)
                : json_encode($errorBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            throw new DhlApiException(
                sprintf(
                    '%s (Status: %d) - %s',
                    $message,
                    $response->getStatusCode(),
                    $formattedError
                ),
                $response->getStatusCode()
            );
        } catch (\JsonException $ex) {
            throw new DhlApiException(
                sprintf(
                    '%s (Status: %d) - Could not parse error response',
                    $message,
                    $response->getStatusCode()
                ),
                $response->getStatusCode()
            );
        }
    }
}
