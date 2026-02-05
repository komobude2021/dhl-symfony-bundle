# DHL Symfony Bundle

Modern Symfony 7+ bundle for DHL API integration.

## Installation
```bash
composer require omobude/dhl-symfony-bundle
```

## Configuration
```yaml
# config/packages/omobude_dhl.yaml
omobude_dhl:
    api_key: '%env(DHL_API_KEY)%'
    api_secret: '%env(DHL_API_SECRET)%'
    account_number: '%env(DHL_ACCOUNT_NUMBER)%'
    sandbox: true
```

## Usage
```php
// In your controller
public function createShipment(DhlApiClient $dhlClient)
{
    $shipment = $dhlClient->createShipment([
        // shipment data
    ]);
}
```

## Documentation

Coming soon...

## License

MIT