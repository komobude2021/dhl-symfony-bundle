<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model;

class ConsigneeAddress
{
    private string $recipientType;
    private string $addressType;
    private string $address1;
    private ?string $address2;
    private string $city;
    private string $postalCode;
    private string $country;
    private string $name;
    private string $phone;
    private string $email;

    public function __construct(
        string $name,
        string $address1,
        string $city,
        string $postalCode,
        string $country,
        string $phone,
        string $email,
        string $recipientType,
        string $addressType,
        ?string $address2 = null
    ) {
        $this->recipientType = $recipientType;
        $this->addressType = $addressType;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
    }

    public function getRecipientType(): string
    {
        return $this->recipientType;
    }

    public function getAddressType(): string
    {
        return $this->addressType;
    }

    public function getAddress1(): string
    {
        return $this->address1;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setRecipientType(string $recipientType): self
    {
        $this->recipientType = $recipientType;
        return $this;
    }

    public function setAddressType(string $addressType): self
    {
        $this->addressType = $addressType;
        return $this;
    }

    public function setAddress1(string $address1): self
    {
        $this->address1 = $address1;
        return $this;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;
        return $this;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function toArray(): array
    {
        $data = [
            'recipientType' => $this->getRecipientType(),
            'addressType' => $this->getAddressType(),
            'address1' => $this->getAddress1(),
            'city' => $this->getCity(),
            'postalCode' => $this->getPostalCode(),
            'country' => $this->getCountry(),
            'name' => $this->getName(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
        ];

        if ($this->getAddress2() !== null) {
            $data['address2'] = $this->getAddress2();
        }

        return $data;
    }
}
