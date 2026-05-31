<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use D3Werk\Gastgeber\Utility\CategoryIconResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns configured icon data for a sys_category record.
 */
final class CategoryIconViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('category', 'mixed', 'Category object/array with uid.', false, null);
        $this->registerArgument('uid', 'int', 'Category uid. Takes precedence over category.', false, 0);
    }

    /**
     * @return array{url:string, cssClass:string}
     */
    public function render(): array
    {
        $uid = (int)$this->arguments['uid'];
        if ($uid <= 0) {
            $uid = $this->resolveUidFromCategory($this->arguments['category'] ?? null);
        }

        if ($uid <= 0) {
            return ['url' => '', 'cssClass' => ''];
        }

        return GeneralUtility::makeInstance(CategoryIconResolver::class)->resolve($uid);
    }

    private function resolveUidFromCategory(mixed $category): int
    {
        if (is_array($category)) {
            return (int)($category['uid'] ?? 0);
        }

        if (is_object($category) && method_exists($category, 'getUid')) {
            return (int)$category->getUid();
        }

        return 0;
    }
}
