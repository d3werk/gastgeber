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
        // `mixed` ist absichtlich gewählt: Extbase liefert je nach Kontext QueryResult,
        // ObjectStorage oder Arrays. Eine zu enge Fluid-Typprüfung kann sonst bereits
        // beim Rendern der Listenansicht einen Fehler auslösen, obwohl die Karte nur
        // im Modal liegt.
        $this->registerArgument('hosts', 'mixed', 'Hosts', false, []);
        $this->registerArgument('host', 'mixed', 'Single host', false, null);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $hosts = [];

        if (isset($arguments['host']) && is_object($arguments['host'])) {
            $hosts[] = $arguments['host'];
        }

        $hostsArgument = $arguments['hosts'] ?? [];
        if (is_iterable($hostsArgument)) {
            foreach ($hostsArgument as $host) {
                if (is_object($host)) {
                    $hosts[] = $host;
                }
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

            $lat = method_exists($host, 'getLatitude') ? (string)$host->getLatitude() : '';
            $lng = method_exists($host, 'getLongitude') ? (string)$host->getLongitude() : '';
            if (!is_numeric($lat) || !is_numeric($lng)) {
                continue;
            }

            $markers[] = [
                'title' => self::safeString(method_exists($host, 'getTitle') ? $host->getTitle() : ''),
                'lat' => (float)$lat,
                'lng' => (float)$lng,
                'address' => self::safeString(method_exists($host, 'getAddressLine') ? $host->getAddressLine() : ''),
            ];
        }

        $json = json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        return is_string($json) ? $json : '[]';
    }

    private static function safeString(mixed $value): string
    {
        $value = (string)$value;
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'UTF-8//IGNORE', $value);
            if (is_string($converted)) {
                return $converted;
            }
        }

        return $value;
    }
}
