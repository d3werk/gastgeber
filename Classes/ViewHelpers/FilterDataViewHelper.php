<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use D3Werk\Gastgeber\Utility\FilterDataProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class FilterDataViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments(): void
    {
        $this->registerArgument('rootCategoryUids', 'string|array', 'Root category UIDs', false, '');
        $this->registerArgument('activeCategories', 'array', 'Active categories', false, []);
        $this->registerArgument('showRootCategories', 'bool', 'Show root categories', false, false);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        return GeneralUtility::makeInstance(FilterDataProvider::class)->getFilterGroups(
            $arguments['rootCategoryUids'] ?? '',
            $arguments['activeCategories'] ?? [],
            (bool)($arguments['showRootCategories'] ?? false)
        );
    }
}
