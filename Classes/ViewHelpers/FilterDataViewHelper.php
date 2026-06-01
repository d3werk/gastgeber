<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use D3Werk\Gastgeber\Utility\FilterDataProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Provides the backend-managed Gastgeber filter category tree for Fluid templates.
 */
final class FilterDataViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('rootCategoryUids', 'string', 'Comma separated sys_category root UIDs.', false, '');
        $this->registerArgument('categoryConjunction', 'string', 'EXT:news category conjunction: or/and.', false, 'or');
        $this->registerArgument('targetPid', 'string', 'Target page UID.', false, '');
        $this->registerArgument('showRootCategories', 'bool', 'Render selected roots as selectable filters.', false, false);
        $this->registerArgument('preferGastgeberRoot', 'bool', 'Use Gastgeber root as fallback.', false, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function render(): array
    {
        $provider = GeneralUtility::makeInstance(FilterDataProvider::class);

        return $provider->build(
            (string)$this->arguments['rootCategoryUids'],
            (string)$this->arguments['categoryConjunction'],
            (string)$this->arguments['targetPid'],
            (bool)$this->arguments['showRootCategories'],
            (bool)$this->arguments['preferGastgeberRoot']
        );
    }
}
