<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Splits categories into display groups for host cards and details.
 *
 * Returned groups:
 * - primaryCategory: first non-rating category, typically host type (Hotel, Pension, ...)
 * - starCategories: rating categories like "4 Sterne"
 * - featureCategories: all non-rating categories except the primary category
 * - listPreviewCategories: up to 4 top features for cards/list rows, falling back to normal features
 * - topFeatureCategories: categories explicitly marked as top feature in sys_category
 * - visibleFeatureCategories: first 6 features for detail preview
 * - hiddenFeatureCategories: remaining features for modal
 */
final class CategoryGroupViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /** @var array<int, bool> */
    private static array $topFeatureCache = [];

    public function initializeArguments(): void
    {
        $this->registerArgument('categories', 'mixed', 'Iterable category collection.', false, []);
        $this->registerArgument('fallback', 'mixed', 'Fallback category if categories are empty.', false, null);
        $this->registerArgument('limit', 'int', 'Number of visible detail features.', false, 6);
        $this->registerArgument('listLimit', 'int', 'Number of features shown in list/card preview.', false, 4);
    }

    /**
     * @return array<string,mixed>
     */
    public function render(): array
    {
        $primaryCategory = null;
        $starCategories = [];
        $featureCategories = [];
        $topFeatureCategories = [];

        foreach ($this->toArray($this->arguments['categories'] ?? []) as $category) {
            if ($this->isStarRatingCategory($category)) {
                $starCategories[] = $category;
                continue;
            }

            if ($primaryCategory === null) {
                $primaryCategory = $category;
                continue;
            }

            $featureCategories[] = $category;
            if ($this->isTopFeatureCategory($category)) {
                $topFeatureCategories[] = $category;
            }
        }

        $fallback = $this->arguments['fallback'] ?? null;
        if ($primaryCategory === null && $fallback !== null && !$this->isStarRatingCategory($fallback)) {
            $primaryCategory = $fallback;
        }
        if ($fallback !== null && $this->isStarRatingCategory($fallback) && $starCategories === []) {
            $starCategories[] = $fallback;
        }

        $limit = max(1, (int)($this->arguments['limit'] ?? 6));
        $listLimit = max(1, (int)($this->arguments['listLimit'] ?? 4));
        $visibleFeatureCategories = array_slice($featureCategories, 0, $limit);
        $hiddenFeatureCategories = array_slice($featureCategories, $limit);
        $listPreviewCategories = array_slice($topFeatureCategories !== [] ? $topFeatureCategories : $featureCategories, 0, $listLimit);

        return [
            'primaryCategory' => $primaryCategory,
            'starCategories' => $starCategories,
            'featureCategories' => $featureCategories,
            'topFeatureCategories' => $topFeatureCategories,
            'listPreviewCategories' => $listPreviewCategories,
            'visibleFeatureCategories' => $visibleFeatureCategories,
            'hiddenFeatureCategories' => $hiddenFeatureCategories,
            'featureCount' => count($featureCategories),
            'hiddenFeatureCount' => count($hiddenFeatureCategories),
            'hasFeatureModal' => count($hiddenFeatureCategories) > 0,
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

    private function isTopFeatureCategory(mixed $category): bool
    {
        $uid = $this->resolveUidFromCategory($category);
        if ($uid <= 0) {
            return false;
        }

        if (array_key_exists($uid, self::$topFeatureCache)) {
            return self::$topFeatureCache[$uid];
        }

        try {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_category');
            $value = $queryBuilder
                ->select('tx_gastgeber_top_feature')
                ->from('sys_category')
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchOne();
            self::$topFeatureCache[$uid] = (bool)$value;
        } catch (\Throwable) {
            self::$topFeatureCache[$uid] = false;
        }

        return self::$topFeatureCache[$uid];
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

    private function resolveUidFromCategory(mixed $category): int
    {
        if (is_array($category)) {
            return (int)($category['uid'] ?? 0);
        }

        if (is_object($category) && method_exists($category, 'getUid')) {
            return (int)$category->getUid();
        }

        if (is_object($category) && method_exists($category, 'getId')) {
            return (int)$category->getId();
        }

        return 0;
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
