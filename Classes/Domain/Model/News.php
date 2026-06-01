<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Model;

class News extends \GeorgRinger\News\Domain\Model\News
{
    protected string $txGastgeberStreet = '';
    protected string $txGastgeberAddressAddition = '';
    protected string $txGastgeberZip = '';
    protected string $txGastgeberCity = '';
    protected string $txGastgeberCountry = 'Deutschland';
    protected string $txGastgeberPhone = '';
    protected string $txGastgeberEmail = '';
    protected string $txGastgeberWebsite = '';
    protected string $txGastgeberBookingUrl = '';
    protected string $txGastgeberBookingNote = '';
    protected float $txGastgeberPriceFrom = 0.0;
    protected string $txGastgeberPriceNote = '';
    protected int $txGastgeberCapacityPeople = 0;
    protected int $txGastgeberRooms = 0;
    protected int $txGastgeberBeds = 0;
    protected float $txGastgeberLatitude = 0.0;
    protected float $txGastgeberLongitude = 0.0;
    protected bool $txGastgeberShowOnMap = true;
    protected string $txGastgeberOpeningTimes = '';
    protected string $txGastgeberEquipment = '';
    protected string $txGastgeberCertifications = '';
    protected string $txGastgeberSeoTitle = '';
    protected string $txGastgeberSeoDescription = '';
    protected string $txGastgeberFocusKeyword = '';
    protected string $txGastgeberOgTitle = '';
    protected string $txGastgeberOgDescription = '';
    protected bool $txGastgeberSeoNoindex = false;

    public function getTxGastgeberStreet(): string
    {
        return $this->txGastgeberStreet;
    }

    public function setTxGastgeberStreet(string $txGastgeberStreet): void
    {
        $this->txGastgeberStreet = $txGastgeberStreet;
    }

    public function getTxGastgeberAddressAddition(): string
    {
        return $this->txGastgeberAddressAddition;
    }

    public function setTxGastgeberAddressAddition(string $txGastgeberAddressAddition): void
    {
        $this->txGastgeberAddressAddition = $txGastgeberAddressAddition;
    }

    public function getTxGastgeberZip(): string
    {
        return $this->txGastgeberZip;
    }

    public function setTxGastgeberZip(string $txGastgeberZip): void
    {
        $this->txGastgeberZip = $txGastgeberZip;
    }

    public function getTxGastgeberCity(): string
    {
        return $this->txGastgeberCity;
    }

    public function setTxGastgeberCity(string $txGastgeberCity): void
    {
        $this->txGastgeberCity = $txGastgeberCity;
    }

    public function getTxGastgeberCountry(): string
    {
        return $this->txGastgeberCountry;
    }

    public function setTxGastgeberCountry(string $txGastgeberCountry): void
    {
        $this->txGastgeberCountry = $txGastgeberCountry;
    }

    public function getTxGastgeberPhone(): string
    {
        return $this->txGastgeberPhone;
    }

    public function setTxGastgeberPhone(string $txGastgeberPhone): void
    {
        $this->txGastgeberPhone = $txGastgeberPhone;
    }

    public function getTxGastgeberEmail(): string
    {
        return $this->txGastgeberEmail;
    }

    public function setTxGastgeberEmail(string $txGastgeberEmail): void
    {
        $this->txGastgeberEmail = $txGastgeberEmail;
    }

    public function getTxGastgeberWebsite(): string
    {
        return $this->txGastgeberWebsite;
    }

    public function setTxGastgeberWebsite(string $txGastgeberWebsite): void
    {
        $this->txGastgeberWebsite = $txGastgeberWebsite;
    }

    public function getTxGastgeberBookingUrl(): string
    {
        return $this->txGastgeberBookingUrl;
    }

    public function setTxGastgeberBookingUrl(string $txGastgeberBookingUrl): void
    {
        $this->txGastgeberBookingUrl = $txGastgeberBookingUrl;
    }

    public function getTxGastgeberBookingNote(): string
    {
        return $this->txGastgeberBookingNote;
    }

    public function setTxGastgeberBookingNote(string $txGastgeberBookingNote): void
    {
        $this->txGastgeberBookingNote = $txGastgeberBookingNote;
    }

    public function getTxGastgeberPriceFrom(): float
    {
        return $this->txGastgeberPriceFrom;
    }

    public function setTxGastgeberPriceFrom(float $txGastgeberPriceFrom): void
    {
        $this->txGastgeberPriceFrom = $txGastgeberPriceFrom;
    }

    public function getTxGastgeberPriceNote(): string
    {
        return $this->txGastgeberPriceNote;
    }

