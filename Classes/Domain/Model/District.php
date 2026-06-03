<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class District extends AbstractEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $description = '';
    protected string $latitude = '';
    protected string $longitude = '';

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function getLatitude(): string { return $this->latitude; }
    public function setLatitude(string $latitude): void { $this->latitude = $latitude; }
    public function getLongitude(): string { return $this->longitude; }
    public function setLongitude(string $longitude): void { $this->longitude = $longitude; }
}
