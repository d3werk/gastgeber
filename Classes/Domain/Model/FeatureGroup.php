<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

class FeatureGroup extends AbstractEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $description = '';
    protected ?FileReference $icon = null;
    protected string $iconClass = '';
    protected bool $collapsed = false;

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function getIcon(): ?FileReference { return $this->icon; }
    public function setIcon(?FileReference $icon): void { $this->icon = $icon; }
    public function getIconClass(): string { return $this->iconClass; }
    public function setIconClass(string $iconClass): void { $this->iconClass = $iconClass; }
    public function getCollapsed(): bool { return $this->collapsed; }
    public function isCollapsed(): bool { return $this->collapsed; }
    public function setCollapsed(bool $collapsed): void { $this->collapsed = $collapsed; }
}
