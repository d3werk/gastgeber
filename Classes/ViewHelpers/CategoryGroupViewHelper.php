<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Splits categories into a primary non-rating category and rating categories.
 */
final class CategoryGroupViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('categories', 'mixed', 'Iterable category collection.', false, []);
        $this->registerArgument('fallback', 'mixed', 'Fallback category if categories are empty.', false, null);
    }

    /**
     * @return array{primaryCategory:mixed,starCategories:array<int,mixed>}
     */
    public function render(): array
    {
        $primaryCategory = null;
        $starCategories = [];

        foreach ($this->toArray($this->arguments['categories'] ?? []) as $category) {
            if ($this->isStarRatingCategory($category)) {
                $starCategories[] = $category;
                continue;
            }

            if ($primaryCategory === null) {
                $primaryCategory = $category;
            }
        }

        $fallback = $this->arguments['fallback'] ?? null;
        if ($primaryCategory === null && $fallback !== null && !$this->isStarRatingCategory($fallback)) {
            $primaryCategory = $fallback;
        }
        if ($fallback !== null && $this->isStarRatingCategory($fallback) && $starCategories === []) {
            $starCategories[] = $fallback;
        }

        return [
            'primaryCategory' => $primaryCategory,
            'starCategories' => $starCategories,
        ];
    }

    /**
     * @return array<int,mixed>
     */
    private function toArray(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof \Traversable) {
            return iterator_to_array($value);
        }

        return [$value];
    }

    private function isStarRatingCategory(mixed $category): bool
    {
        $title = $this->resolveTitleFromCategory($category);
        return $this->resolveStars($title) !== null;
    }

    private function resolveTitleFromCategory(mixed $category): string
    {
        if (is_string($category)) {
            return trim($category);
        }

        if (is_array($category)) {
            return trim((string)($category['title'] ?? ''));
        }

        if (is_object($category) && method_exists($category, 'getTitle')) {
            return trim((string)$category->getTitle());
        }

        return '';
    }

    private function resolveStars(string $title): ?int
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $title) ?: $title);
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^keine\s+sterne?$/iu', $normalized) === 1) {
            return 0;
        }

        if (preg_match('/^([1-5])\s*(?:stern|sterne)(?:\s+superior)?$/iu', $normalized, $matches) === 1) {
            return (int)$matches[1];
        }

        if (preg_match('/^([1-5])\s*[\*★☆]+$/u', $normalized, $matches) === 1) {
            return (int)$matches[1];
        }

        return null;
    }
}
