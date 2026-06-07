<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

class Feature extends AbstractEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $description = '';
    protected ?FeatureGroup $group = null;
    protected ?FileReference $icon = null;
    protected string $iconClass = '';
    protected bool $filterable = true;
    protected bool $showInCard = true;
    protected bool $showInDetail = true;
    protected bool $topFeature = false;

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function getGroup(): ?FeatureGroup { return $this->group; }
    public function setGroup(?FeatureGroup $group): void { $this->group = $group; }
    public function getIcon(): ?FileReference { return $this->icon; }
    public function setIcon(?FileReference $icon): void { $this->icon = $icon; }
    public function getIconClass(): string { return $this->iconClass; }
    public function setIconClass(string $iconClass): void { $this->iconClass = $iconClass; }
    public function getFilterable(): bool { return $this->filterable; }
    public function isFilterable(): bool { return $this->filterable; }
    public function setFilterable(bool $filterable): void { $this->filterable = $filterable; }
    public function getShowInCard(): bool { return $this->showInCard; }
    public function isShowInCard(): bool { return $this->showInCard; }
    public function setShowInCard(bool $showInCard): void { $this->showInCard = $showInCard; }
    public function getShowInDetail(): bool { return $this->showInDetail; }
    public function isShowInDetail(): bool { return $this->showInDetail; }
    public function setShowInDetail(bool $showInDetail): void { $this->showInDetail = $showInDetail; }
    public function getTopFeature(): bool { return $this->topFeature; }
    public function isTopFeature(): bool { return $this->topFeature; }
    public function setTopFeature(bool $topFeature): void { $this->topFeature = $topFeature; }
}
