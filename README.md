# DHL Symfony Bundle

Modern Symfony 7+ bundle for DHL API integration. Create shipment labels, track parcels, and manage DHL shipments with ease.

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)](https://php.net)
[![Symfony](https://img.shields.io/badge/symfony-%5E7.0-blue)](https://symfony.com)

## Features

- 🚀 Create DHL shipments
- 📦 Generate and download shipping labels
- 🔍 Track shipments
- 🧪 Sandbox mode for testing
- ⚡ Modern Symfony 7 integration
- 🔐 Secure API authentication

## Requirements

- PHP 8.2 or higher
- Symfony 7.0 or higher
- DHL Developer Account ([Sign up here](https://developer.dhl.com/))

## Installation

### Step 1: Install the Bundle

```bash
composer require omobude/dhl-symfony-bundle
```

### Step 2: Create Configuration File

Create a new file `config/packages/omobude_dhl.yaml`:

```yaml
# config/packages/omobude_dhl.yaml
omobude_dhl:
    api_key: '%env(DHL_API_KEY)%'
    api_secret: '%env(DHL_API_SECRET)%'
    account_number: '%env(DHL_ACCOUNT_NUMBER)%'
    sandbox: true  # Set to false for production
```

### Step 3: Add DHL Credentials to Environment Variables

Add these lines to your `.env` file:

```env
###> omobude/dhl-symfony-bundle ###
DHL_API_KEY=your_dhl_api_key_here
DHL_API_SECRET=your_dhl_api_secret_here
DHL_ACCOUNT_NUMBER=your_dhl_account_number_here
###< omobude/dhl-symfony-bundle ###
```

### Step 4: Clear Cache

```bash
php bin/console cache:clear
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| `api_key` | string | Yes | - | Your DHL API Key |
| `api_secret` | string | Yes | - | Your DHL API Secret |
| `account_number` | string | Yes | - | Your DHL Account Number |
| `sandbox` | boolean | No | `true` | Use sandbox mode for testing |
| `api_url` | string | No | `https://api-sandbox.dhl.com` | DHL API endpoint URL |

## Usage

### Creating a Shipment

```php
<?php

namespace App\Controller;

use Omobude\DhlBundle\Service\DhlApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShippingController extends AbstractController
{
    #[Route('/create-shipment', name: 'create_shipment')]
    public function createShipment(DhlApiClient $dhlClient): Response
    {
        $shipmentData = [
            'shipmentDetails' => [
                'product' => 'P',
                'accountNumber' => 'your_account_number',
                'date' => date('Y-m-d'),
            ],
            'shipper' => [
                'name' => 'Sender Name',
                'addressLine1' => '123 Sender Street',
                'city' => 'London',
                'postalCode' => 'SW1A 1AA',
                'countryCode' => 'GB',
            ],
            'recipient' => [
                'name' => 'Recipient Name',
                'addressLine1' => '456 Recipient Avenue',
                'city' => 'Manchester',
                'postalCode' => 'M1 1AA',
                'countryCode' => 'GB',
            ],
            'packages' => [
                [
                    'weight' => 5.0,
                    'dimensions' => [
                        'length' => 30,
                        'width' => 20,
                        'height' => 15,
                    ],
                ],
            ],
        ];

        try {
            $result = $dhlClient->createShipment($shipmentData);
            
            return $this->json([
                'success' => true,
                'shipment_id' => $result['shipmentId'],
                'tracking_number' => $result['trackingNumber'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### Getting a Shipping Label

```php
#[Route('/get-label/{shipmentId}', name: 'get_label')]
public function getLabel(string $shipmentId, DhlApiClient $dhlClient): Response
{
    try {
        $label = $dhlClient->getLabel($shipmentId);
        
        return new Response(
            $label,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="shipping-label.pdf"',
            ]
        );
    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 400);
    }
}
```

### Tracking a Shipment

```php
#[Route('/track/{trackingNumber}', name: 'track_shipment')]
public function trackShipment(string $trackingNumber, DhlApiClient $dhlClient): Response
{
    try {
        $trackingInfo = $dhlClient->trackShipment($trackingNumber);
        
        return $this->json([
            'success' => true,
            'tracking_info' => $trackingInfo,
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 400);
    }
}
```

## Getting DHL API Credentials

1. Visit [DHL Developer Portal](https://developer.dhl.com/)
2. Create an account or sign in
3. Create a new application
4. Note down your:
    - API Key
    - API Secret
    - Account Number
5. Start with sandbox mode for testing

## Switching to Production

Once you're ready for production:

1. Update your `.env` file with production credentials
2. Change `sandbox: false` in `config/packages/omobude_dhl.yaml`
3. Update the `api_url` if needed:

```yaml
omobude_dhl:
    api_key: '%env(DHL_API_KEY)%'
    api_secret: '%env(DHL_API_SECRET)%'
    account_number: '%env(DHL_ACCOUNT_NUMBER)%'
    sandbox: false
    api_url: 'https://api.dhl.com'  # Production URL
```

## Testing

```bash
# Run tests
./vendor/bin/phpunit
```

## Troubleshooting

### "Class DhlApiClient does not exist"

Clear your cache:
```bash
php bin/console cache:clear
composer dump-autoload
```

### "The child config api_key must be configured"

Make sure you've:
1. Created `config/packages/omobude_dhl.yaml`
2. Added DHL credentials to your `.env` file
3. Cleared the cache

### Authentication Errors

- Verify your API credentials are correct
- Check if you're using sandbox credentials in sandbox mode
- Ensure your DHL account is active

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## Author

**Omobude Kelly**

- GitHub: [@komobude2021](https://github.com/komobude2021)

## Support

If you encounter any issues or have questions:

- Open an issue on [GitHub Issues](https://github.com/komobude2021/dhl-symfony-bundle/issues)
- Check the [DHL API Documentation](https://developer.dhl.com/api-reference)

## Roadmap

- [ ] Support for multiple DHL services (Express, Parcel, eCommerce)
- [ ] Bulk shipment creation
- [ ] Webhook support for tracking updates
- [ ] Address validation
- [ ] Rate calculation
- [ ] Return label generation
- [ ] Comprehensive test coverage

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

---

Made with ❤️ for the Symfony community
