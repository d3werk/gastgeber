<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns display information for a category badge.
 * Especially detects rating categories like "4 Sterne" or "Keine Sterne".
 */
final class CategoryPresentationViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('category', 'mixed', 'Category object/array/string.', false, null);
        $this->registerArgument('title', 'string', 'Category title. Takes precedence over category title.', false, '');
    }

    /**
     * @return array{title:string,isStarRating:bool,stars:int,starItems:array<int,int>,badgeClass:string,ariaLabel:string}
     */
    public function render(): array
    {
        $title = trim((string)$this->arguments['title']);
        if ($title === '') {
            $title = $this->resolveTitleFromCategory($this->arguments['category'] ?? null);
        }

        $stars = $this->resolveStars($title);
        $isStarRating = $stars !== null;
        $starCount = $stars ?? 0;

        return [
            'title' => $title,
            'isStarRating' => $isStarRating,
            'stars' => $starCount,
            'starItems' => $starCount > 0 ? range(1, $starCount) : [],
            'badgeClass' => $isStarRating ? 'gastgeber-category-badge--stars gastgeber-category-badge--stars-' . $starCount : 'text-bg-light border',
            'ariaLabel' => $title,
        ];
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
