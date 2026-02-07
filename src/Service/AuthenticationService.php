<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Service;

use Omobude\DhlBundle\Constant\DhlEndpoints;
use Omobude\DhlBundle\Exception\DhlAuthenticationException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AuthenticationService
{
    private const TOKEN_CACHE_KEY = 'dhl_access_token';
    private const TOKEN_EXPIRY_SECONDS = 3500; // ~58 minutes (DHL tokens expire in 60 minutes)

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly bool $sandbox,
        private readonly CacheInterface $cache,
        private readonly ?LoggerInterface $logger = null
    ) {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new \RuntimeException(
                'DHL bundle is not configured. Please set client_id and client_secret in config/packages/omobude_dhl.yaml'
            );
        }
    }

    /**
     * Gets a valid access token, using cache or authenticating if needed.
     *
     * @return string The access token
     * @throws DhlAuthenticationException
     */
    public function getAccessToken(): string
    {
        try {
            return $this->cache->get(self::TOKEN_CACHE_KEY, function (ItemInterface $item): string {
                $item->expiresAfter(self::TOKEN_EXPIRY_SECONDS);

                $this->logger?->info('DHL access token cache miss, authenticating...');

                return $this->authenticate();
            });

        } catch (\Exception $ex) {
            $this->logger?->error('Failed to get DHL access token', [
                'error' => $ex->getMessage(),
            ]);
            throw new DhlAuthenticationException(
                sprintf('Failed to retrieve access token: %s', $ex->getMessage()),
                $ex->getCode(),
                $ex
            );
        }
    }

    /**
     * Forces a new authentication and refreshes the cached token.
     *
     * @return string The new access token
     * @throws DhlAuthenticationException
     */
    public function refreshAccessToken(): string
    {
        try {
            $this->cache->delete(self::TOKEN_CACHE_KEY);

            $this->logger?->info('Forcing DHL access token refresh');

            return $this->getAccessToken();
        } catch (\Exception $ex) {
            $this->logger?->error('Failed to refresh DHL access token', [
                'error' => $ex->getMessage(),
            ]);
            throw new DhlAuthenticationException(
                sprintf('Failed to refresh access token: %s', $ex->getMessage()),
                $ex->getCode(),
                $ex
            );
        }
    }

    /**
     * Authenticates with DHL API and returns an access token.
     *
     * @return string The access token
     * @throws DhlAuthenticationException
     */
    private function authenticate(): string
    {
        try {
            $endpoint = sprintf('%s%s', DhlEndpoints::getApiUrl($this->sandbox), DhlEndpoints::getAuthTokenEndpoint());

            $response = $this->httpClient->request('POST', $endpoint, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $errorBody = $response->toArray(false);

                $this->logger?->error('DHL authentication failed', [
                    'status_code' => $statusCode,
                    'error' => $errorBody,
                ]);

                throw new DhlAuthenticationException(
                    sprintf(
                        'DHL authentication failed with status code %d: %s',
                        $statusCode,
                        json_encode($errorBody)
                    ),
                    $statusCode
                );
            }

            $data = $response->toArray();

            if (!isset($data['access_token'])) {
                throw new DhlAuthenticationException('Access token not found in response');
            }

            $this->logger?->info('DHL authentication successful');

            return $data['access_token'];

        } catch (TransportExceptionInterface $ex) {
            $this->logger?->error('DHL authentication transport error', [
                'error' => $ex->getMessage(),
            ]);
            throw new DhlAuthenticationException(
                sprintf('Transport error during authentication: %s', $ex->getMessage()),
                $ex->getCode(),
                $ex
            );
        } catch (\Exception $ex) {
            if ($ex instanceof DhlAuthenticationException) {
                throw $ex;
            }

            $this->logger?->error('DHL authentication error', [
                'error' => $ex->getMessage(),
            ]);
            throw new DhlAuthenticationException(
                sprintf('Authentication failed: %s', $ex->getMessage()),
                $ex->getCode(),
                $ex
            );
        }
    }

    /**
     * Clears the cached access token.
     *
     * @return void
     */
    public function clearCache(): void
    {
        try {
            $this->cache->delete(self::TOKEN_CACHE_KEY);
            $this->logger?->info('DHL access token cache cleared');
        } catch (\Exception $ex) {
            $this->logger?->warning('Failed to clear DHL access token cache', [
                'error' => $ex->getMessage(),
            ]);
        }
    }
}
