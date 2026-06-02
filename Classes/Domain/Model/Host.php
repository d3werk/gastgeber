<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Host extends AbstractEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $teaser = '';
    protected string $description = '';

    /** @var ObjectStorage<FileReference> */
    protected ObjectStorage $media;

    /** @var ObjectStorage<Category> */
    protected ObjectStorage $categories;

    protected string $street = '';
    protected string $addressAddition = '';
    protected string $zip = '';
    protected string $city = '';
    protected string $country = 'Deutschland';
    protected string $latitude = '';
    protected string $longitude = '';
    protected bool $showOnMap = false;
    protected bool $geocodeOnSave = false;
    protected string $contactName = '';
    protected string $phone = '';
    protected string $email = '';
    protected string $website = '';
    protected string $bookingUrl = '';
    protected string $bookingText = '';
    protected float $priceFrom = 0.0;
    protected string $priceInfo = '';
    protected int $persons = 0;
    protected int $bedrooms = 0;
    protected int $beds = 0;
    protected int $bathrooms = 0;
    protected int $sizeSqm = 0;
    protected int $units = 0;
    protected string $seasonInfo = '';
    protected string $capacityNote = '';
    protected string $equipmentText = '';
    protected string $openingTimes = '';
    protected string $classificationText = '';
    protected string $seoTitle = '';
    protected string $metaDescription = '';
    protected string $ogTitle = '';
    protected string $ogDescription = '';
    protected bool $noindex = false;
    protected bool $featured = false;

    public function __construct()
    {
        $this->media = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function getTeaser(): string { return $this->teaser; }
    public function setTeaser(string $teaser): void { $this->teaser = $teaser; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    /** @return ObjectStorage<FileReference> */
    public function getMedia(): ObjectStorage { return $this->media; }
    /** @param ObjectStorage<FileReference> $media */
    public function setMedia(ObjectStorage $media): void { $this->media = $media; }
    public function getFirstMedia(): ?FileReference
    {
        foreach ($this->media as $media) {
            return $media;
        }
        return null;
    }

    /** @return ObjectStorage<Category> */
    public function getCategories(): ObjectStorage { return $this->categories; }
    /** @param ObjectStorage<Category> $categories */
    public function setCategories(ObjectStorage $categories): void { $this->categories = $categories; }
    public function getCategoryUids(): array
    {
        $uids = [];
        foreach ($this->categories as $category) {
            $uids[] = (int)$category->getUid();
        }
        return $uids;
    }

    public function getStreet(): string { return $this->street; }
    public function setStreet(string $street): void { $this->street = $street; }
    public function getAddressAddition(): string { return $this->addressAddition; }
    public function setAddressAddition(string $addressAddition): void { $this->addressAddition = $addressAddition; }
    public function getZip(): string { return $this->zip; }
    public function setZip(string $zip): void { $this->zip = $zip; }
    public function getCity(): string { return $this->city; }
    public function setCity(string $city): void { $this->city = $city; }
    public function getCountry(): string { return $this->country; }
    public function setCountry(string $country): void { $this->country = $country; }
    public function getLatitude(): string { return $this->latitude; }
    public function setLatitude(string $latitude): void { $this->latitude = $latitude; }
    public function getLongitude(): string { return $this->longitude; }
    public function setLongitude(string $longitude): void { $this->longitude = $longitude; }
    public function isShowOnMap(): bool { return $this->showOnMap; }
    public function getShowOnMap(): bool { return $this->showOnMap; }
    public function setShowOnMap(bool $showOnMap): void { $this->showOnMap = $showOnMap; }
    public function isGeocodeOnSave(): bool { return $this->geocodeOnSave; }
    public function getGeocodeOnSave(): bool { return $this->geocodeOnSave; }
    public function setGeocodeOnSave(bool $geocodeOnSave): void { $this->geocodeOnSave = $geocodeOnSave; }

    public function getContactName(): string { return $this->contactName; }
    public function setContactName(string $contactName): void { $this->contactName = $contactName; }
    public function getPhone(): string { return $this->phone; }
    public function setPhone(string $phone): void { $this->phone = $phone; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function getWebsite(): string { return $this->website; }
    public function setWebsite(string $website): void { $this->website = $website; }
    public function getBookingUrl(): string { return $this->bookingUrl; }
    public function setBookingUrl(string $bookingUrl): void { $this->bookingUrl = $bookingUrl; }
    public function getBookingText(): string { return $this->bookingText; }
    public function setBookingText(string $bookingText): void { $this->bookingText = $bookingText; }

    public function getPriceFrom(): float { return $this->priceFrom; }
    public function setPriceFrom(float $priceFrom): void { $this->priceFrom = $priceFrom; }
    public function getPriceInfo(): string { return $this->priceInfo; }
    public function setPriceInfo(string $priceInfo): void { $this->priceInfo = $priceInfo; }
    public function getPersons(): int { return $this->persons; }
    public function setPersons(int $persons): void { $this->persons = $persons; }
    public function getBedrooms(): int { return $this->bedrooms; }
    public function setBedrooms(int $bedrooms): void { $this->bedrooms = $bedrooms; }
    public function getBeds(): int { return $this->beds; }
    public function setBeds(int $beds): void { $this->beds = $beds; }
    public function getBathrooms(): int { return $this->bathrooms; }
    public function setBathrooms(int $bathrooms): void { $this->bathrooms = $bathrooms; }
    public function getSizeSqm(): int { return $this->sizeSqm; }
    public function setSizeSqm(int $sizeSqm): void { $this->sizeSqm = $sizeSqm; }
    public function getUnits(): int { return $this->units; }
    public function setUnits(int $units): void { $this->units = $units; }
    public function getSeasonInfo(): string { return $this->seasonInfo; }
    public function setSeasonInfo(string $seasonInfo): void { $this->seasonInfo = $seasonInfo; }
    public function getCapacityNote(): string { return $this->capacityNote; }
    public function setCapacityNote(string $capacityNote): void { $this->capacityNote = $capacityNote; }

    public function getEquipmentText(): string { return $this->equipmentText; }
    public function setEquipmentText(string $equipmentText): void { $this->equipmentText = $equipmentText; }
    public function getOpeningTimes(): string { return $this->openingTimes; }
    public function setOpeningTimes(string $openingTimes): void { $this->openingTimes = $openingTimes; }
    public function getClassificationText(): string { return $this->classificationText; }
    public function setClassificationText(string $classificationText): void { $this->classificationText = $classificationText; }

    public function getSeoTitle(): string { return $this->seoTitle; }
    public function setSeoTitle(string $seoTitle): void { $this->seoTitle = $seoTitle; }
    public function getMetaDescription(): string { return $this->metaDescription; }
    public function setMetaDescription(string $metaDescription): void { $this->metaDescription = $metaDescription; }
    public function getOgTitle(): string { return $this->ogTitle; }
    public function setOgTitle(string $ogTitle): void { $this->ogTitle = $ogTitle; }
    public function getOgDescription(): string { return $this->ogDescription; }
    public function setOgDescription(string $ogDescription): void { $this->ogDescription = $ogDescription; }
    public function isNoindex(): bool { return $this->noindex; }
    public function getNoindex(): bool { return $this->noindex; }
    public function setNoindex(bool $noindex): void { $this->noindex = $noindex; }
    public function isFeatured(): bool { return $this->featured; }
    public function getFeatured(): bool { return $this->featured; }
    public function setFeatured(bool $featured): void { $this->featured = $featured; }

    public function getAddressLine(): string
    {
        return trim(implode(' ', array_filter([$this->street, trim($this->zip . ' ' . $this->city)])));
    }

    public function getHasCoordinates(): bool
    {
        return $this->hasCoordinates();
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== '' && $this->longitude !== '' && $this->latitude !== '0.0000000' && $this->longitude !== '0.0000000';
    }
}
