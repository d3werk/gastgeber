<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class PriceItem extends AbstractEntity
{
    public const ROW_TYPE_PRICE = 'price';
    public const ROW_TYPE_HEADING = 'heading';
    public const ROW_TYPE_NOTE = 'note';

    protected ?Host $host = null;
    protected string $rowType = self::ROW_TYPE_PRICE;
    protected string $title = '';
    protected string $description = '';

    public function getHost(): ?Host
    {
        return $this->host;
    }

    public function setHost(?Host $host): void
    {
        $this->host = $host;
    }

    public function getRowType(): string
    {
        return $this->rowType;
    }

    public function setRowType(string $rowType): void
    {
        $allowed = [self::ROW_TYPE_PRICE, self::ROW_TYPE_HEADING, self::ROW_TYPE_NOTE];
        $this->rowType = in_array($rowType, $allowed, true) ? $rowType : self::ROW_TYPE_PRICE;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isHeading(): bool
    {
        return $this->rowType === self::ROW_TYPE_HEADING;
    }

    public function isNote(): bool
    {
        return $this->rowType === self::ROW_TYPE_NOTE;
    }
}
