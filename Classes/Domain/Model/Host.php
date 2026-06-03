<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Model;

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Host extends AbstractEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected ?HostType $type = null;
    protected ?District $district = null;
    protected string $teaser = '';
    protected string $description = '';
    protected bool $featured = false;
    protected int $priority = 0;
    protected string $externalId = '';

    /** @var ObjectStorage<FileReference> */
    protected ObjectStorage $media;
    /** @var ObjectStorage<FileReference> */
    protected ObjectStorage $logo;
    /** @var ObjectStorage<FileReference> */
    protected ObjectStorage $documents;
    protected string $videoUrl = '';

    protected string $street = '';
    protected string $addressAddition = '';
    protected string $zip = '';
    protected string $city = '';
    protected string $country = 'Deutschland';
    protected string $latitude = '';
    protected string $longitude = '';
    protected bool $showOnMap = true;
    protected bool $geocodeOnSave = false;

    protected string $contactName = '';
    protected string $phone = '';
    protected string $mobile = '';
    protected string $email = '';
    protected string $website = '';
    protected string $bookingUrl = '';
    protected string $bookingText = '';
    protected string $requestEmail = '';

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

    /** @var ObjectStorage<Feature> */
    protected ObjectStorage $features;
    /** @var ObjectStorage<Certificate> */
    protected ObjectStorage $certificates;
    protected string $equipmentText = '';
    protected string $openingTimes = '';
    protected string $accessibilityText = '';
    protected string $sustainabilityText = '';

    protected string $seoTitle = '';
    protected string $metaDescription = '';
    protected string $ogTitle = '';
    protected string $ogDescription = '';
    protected bool $noindex = false;

    public function __construct()
    {
        $this->media = new ObjectStorage();
        $this->logo = new ObjectStorage();
        $this->documents = new ObjectStorage();
        $this->features = new ObjectStorage();
        $this->certificates = new ObjectStorage();
    }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function getType(): ?HostType { return $this->type; }
    public function setType(?HostType $type): void { $this->type = $type; }
    public function getDistrict(): ?District { return $this->district; }
    public function setDistrict(?District $district): void { $this->district = $district; }
    public function getTeaser(): string { return $this->teaser; }
    public function setTeaser(string $teaser): void { $this->teaser = $teaser; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function getFeatured(): bool { return $this->featured; }
    public function isFeatured(): bool { return $this->featured; }
    public function setFeatured(bool $featured): void { $this->featured = $featured; }
    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): void { $this->priority = $priority; }
    public function getExternalId(): string { return $this->externalId; }
    public function setExternalId(string $externalId): void { $this->externalId = $externalId; }

    /** @return ObjectStorage<FileReference> */ public function getMedia(): ObjectStorage { return $this->media; }
    /** @param ObjectStorage<FileReference> $media */ public function setMedia(ObjectStorage $media): void { $this->media = $media; }
    public function getFirstMedia(): ?FileReference { foreach ($this->media as $item) { return $item; } return null; }
    public function getMediaCount(): int { return count($this->media); }
    /** @return ObjectStorage<FileReference> */ public function getLogo(): ObjectStorage { return $this->logo; }
    /** @param ObjectStorage<FileReference> $logo */ public function setLogo(ObjectStorage $logo): void { $this->logo = $logo; }
    /** @return ObjectStorage<FileReference> */ public function getDocuments(): ObjectStorage { return $this->documents; }
    /** @param ObjectStorage<FileReference> $documents */ public function setDocuments(ObjectStorage $documents): void { $this->documents = $documents; }
    public function getVideoUrl(): string { return $this->videoUrl; }
    public function setVideoUrl(string $videoUrl): void { $this->videoUrl = $videoUrl; }

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
    public function getShowOnMap(): bool { return $this->showOnMap; }
    public function isShowOnMap(): bool { return $this->showOnMap; }
    public function setShowOnMap(bool $showOnMap): void { $this->showOnMap = $showOnMap; }
    public function getGeocodeOnSave(): bool { return $this->geocodeOnSave; }
    public function isGeocodeOnSave(): bool { return $this->geocodeOnSave; }
    public function setGeocodeOnSave(bool $geocodeOnSave): void { $this->geocodeOnSave = $geocodeOnSave; }

    public function getContactName(): string { return $this->contactName; }
    public function setContactName(string $contactName): void { $this->contactName = $contactName; }
    public function getPhone(): string { return $this->phone; }
    public function setPhone(string $phone): void { $this->phone = $phone; }
    public function getMobile(): string { return $this->mobile; }
    public function setMobile(string $mobile): void { $this->mobile = $mobile; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function getWebsite(): string { return $this->website; }
    public function setWebsite(string $website): void { $this->website = $website; }
    public function getBookingUrl(): string { return $this->bookingUrl; }
    public function setBookingUrl(string $bookingUrl): void { $this->bookingUrl = $bookingUrl; }
    public function getBookingText(): string { return $this->bookingText; }
    public function setBookingText(string $bookingText): void { $this->bookingText = $bookingText; }
    public function getRequestEmail(): string { return $this->requestEmail; }
    public function setRequestEmail(string $requestEmail): void { $this->requestEmail = $requestEmail; }

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

    /** @return ObjectStorage<Feature> */ public function getFeatures(): ObjectStorage { return $this->features; }
    /** @param ObjectStorage<Feature> $features */ public function setFeatures(ObjectStorage $features): void { $this->features = $features; }
    /** @return ObjectStorage<Certificate> */ public function getCertificates(): ObjectStorage { return $this->certificates; }
    /** @param ObjectStorage<Certificate> $certificates */ public function setCertificates(ObjectStorage $certificates): void { $this->certificates = $certificates; }
    public function getEquipmentText(): string { return $this->equipmentText; }
    public function setEquipmentText(string $equipmentText): void { $this->equipmentText = $equipmentText; }
    public function getOpeningTimes(): string { return $this->openingTimes; }
    public function setOpeningTimes(string $openingTimes): void { $this->openingTimes = $openingTimes; }
    public function getAccessibilityText(): string { return $this->accessibilityText; }
    public function setAccessibilityText(string $accessibilityText): void { $this->accessibilityText = $accessibilityText; }
    public function getSustainabilityText(): string { return $this->sustainabilityText; }
    public function setSustainabilityText(string $sustainabilityText): void { $this->sustainabilityText = $sustainabilityText; }

    public function getSeoTitle(): string { return $this->seoTitle; }
    public function setSeoTitle(string $seoTitle): void { $this->seoTitle = $seoTitle; }
    public function getMetaDescription(): string { return $this->metaDescription; }
    public function setMetaDescription(string $metaDescription): void { $this->metaDescription = $metaDescription; }
    public function getOgTitle(): string { return $this->ogTitle; }
    public function setOgTitle(string $ogTitle): void { $this->ogTitle = $ogTitle; }
    public function getOgDescription(): string { return $this->ogDescription; }
    public function setOgDescription(string $ogDescription): void { $this->ogDescription = $ogDescription; }
    public function getNoindex(): bool { return $this->noindex; }
    public function isNoindex(): bool { return $this->noindex; }
    public function setNoindex(bool $noindex): void { $this->noindex = $noindex; }

    public function getAddressLine(): string
    {
        return trim(implode(', ', array_filter([$this->street, trim($this->zip . ' ' . $this->city)])));
    }
    public function hasCoordinates(): bool
    {
        return $this->latitude !== '' && $this->longitude !== '' && $this->latitude !== '0' && $this->longitude !== '0' && $this->latitude !== '0.0000000' && $this->longitude !== '0.0000000';
    }
    public function getHasCoordinates(): bool { return $this->hasCoordinates(); }

    /** @return array<int,Feature> */
    public function getTopFeatures(): array
    {
        $features = [];
        foreach ($this->features as $feature) {
            if ($feature->isTopFeature()) {
                $features[] = $feature;
            }
        }
        return $features;
    }

    /** @return array<int,Feature> */
    public function getCardFeatures(): array
    {
        $features = [];
        foreach ($this->features as $feature) {
            if ($feature->isShowInCard()) {
                $features[] = $feature;
            }
            if (count($features) >= 5) {
                break;
            }
        }
        return $features;
    }

    /** @return array<int,Feature> */
    public function getDetailPreviewFeatures(): array
    {
        $features = [];
        foreach ($this->features as $feature) {
            if ($feature->isShowInDetail()) {
                $features[] = $feature;
            }
            if (count($features) >= 6) {
                break;
            }
        }
        return $features;
    }

    public function getDetailFeatureCount(): int
    {
        $count = 0;
        foreach ($this->features as $feature) {
            if ($feature->isShowInDetail()) {
                $count++;
            }
        }
        return $count;
    }

    /** @return array<int,Feature> */ public function getDetailFeatures(): array
    {
        $features = [];
        foreach ($this->features as $feature) {
            if ($feature->isShowInDetail()) {
                $features[] = $feature;
            }
        }
        return $features;
    }

    /** @return array<int,array{title:string,iconClass:string,features:array<int,Feature>}> */
    public function getGroupedDetailFeatures(): array
    {
        $groups = [];
        foreach ($this->getDetailFeatures() as $feature) {
            $group = $feature->getGroup();
            $groupUid = $group ? (string)$group->getUid() : '0';
            if (!isset($groups[$groupUid])) {
                $groups[$groupUid] = [
                    'title' => $group ? $group->getTitle() : 'Weitere Merkmale',
                    'iconClass' => $group ? $group->getIconClass() : '',
                    'features' => [],
                ];
            }
            $groups[$groupUid]['features'][] = $feature;
        }
        return array_values($groups);
    }

    public function getPrimaryCertificate(): ?Certificate
    {
        foreach ($this->certificates as $certificate) {
            if ($certificate->getIsRating()) {
                return $certificate;
            }
        }
        foreach ($this->certificates as $certificate) {
            return $certificate;
        }
        return null;
    }

    /** @return array<int,Certificate> */
    public function getRatingCertificates(): array
    {
        $certificates = [];
        foreach ($this->certificates as $certificate) {
            if ($certificate->getIsRating()) {
                $certificates[] = $certificate;
            }
        }
        return $certificates;
    }

    public function getShortAddress(): string
    {
        return trim(implode(' · ', array_filter([$this->district?->getTitle(), trim($this->zip . ' ' . $this->city)])));
    }
}
