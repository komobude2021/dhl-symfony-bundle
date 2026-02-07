<?php
declare(strict_types=1);

namespace Omobude\DhlBundle\Model;

class PickupData
{
    private \DateTimeImmutable $date;
    private bool $accountAddress;

    public function __construct(\DateTimeImmutable $date, bool $accountAddress = true)
    {
        $this->date = $date;
        $this->accountAddress = $accountAddress;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function isAccountAddress(): bool
    {
        return $this->accountAddress;
    }

    public function setAccountAddress(bool $accountAddress): self
    {
        $this->accountAddress = $accountAddress;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'date' => $this->getDate()->format('Y-m-d'),
            'accountAddress' => $this->isAccountAddress(),
        ];
    }
}
