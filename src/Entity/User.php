<?php

declare(strict_types=1);

namespace App\Entity;

class User
{
    private int $id;

    private int $clientProfileId;

    private int $userType;

    private string $cmpFullName = '';

    private ?string $email;

    private array $userRoles = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getClientProfileId(): int
    {
        return $this->clientProfileId;
    }

    public function setClientProfileId(int $clientProfileId): self
    {
        $this->clientProfileId = $clientProfileId;

        return $this;
    }

    public function getUserType(): int
    {
        return $this->userType;
    }

    public function setUserType(int $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getCmpFullName(): string
    {
        return $this->cmpFullName;
    }

    public function setCmpFullName(string $cmpFullName): self
    {
        $this->cmpFullName = $cmpFullName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserRoles(): array
    {
        return $this->userRoles;
    }

    public function setUserRoles(array $userRoles): self
    {
        $this->userRoles = $userRoles;

        return $this;
    }
}
