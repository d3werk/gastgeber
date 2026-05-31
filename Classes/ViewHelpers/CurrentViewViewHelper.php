<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the selected Gastgeber view mode from the current query string.
 */
final class CurrentViewViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('default', 'string', 'Default view mode: cards, list or map.', false, 'cards');
        $this->registerArgument('parameterName', 'string', 'GET parameter name.', false, 'tx_gastgeber_view');
    }

    public function render(): string
    {
        $allowed = ['cards', 'list', 'map'];
        $default = (string)$this->arguments['default'];
        if (!in_array($default, $allowed, true)) {
            $default = 'cards';
        }

        $parameterName = (string)$this->arguments['parameterName'];
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return $default;
        }

        $queryParams = $request->getQueryParams();
        $selectedView = $queryParams[$parameterName] ?? $default;
        if (is_array($selectedView)) {
            return $default;
        }

        $selectedView = strtolower((string)$selectedView);
        return in_array($selectedView, $allowed, true) ? $selectedView : $default;
    }
}
