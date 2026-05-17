<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Util;

final class PostcodeHelper
{
    private string $postCode;

    public function __construct(string $postcode)
    {
        $this->postCode = $postcode;
    }

    public function getZone(): string
    {
        $normalised = strtoupper(preg_replace('/\s+/', '', $this->getPostCode()));
        if ($normalised === '') {
            return 'A';
        }

        // Zone C — Northern Ireland
        foreach ($this->getZoneCPrefixes() as $prefix) {
            if (str_starts_with($normalised, $prefix)) {
                return 'C';
            }
        }

        // Zone D
        foreach ($this->getZoneDPrefixes() as $prefix) {
            if (str_starts_with($normalised, $prefix)) {
                return 'D';
            }
        }

        return 'A';
    }

    public function isNorthernIreland(): bool
    {
        return $this->getZone() === 'C';
    }

    protected function getZoneCPrefixes(): array
    {
        return ['BT'];
    }

    protected function getZoneDPrefixes(): array
    {
        return [
            'AB31', 'AB32', 'AB33', 'AB34', 'AB35', 'AB36', 'AB37', 'AB38', 'AB41', 'AB42', 'AB43', 'AB44', 'AB45', 'AB46',
            'AB47', 'AB48', 'AB49', 'AB50', 'AB51', 'AB52', 'AB53', 'AB54', 'AB55', 'AB56', 'HS', 'IV', 'KA27', 'KA28', 'KW',
            'PA21', 'PA22', 'PA23', 'PA24', 'PA25', 'PA26', 'PA27', 'PA28', 'PA29', 'PA30', 'PA31', 'PA32', 'PA33', 'PA34',
            'PA35', 'PA36', 'PA37', 'PA38', 'PA39', 'PA40', 'PA41', 'PA42', 'PA43', 'PA44', 'PA45', 'PA46', 'PA47', 'PA48',
            'PA49', 'PA50', 'PA60', 'PA61', 'PA62', 'PA63', 'PA64', 'PA65', 'PA66', 'PA67', 'PA68', 'PA69', 'PA70', 'PA71',
            'PA72', 'PA73', 'PA74', 'PA75', 'PA76', 'PA77', 'PA78', 'PA80', 'PH4', 'PH5', 'PH6', 'PH7', 'PH8', 'PH9',
            'PH10', 'PH11', 'PH12', 'PH13', 'PH14', 'PH15', 'PH16', 'PH17', 'PH18', 'PH19', 'PH20', 'PH21', 'PH22', 'PH23',
            'PH24', 'PH25', 'PH26', 'PH27', 'PH28', 'PH29', 'PH30', 'PH31', 'PH32', 'PH33', 'PH34', 'PH35', 'PH36', 'PH37',
            'PH38', 'PH39', 'PH40', 'PH41', 'PH42', 'PH43', 'PH44', 'PH49', 'PH50', 'ZE1', 'ZE2', 'ZE3', 'IM1', 'IM2', 'IM3',
            'IM4', 'IM5', 'IM6', 'IM7', 'IM8', 'IM9', 'GY1', 'GY2', 'GY3', 'GY4', 'GY5', 'GY6', 'GY7', 'GY8', 'GY9', 'GY10',
            'JE1', 'JE2', 'JE3', 'JE4', 'JE5',
        ];
    }

    /**
     * @return string
     */
    private function getPostCode(): string
    {
        return $this->postCode;
    }
}
