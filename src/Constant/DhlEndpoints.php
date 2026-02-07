<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Constant;

final class DhlEndpoints
{
    private const SANDBOX_API_URL = 'https://api-uat.dhl.com'; // UAT Environment
    private const PROD_API_URL = 'https://api.dhl.com'; // Production Environment
    private const ENDPOINT_AUTH_TOKEN = '/parceluk/auth/v1/accesstoken';
    private const ENDPOINT_CREATE_SHIPMENT = '/parceluk/shipping/v1/label';
    private const ENDPOINT_GET_LABEL = '/parceluk/reprintlabels/v1/labels?shipmentId=%s&format=%s';
    private const ENDPOINT_CANCEL_SHIPMENT = '/parceluk/shipping/v1/shipments/%s';
    private const ENDPOINT_TRACK_SHIPMENT = '/parceluk/tracking/v1/shipments?trackingNumber=%s';

    /**
     * Get the appropriate API URL based on environment.
     *
     * @param bool $sandbox Whether to use sandbox environment
     * @return string The API base URL
     */
    public static function getApiUrl(bool $sandbox): string
    {
        return $sandbox ? self::SANDBOX_API_URL : self::PROD_API_URL;
    }

    /**
     * Get the auth token endpoint.
     * @return string The auth token endpoint
     */
    public static function getAuthTokenEndpoint()
    {
        return self::ENDPOINT_AUTH_TOKEN;
    }

    /**
     * Format the create shipment endpoint with query parameters.
     *
     * @param string $format Label format (PDF, PNG, ZPL)
     * @return string The formatted endpoint
     */
    public static function getCreateShipmentEndpoint(string $format = 'PDF'): string
    {
        return self::ENDPOINT_CREATE_SHIPMENT . '?format=' . strtoupper($format);
    }

    /**
     * Format the get label endpoint with shipment ID and format.
     *
     * @param string $shipmentId The DHL shipment ID
     * @param string $format Label format (PDF, PNG, ZPL)
     * @return string The formatted endpoint
     */
    public static function getGetLabelEndpoint(string $shipmentId, string $format = 'PDF'): string
    {
        return sprintf(self::ENDPOINT_GET_LABEL, $shipmentId, urlencode($format));
    }

    /**
     * Format the track shipment endpoint with tracking number.
     *
     * @param string $trackingNumber The DHL tracking number
     * @return string The formatted endpoint
     */
    public static function getTrackShipmentEndpoint(string $trackingNumber): string
    {
        return sprintf(self::ENDPOINT_TRACK_SHIPMENT, $trackingNumber);
    }

    /**
     * Format the cancel shipment endpoint with shipment ID.
     *
     * @param string $shipmentId The DHL shipment ID
     * @return string The formatted endpoint
     */
    public static function getCancelShipmentEndpoint(string $shipmentId): string
    {
        return sprintf(self::ENDPOINT_CANCEL_SHIPMENT, $shipmentId);
    }
}
