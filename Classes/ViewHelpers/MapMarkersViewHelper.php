<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class MapMarkersViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('hosts', 'iterable', 'Hosts', false, []);
        $this->registerArgument('host', 'object', 'Single host', false, null);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $hosts = [];
        if (isset($arguments['host']) && is_object($arguments['host'])) {
            $hosts[] = $arguments['host'];
        }
        foreach (($arguments['hosts'] ?? []) as $host) {
            if (is_object($host)) {
                $hosts[] = $host;
            }
        }
        $markers = [];
        foreach ($hosts as $host) {
            if (!method_exists($host, 'hasCoordinates') || !$host->hasCoordinates()) {
                continue;
            }
            if (method_exists($host, 'isShowOnMap') && !$host->isShowOnMap()) {
                continue;
            }
            $markers[] = [
                'title' => method_exists($host, 'getTitle') ? $host->getTitle() : '',
                'lat' => method_exists($host, 'getLatitude') ? $host->getLatitude() : '',
                'lng' => method_exists($host, 'getLongitude') ? $host->getLongitude() : '',
                'address' => method_exists($host, 'getAddressLine') ? $host->getAddressLine() : '',
            ];
        }
        return json_encode($markers, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
