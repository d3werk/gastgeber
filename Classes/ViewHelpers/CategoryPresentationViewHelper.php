<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use D3Werk\Gastgeber\Utility\CategoryIconResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class CategoryPresentationViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('category', 'object|array|int', 'Category object, array or uid', true);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        $category = $arguments['category'];
        $uid = 0;
        $title = '';
        if (is_object($category) && method_exists($category, 'getUid')) {
            $uid = (int)$category->getUid();
            $title = method_exists($category, 'getTitle') ? (string)$category->getTitle() : '';
        } elseif (is_array($category)) {
            $uid = (int)($category['uid'] ?? 0);
            $title = (string)($category['title'] ?? '');
        } else {
            $uid = (int)$category;
        }
        if ($uid <= 0) {
            return [];
        }
        return GeneralUtility::makeInstance(CategoryIconResolver::class)->getPresentation($uid, $title);
    }
}