    public function setTxGastgeberPriceNote(string $txGastgeberPriceNote): void
    {
        $this->txGastgeberPriceNote = $txGastgeberPriceNote;
    }

    public function getTxGastgeberCapacityPeople(): int
    {
        return $this->txGastgeberCapacityPeople;
    }

    public function setTxGastgeberCapacityPeople(int $txGastgeberCapacityPeople): void
    {
        $this->txGastgeberCapacityPeople = $txGastgeberCapacityPeople;
    }

    public function getTxGastgeberRooms(): int
    {
        return $this->txGastgeberRooms;
    }

    public function setTxGastgeberRooms(int $txGastgeberRooms): void
    {
        $this->txGastgeberRooms = $txGastgeberRooms;
    }

    public function getTxGastgeberBeds(): int
    {
        return $this->txGastgeberBeds;
    }

    public function setTxGastgeberBeds(int $txGastgeberBeds): void
    {
        $this->txGastgeberBeds = $txGastgeberBeds;
    }

    public function getTxGastgeberLatitude(): float
    {
        return $this->txGastgeberLatitude;
    }

    public function setTxGastgeberLatitude(float $txGastgeberLatitude): void
    {
        $this->txGastgeberLatitude = $txGastgeberLatitude;
    }

    public function getTxGastgeberLongitude(): float
    {
        return $this->txGastgeberLongitude;
    }

    public function setTxGastgeberLongitude(float $txGastgeberLongitude): void
    {
        $this->txGastgeberLongitude = $txGastgeberLongitude;
    }

    public function getTxGastgeberShowOnMap(): bool
    {
        return $this->txGastgeberShowOnMap;
    }

    public function setTxGastgeberShowOnMap(bool $txGastgeberShowOnMap): void
    {
        $this->txGastgeberShowOnMap = $txGastgeberShowOnMap;
    }

    public function getTxGastgeberOpeningTimes(): string
    {
        return $this->txGastgeberOpeningTimes;
    }

    public function setTxGastgeberOpeningTimes(string $txGastgeberOpeningTimes): void
    {
        $this->txGastgeberOpeningTimes = $txGastgeberOpeningTimes;
    }

    public function getTxGastgeberEquipment(): string
    {
        return $this->txGastgeberEquipment;
    }

    public function setTxGastgeberEquipment(string $txGastgeberEquipment): void
    {
        $this->txGastgeberEquipment = $txGastgeberEquipment;
    }

    public function getTxGastgeberCertifications(): string
    {
        return $this->txGastgeberCertifications;
    }

    public function setTxGastgeberCertifications(string $txGastgeberCertifications): void
    {
        $this->txGastgeberCertifications = $txGastgeberCertifications;
    }

    public function getTxGastgeberSeoTitle(): string
    {
        return $this->txGastgeberSeoTitle;
    }

    public function setTxGastgeberSeoTitle(string $txGastgeberSeoTitle): void
    {
        $this->txGastgeberSeoTitle = $txGastgeberSeoTitle;
    }

    public function getTxGastgeberSeoDescription(): string
    {
        return $this->txGastgeberSeoDescription;
    }

    public function setTxGastgeberSeoDescription(string $txGastgeberSeoDescription): void
    {
        $this->txGastgeberSeoDescription = $txGastgeberSeoDescription;
    }

    public function getTxGastgeberFocusKeyword(): string
    {
        return $this->txGastgeberFocusKeyword;
    }

    public function setTxGastgeberFocusKeyword(string $txGastgeberFocusKeyword): void
    {
        $this->txGastgeberFocusKeyword = $txGastgeberFocusKeyword;
    }

    public function getTxGastgeberOgTitle(): string
    {
        return $this->txGastgeberOgTitle;
    }

    public function setTxGastgeberOgTitle(string $txGastgeberOgTitle): void
    {
        $this->txGastgeberOgTitle = $txGastgeberOgTitle;
    }

    public function getTxGastgeberOgDescription(): string
    {
        return $this->txGastgeberOgDescription;
    }

    public function setTxGastgeberOgDescription(string $txGastgeberOgDescription): void
    {
        $this->txGastgeberOgDescription = $txGastgeberOgDescription;
    }

    public function getTxGastgeberSeoNoindex(): bool
    {
        return $this->txGastgeberSeoNoindex;
    }

    public function setTxGastgeberSeoNoindex(bool $txGastgeberSeoNoindex): void
    {
        $this->txGastgeberSeoNoindex = $txGastgeberSeoNoindex;
    }

}
