<?php

declare(strict_types=1);

namespace App\Dto;

class BankAccountDto
{
    private string $account;

    private string $bik;

    private string $bank;

    private string $bankAddr;

    private int $id;

    public function getAccount(): string
    {
        return $this->account;
    }

    public function setAccount(string $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getBik(): string
    {
        return $this->bik;
    }

    public function setBik(string $bik): self
    {
        $this->bik = $bik;

        return $this;
    }

    public function getBank(): string
    {
        return $this->bank;
    }

    public function setBank(string $bank): self
    {
        $this->bank = $bank;

        return $this;
    }

    public function getBankAddr(): string
    {
        return $this->bankAddr;
    }

    public function setBankAddr(string $bankAddr): self
    {
        $this->bankAddr = $bankAddr;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
