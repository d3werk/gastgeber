<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

class Certificate extends AbstractEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $description = '';
    protected string $issuer = '';
    protected ?FileReference $icon = null;
    protected string $iconClass = '';
    protected string $url = '';
    protected float $ratingValue = 0.0;

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function getIssuer(): string { return $this->issuer; }
    public function setIssuer(string $issuer): void { $this->issuer = $issuer; }
    public function getIcon(): ?FileReference { return $this->icon; }
    public function setIcon(?FileReference $icon): void { $this->icon = $icon; }
    public function getIconClass(): string { return $this->iconClass; }
    public function setIconClass(string $iconClass): void { $this->iconClass = $iconClass; }
    public function getUrl(): string { return $this->url; }
    public function setUrl(string $url): void { $this->url = $url; }
    public function getRatingValue(): float { return $this->ratingValue; }
    public function setRatingValue(float $ratingValue): void { $this->ratingValue = $ratingValue; }

    public function getStarCount(): int
    {
        $stars = (int)round($this->ratingValue);
        if ($stars <= 0 && preg_match('/([1-5])/', $this->title, $matches) === 1) {
            $stars = (int)$matches[1];
        }
        return max(0, min(5, $stars));
    }

    public function getStarLabel(): string
    {
        $count = $this->getStarCount();
        return $count > 0 ? str_repeat('★', $count) : $this->title;
    }

    public function getIsRating(): bool
    {
        return $this->getStarCount() > 0 || stripos($this->title, 'stern') !== false || stripos($this->issuer, 'dtv') !== false;
    }
}

