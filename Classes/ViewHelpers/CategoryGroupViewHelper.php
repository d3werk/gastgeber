<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use D3Werk\Gastgeber\Utility\CategoryIconResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class CategoryGroupViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('categories', 'iterable', 'Categories', true);
        $this->registerArgument('mode', 'string', 'all|ratings|features|topFeatures|types', false, 'all');
        $this->registerArgument('limit', 'int', 'Limit', false, 0);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        $categories = $arguments['categories'];
        $mode = (string)$arguments['mode'];
        $limit = (int)$arguments['limit'];
        $resolver = GeneralUtility::makeInstance(CategoryIconResolver::class);
        $items = [];
        foreach ($categories as $category) {
            if (!is_object($category) || !method_exists($category, 'getUid')) {
                continue;
            }
            $presentation = $resolver->getPresentation((int)$category->getUid(), method_exists($category, 'getTitle') ? (string)$category->getTitle() : '');
            $isRating = (bool)($presentation['isRating'] ?? false);
            $topFeature = (bool)($presentation['topFeature'] ?? false);
            if ($mode === 'ratings' && !$isRating) {
                continue;
            }
            if ($mode === 'features' && $isRating) {
                continue;
            }
            if ($mode === 'topFeatures' && (!$topFeature || $isRating)) {
                continue;
            }
            if ($mode === 'types' && $isRating) {
                continue;
            }
            $items[] = [
                'category' => $category,
                'presentation' => $presentation,
            ];
            if ($limit > 0 && count($items) >= $limit) {
                break;
            }
        }
        return $items;
    }
}
