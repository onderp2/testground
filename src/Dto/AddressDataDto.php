<?php

declare(strict_types=1);

namespace App\Dto;

class AddressDataDto
{
    public string $index;

    public string $region;

    public string $city;

    public string $street;

    public ?string $house = null;

    public ?string $houseUnit = null;

    public ?string $housingUnit = null;

    public ?string $officeUnit = null;


    public ?int $id;

    public int $countryIsoNr;

    private int $modelType;

    public function getCountryIsoNr(): int
    {
        return $this->countryIsoNr;
    }

    public function setCountryIsoNr(int $countryIsoNr): self
    {
        $this->countryIsoNr = $countryIsoNr;

        return $this;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function setIndex(string $index): self
    {
        $this->index = $index;

        return $this;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getHouse(): ?string
    {
        return $this->house;
    }

    public function setHouse(?string $house): self
    {
        $this->house = $house;

        return $this;
    }

    public function getHouseUnit(): ?string
    {
        return $this->houseUnit;
    }

    public function setHouseUnit(?string $houseUnit): self
    {
        $this->houseUnit = $houseUnit;

        return $this;
    }

    public function getHousingUnit(): ?string
    {
        return $this->housingUnit;
    }

    public function setHousingUnit(?string $housingUnit): self
    {
        $this->housingUnit = $housingUnit;

        return $this;
    }

    public function getOfficeUnit(): ?string
    {
        return $this->officeUnit;
    }

    public function setOfficeUnit(?string $officeUnit): self
    {
        $this->officeUnit = $officeUnit;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getModelType(): int
    {
        return $this->modelType;
    }

    public function getModeTypeText(): string
    {
        return match ($this->modelType) {
            Model_Address::TYPE_POSTAL => 'почтового',
            Model_Address::TYPE_LEGAL => 'почтового',
        };
    }

    public function setModelType(int $modelType): self
    {
        $this->modelType = $modelType;

        return $this;
    }
}
