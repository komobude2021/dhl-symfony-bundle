# DHL Symfony Bundle

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)](https://php.net)
[![Symfony](https://img.shields.io/badge/symfony-%5E7.0-blue)](https://symfony.com)

Modern Symfony 7+ bundle for DHL API integration. Create shipment labels, view and download DHL shipments label with ease using OAuth authentication.

## Features

- 🚀 **Create DHL shipments**
- 📦 **Download shipping labels** (PDF)
- 🔐 **OAuth 2.0 authentication** with automatic token management
- ⚡ **Token caching** for optimal performance
- 🧪 **Sandbox mode** for testing
- 📝 **Comprehensive logging** support
- 🎯 **Type-safe models** with PHP 8.2+
- 🇬🇧 **Northern Ireland clearance declaration** support (Windsor Framework / UKIMS)
- 🔄 **Modern Symfony 7 integration**

## Requirements

- PHP 8.2 or higher
- Symfony 7.0 or higher
- Symfony Cache component
- DHL Developer Account ([Sign up here](https://developer.dhl.com/))

## Table of Contents

- [Installation](#installation)
- [Getting DHL Credentials](#getting-dhl-credentials)
- [Usage](#usage)
  - [Creating a Shipment](#creating-a-shipment)
  - [Northern Ireland Shipments (Clearance Declaration)](#northern-ireland-shipments-clearance-declaration)
  - [Downloading a Label](#downloading-a-label)
- [Configuration Reference](#configuration-reference)
- [Switching to Production](#switching-to-production)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## Installation

### Step 1: Install the Bundle

```bash
composer require omobude/dhl-symfony-bundle
```

### Step 2: Register the Bundle (If Not Auto-Registered)

If Symfony Flex doesn't automatically register the bundle, manually add it to `config/bundles.php`:
```php
<?php

return [
    // ... other bundles
    Omobude\DhlBundle\OmobudeDhlBundle::class => ['all' => true],
];
```

> **Note:** With Symfony Flex, this step is usually automatic. Only add this manually if you encounter the error: "There is no extension able to load the configuration for 'omobude_dhl'".


### Step 3: Configure Environment Variables

Add your DHL credentials to your `.env` file:

```env
###> omobude/dhl-symfony-bundle ###
DHL_CLIENT_ID=your_dhl_client_id_here
DHL_CLIENT_SECRET=your_dhl_client_secret_here
DHL_CLIENT_SANDBOX=true
###< omobude/dhl-symfony-bundle ###
```

### Step 4: Create Bundle Configuration

Create the file `config/packages/omobude_dhl.yaml`:

```yaml
omobude_dhl:
    client_id: '%env(DHL_CLIENT_ID)%'
    client_secret: '%env(DHL_CLIENT_SECRET)%'
    sandbox: '%env(bool:DHL_CLIENT_SANDBOX)%'
```

### Step 5: Clear Cache

```bash
php bin/console cache:clear
```

## Getting DHL Credentials

### For Sandbox (Testing)

1. Go to [DHL Developer Portal](https://developer.dhl.com/)
2. Sign up for a free account
3. Create a new application
4. Navigate to your application settings
5. Copy your **Client ID** and **Client Secret**
6. Use these credentials in your `.env` file

### For Production

1. Contact DHL to request production API access
2. Complete any required business verification
3. Receive your production **Client ID** and **Client Secret**
4. Update your production environment variables
5. Set `sandbox: false` in your configuration

## Usage

### Creating a Shipment

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Omobude\DhlBundle\Exception\DhlApiException;
use Omobude\DhlBundle\Exception\DhlAuthenticationException;
use Omobude\DhlBundle\Exception\DhlDownloadLabelException;
use Omobude\DhlBundle\Model\ConsigneeAddress;
use Omobude\DhlBundle\Model\PickupData;
use Omobude\DhlBundle\Model\SenderAddress;
use Omobude\DhlBundle\Model\ShipmentData;
use Omobude\DhlBundle\Model\ShipmentDetails;
use Omobude\DhlBundle\Service\DhlApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ShippingController extends AbstractController
{
    #[Route('/create-shipment', name: 'create_shipment')]
    public function createShipment(DhlApiClient $dhlClient): Response
    {
        try {
            $pickupData = new PickupData(
                date: new \DateTimeImmutable('now', new \DateTimeZone("Europe/London")),
                accountAddress: true
            );
    
            $senderAddress = new SenderAddress(
                companyName: 'XXXXXXXXXX LIMITED',
                address1: 'UNIT 5C, XXXXXXX DRIVE',
                city: 'SHEFFIELD',
                postalCode: 'XXXXX',
                country: 'GB',
                name: 'DISPATCH MANAGER',
                phone: '07443822832',
                email: 'customersupport@xxxxxxxxxx.com',
                address2: 'XXXXXXXXXX HOUSE',
                address3: 'SHEFFIELD'
            );
    
            $consigneeAddress = new ConsigneeAddress(
                name: 'JOHN DOE',
                address1: '123 CUSTOMER STREET',
                city: 'LONDON',
                postalCode: 'XXXXXX',
                country: 'GB',
                phone: '07123456789',
                email: 'customer@example.com',
                recipientType: 'residential',
                addressType: 'doorstep',
                address2: 'APARTMENT 4B'
            );
    
            $shipmentDetails = new ShipmentDetails(
                customerRef1: 'TN-' . date('YmdHis'),
                customerRef2: substr(md5(uniqid()), 0, 8),
                orderedProduct: '1', // 1 = Next day, 48 = 48 hours
                totalPieces: 1,
                totalWeight: 5.5
            );
    
            $shipmentData = new ShipmentData(
                pickupAccount: 'XXXXXXX',
                dropoffType: 'PICKUP',
                consigneeAddress: $consigneeAddress,
                pickupData: $pickupData,
                senderAddress: $senderAddress,
                shipmentDetails: $shipmentDetails,
            );

            // Create shipment with PDF label
            $result = $dhlClient->createShipment($shipmentData);

            return $this->json([
                'success' => true,
                'shipment_id' => $result->getShipmentId(),
                'message' => 'Shipment created successfully',
            ]);
            
        } catch (DhlApiException|DhlAuthenticationException $ex) {
            return $this->json([
                'success' => false,
                'error' => $ex->getMessage(),
                'code' => $ex->getCode(),
            ], $ex->getCode());
        }
    }
}
```

### Northern Ireland Shipments (Clearance Declaration)

Shipments delivered to **Northern Ireland** (postcodes starting with `BT`) require an additional **clearance declaration** under the Windsor Framework / UK Internal Market Scheme (UKIMS). The bundle automatically detects Northern Ireland postcodes and will throw a `BadRequestHttpException` if a clearance declaration is missing.

#### When is a Clearance Declaration Required?

| Destination | Postcode Prefix | Clearance Declaration |
|-------------|-----------------|-----------------------|
| Great Britain (England, Scotland, Wales) | Most UK postcodes | ❌ Not required |
| Northern Ireland | `BT` | ✅ **Required** |

#### Building a Clearance Declaration

A clearance declaration is composed of two model classes:

- `ClearanceDeclaration` — top-level customs information for the shipment
- `ClearanceItem` — line-item details for each product in the shipment

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Omobude\DhlBundle\Exception\DhlApiException;
use Omobude\DhlBundle\Exception\DhlAuthenticationException;
use Omobude\DhlBundle\Model\ClearanceDeclaration;
use Omobude\DhlBundle\Model\ClearanceItem;
use Omobude\DhlBundle\Model\ConsigneeAddress;
use Omobude\DhlBundle\Model\PickupData;
use Omobude\DhlBundle\Model\SenderAddress;
use Omobude\DhlBundle\Model\ShipmentData;
use Omobude\DhlBundle\Model\ShipmentDetails;
use Omobude\DhlBundle\Service\DhlApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ShippingController extends AbstractController
{
    #[Route('/create-ni-shipment', name: 'create_ni_shipment')]
    public function createNorthernIrelandShipment(DhlApiClient $dhlClient): Response
    {
        try {
            $pickupData = new PickupData(
                date: new \DateTimeImmutable('now', new \DateTimeZone('Europe/London')),
                accountAddress: true
            );

            $senderAddress = new SenderAddress(
                companyName: 'XXXXXXXXXX LIMITED',
                address1: 'UNIT 5C, XXXXXXX DRIVE',
                city: 'SHEFFIELD',
                postalCode: 'S9 1XX',
                country: 'GB',
                name: 'DISPATCH MANAGER',
                phone: '07443822832',
                email: 'customersupport@xxxxxxxxxx.com',
                address2: 'XXXXXXXXXX HOUSE',
                address3: 'SHEFFIELD'
            );

            // Northern Ireland consignee — postcode starts with "BT"
            $consigneeAddress = new ConsigneeAddress(
                name: 'JANE DOE',
                address1: '45 BELFAST ROAD',
                city: 'BELFAST',
                postalCode: 'BT1 5GS',
                country: 'GB',
                phone: '07123456789',
                email: 'customer@example.com',
                recipientType: 'residential',
                addressType: 'doorstep',
                address2: 'FLAT 2'
            );

            $shipmentDetails = new ShipmentDetails(
                customerRef1: 'NI-' . date('YmdHis'),
                customerRef2: substr(md5(uniqid()), 0, 8),
                orderedProduct: '1',
                totalPieces: 2,
                totalWeight: 3.2
            );

            // Build each line item being shipped
            $items = [
                new ClearanceItem(
                    descriptionOfGoods: 'Cotton T-Shirt',
                    unitQuantity: 1,
                    commodityCode: '6109100010'
                ),
                new ClearanceItem(
                    descriptionOfGoods: 'Wool Scarf',
                    unitQuantity: 1,
                    commodityCode: '6117100000'
                ),
            ];

            // Build the clearance declaration
            $clearanceDeclaration = new ClearanceDeclaration(
                shipmentMovementType: 'B2C',     // B2C or B2B
                totalValue: 49.99,                // Total goods value (excl. shipping)
                numberOfItems: 2,                 // Total number of items
                items: $items,
                sendersEORINumber: 'GB123456789000',     // Replace with your actual EORI
                sendersUKIMSNumber: 'XIUKIM12345678901234567890' // Replace with your actual UKIMS number
            );

            $shipmentData = new ShipmentData(
                pickupAccount: 'XXXXXXX',
                dropoffType: 'PICKUP',
                consigneeAddress: $consigneeAddress,
                pickupData: $pickupData,
                senderAddress: $senderAddress,
                shipmentDetails: $shipmentDetails,
                clearanceDeclaration: $clearanceDeclaration, // <-- required for NI
            );

            $result = $dhlClient->createShipment($shipmentData);

            return $this->json([
                'success' => true,
                'shipment_id' => $result->getShipmentId(),
                'message' => 'Northern Ireland shipment created successfully',
            ]);

        } catch (BadRequestHttpException $ex) {
            // Thrown when clearance declaration is missing for an NI postcode
            return $this->json([
                'success' => false,
                'error' => 'Clearance declaration required',
                'message' => $ex->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (DhlApiException | DhlAuthenticationException $ex) {
            return $this->json([
                'success' => false,
                'error' => $ex->getMessage(),
                'code' => $ex->getCode(),
            ], $ex->getCode() ?: Response::HTTP_BAD_REQUEST);
        }
    }
}
```

#### `ClearanceDeclaration` Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `shipmentMovementType` | string | Yes | Movement type — `B2C` (business to consumer) or `B2B` (business to business) |
| `totalValue` | float | Yes | Total declared value of goods (excluding shipping costs) |
| `numberOfItems` | int | Yes | Total number of items across all line items |
| `items` | `ClearanceItem[]` | Yes | Array of `ClearanceItem` objects, one per line item |
| `sendersEORINumber` | string | No | Sender's EORI (Economic Operators Registration and Identification) number |
| `sendersUKIMSNumber` | string | No | Sender's UKIMS (UK Internal Market Scheme) authorisation number |

> **Note on EORI / UKIMS:** Although marked optional in the model, most B2C movements into Northern Ireland under the Windsor Framework will require a valid UKIMS number to qualify for the green-lane "not at risk" treatment. Check your DHL contract and current HMRC guidance before omitting these fields.

#### `ClearanceItem` Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `descriptionOfGoods` | string | Yes | Plain-English description of the goods (e.g. `"Cotton T-Shirt"`) |
| `unitQuantity` | int | Yes | Quantity of this item being shipped |
| `commodityCode` | string | No | HS / commodity tariff code (e.g. `"6109100010"`). Strongly recommended for customs clearance |

#### Validation Behaviour

The bundle enforces clearance declaration rules at the point of calling `toArray()` on `ShipmentData` (which happens automatically inside `DhlApiClient::createShipment()`):

- ✅ **NI postcode + clearance declaration present** → shipment proceeds
- ❌ **NI postcode + no clearance declaration** → throws `BadRequestHttpException`
- ✅ **GB postcode + no clearance declaration** → shipment proceeds
- ✅ **GB postcode + clearance declaration present** → shipment proceeds (declaration is included)

Always catch `BadRequestHttpException` separately if you want to surface clear validation errors to your users before the request hits the DHL API.

### Downloading a Label

```php
/**
 * Download shipping label as a file (PDF).
 * Returns a BinaryFileResponse that automatically downloads the file.
 */
#[Route('/label/{shipmentId}', name: 'get_label')]
public function getLabel(string $shipmentId, DhlApiClient $dhlClient): Response
{
    try {
            return $dhlClient->getLabel($shipmentId);
            
        } catch (DhlAuthenticationException $ex) {
            return $this->json([
                'success' => false,
                'error' => 'Authentication failed',
                'message' => $ex->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        } catch (DhlDownloadLabelException $ex) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to process label',
                'message' => $ex->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (DhlApiException $ex) {
            return $this->json([
                'success' => false,
                'error' => 'DHL API error',
                'message' => $ex->getMessage(),
            ], $ex->getCode() ?: Response::HTTP_BAD_REQUEST);
        }
}
```

```php
/**
 * Inline display of label (opens in browser).
 */
#[Route('/label/{shipmentId}/view', name: 'view_label', methods: ['GET'])]
public function viewLabel(string $shipmentId, DhlApiClient $dhlClient): BinaryFileResponse|Response
{
    try {
        $response = $dhlClient->getLabel($shipmentId);

        // Change disposition to inline so it opens in browser
        $response->headers->set(
            'Content-Disposition',
            sprintf('inline; filename="label-%s.pdf"', $shipmentId)
        );

        return $response;
        
    } catch (DhlAuthenticationException | DhlDownloadLabelException | DhlApiException $e) {
        return $this->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], $e->getCode() ?: Response::HTTP_BAD_REQUEST);
    }
}
```

### Checking Sandbox Mode

```php
if ($dhlClient->isSandbox()) {
    // Running in sandbox mode
    echo "Testing mode - no real shipments created";
} else {
    // Running in production mode
    echo "Production mode - real shipments will be created";
}
```

## Configuration Reference

| Option | Type | Required | Default | Description |
|--------|------|-----|-------|-------------|
| `client_id` | string | Yes | - | Your DHL OAuth Client ID |
| `client_secret` | string | Yes | - | Your DHL OAuth Client Secret |
| `sandbox` | boolean | Yes |  | Enable sandbox/testing mode |

### DHL Product Codes

Common product codes for `orderedProduct`:

| Code | Service | Delivery Time |
|------|---------|---------------|
| `1` | DHL Parcel | Next day |
| `48` | DHL Parcel Neighbour | 48 hours |

### Recipient Types

Valid values for `recipientType`:
- `residential` - Home delivery
- `business` - Business address

### Address Types

Valid values for `addressType`:
- `doorstep` - Standard delivery
- `neighbour` - Deliver to neighbour if recipient not available

### Shipment Movement Types

Valid values for `ClearanceDeclaration::$shipmentMovementType`:
- `B2C` - Business to consumer
- `B2B` - Business to business

## Switching to Production

### Step 1: Update Environment Variables

Update your production `.env` file:

```env
###> omobude/dhl-symfony-bundle ###
DHL_CLIENT_ID=your_production_client_id
DHL_CLIENT_SECRET=your_production_client_secret
DHL_CLIENT_SANDBOX=false
###< omobude/dhl-symfony-bundle ###
```

### Step 3: Clear Production Cache

```bash
php bin/console cache:clear --env=prod
```

### Step 4: Test in Production

Always test with a single shipment first to ensure everything works correctly.

## Troubleshooting

### Bundle Not Registered

**Problem:** "There is no extension able to load the configuration for 'omobude_dhl'"

**Solution:**
1. Ensure the bundle is registered in `config/bundles.php`:
```php
   Omobude\DhlBundle\OmobudeDhlBundle::class => ['all' => true],
```
2. Clear the cache: `php bin/console cache:clear`
3. Verify installation: `composer show omobude/dhl-symfony-bundle`

### Authentication Errors

**Problem:** "Authentication failed" or "Invalid credentials"

**Solution:**
- Verify your Client ID and Client Secret are correct
- Ensure you're using sandbox credentials with `sandbox: true`
- Check that credentials are properly set in `.env`
- Try clearing the token cache: `php bin/console cache:clear`

### Configuration Errors

**Problem:** "The child config 'client_id' under 'omobude_dhl' must be configured"

**Solution:**
1. Ensure `config/packages/omobude_dhl.yaml` exists
2. Verify the configuration syntax is correct
3. Check that environment variables are defined in `.env`
4. Run `php bin/console debug:config omobude_dhl` to verify

### Northern Ireland Clearance Errors

**Problem:** `BadRequestHttpException: Clearance declaration is required for Northern Ireland deliveries (postcode: BT1 5GS).`

**Solution:**
- The destination postcode starts with `BT` (Northern Ireland), so a `ClearanceDeclaration` must be passed to `ShipmentData`
- See the [Northern Ireland Shipments](#northern-ireland-shipments-clearance-declaration) section for a full example
- Make sure your `ClearanceDeclaration` includes a valid `shipmentMovementType`, `totalValue`, `numberOfItems`, and at least one `ClearanceItem`
- For most B2C movements, also include a valid UKIMS number via `sendersUKIMSNumber`

### Token Caching Issues

**Problem:** "Cached token expired" or authentication errors after some time

**Solution:**
- The bundle automatically refreshes tokens
- Clear cache if issues persist: `php bin/console cache:clear`
- Check cache directory permissions: `var/cache/` should be writable
- Verify `symfony/cache` is installed

### API Errors

**Problem:** DHL API returns error codes

**Solution:**
- Check [DHL API Documentation](https://developer.dhl.com/api-reference) for error codes
- Enable logging to see detailed error messages
- Verify all required fields are provided
- Ensure addresses are in the correct format

### Debugging

Enable detailed logging:

```yaml
# config/packages/monolog.yaml
monolog:
  channels: ['dhl']
  handlers:
    dhl:
      type: stream
      path: '%kernel.logs_dir%/dhl.log'
      level: debug
      channels: ['dhl']
```

Check logs:
```bash
tail -f var/log/dhl.log
```

Verify bundle configuration:
```bash
php bin/console debug:config omobude_dhl
php bin/console debug:container DhlApiClient
```

## Environment-Specific Configuration

### Development

```env
# .env.local
DHL_CLIENT_ID=sandbox_dev_client_id
DHL_CLIENT_SECRET=sandbox_dev_client_secret
DHL_CLIENT_SANDBOX=true
```

### Staging

```env
# .env.staging
DHL_CLIENT_ID=sandbox_staging_client_id
DHL_CLIENT_SECRET=sandbox_staging_client_secret
DHL_CLIENT_SANDBOX=true
```

### Production

```env
# .env.production
DHL_CLIENT_ID=production_client_id
DHL_CLIENT_SECRET=production_client_secret
DHL_CLIENT_SANDBOX=false
```

## Security Best Practices

1. **Never commit credentials** - Add `.env` to `.gitignore`
2. **Use environment variables** - Store credentials in environment, not code
4. **Separate environments** - Use different credentials for dev/staging/prod
5. **Monitor access logs** - Check DHL dashboard for unusual activity
6. **Use HTTPS only** - The bundle uses HTTPS by default
7. **Limit permissions** - Only grant necessary access to DHL accounts

### Recommended .gitignore

```gitignore
.env
.env.local
.env.*.local
.env.production
.env.prod
```

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Write tests for your changes
5. Ensure all tests pass: `./vendor/bin/phpunit`
6. Commit your changes: `git commit -m 'Add amazing feature'`
7. Push to the branch: `git push origin feature/amazing-feature`
8. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Use PHP 8.2+ features (typed properties, readonly, etc.)
- Write PHPDoc comments for all public methods
- Add tests for new features

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## Author

**Omobude Kelly**

- GitHub: [@komobude2021](https://github.com/komobude2021)
- Email: k.omobude2019@gmail.com

## Support

Need help? Here are your options:

- 📖 [Read the Documentation](README.md)
- 🐛 [Report Issues](https://github.com/komobude2021/dhl-symfony-bundle/issues)
- 💬 [GitHub Discussions](https://github.com/komobude2021/dhl-symfony-bundle/discussions)
- 📧 [Email Support](mailto:k.omobude2019@gmail.com)
- 📚 [DHL API Documentation](https://developer.dhl.com/api-reference)

## Acknowledgments

- Built for the Symfony community
- Powered by [DHL API](https://developer.dhl.com/)
- Inspired by modern Symfony best practices

---

**Made with ❤️ for the Symfony community**

If this bundle helped you, please consider giving it a ⭐ on GitHub!
