<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Renders uploaded category/feature icons without TYPO3 image processing.
 *
 * The icon fields are editor fields and may contain FAL file references, an
 * ObjectStorage with one file reference, a public URL/path or a CSS icon class.
 * Using f:image for these small SVG/PNG pictograms is fragile because TYPO3's
 * image processing can fail on SVGs or unexpected relation shapes. This
 * ViewHelper keeps the detail view stable and falls back gracefully.
 */
final class IconViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('item', 'mixed', 'Object with icon/iconClass properties', true);
        $this->registerArgument('imgClass', 'string', 'CSS class for uploaded/path icons', false, 'gastgeber-icon-img');
        $this->registerArgument('iconClass', 'string', 'Base CSS class for CSS icons', false, 'gastgeber-icon');
        $this->registerArgument('fallbackClass', 'string', 'Fallback CSS class', false, 'gastgeber-icon gastgeber-icon--fallback');
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $item = $arguments['item'] ?? null;
        $imgClass = self::sanitizeClassList((string)($arguments['imgClass'] ?? 'gastgeber-icon-img'));
        $iconBaseClass = self::sanitizeClassList((string)($arguments['iconClass'] ?? 'gastgeber-icon'));
        $fallbackClass = self::sanitizeClassList((string)($arguments['fallbackClass'] ?? 'gastgeber-icon gastgeber-icon--fallback'));

        $icon = self::readProperty($item, 'icon');
        $iconClassOrPath = trim((string)self::readProperty($item, 'iconClass'));

        $url = self::resolveIconUrl($icon);
        if ($url === '' && self::looksLikeImageReference($iconClassOrPath)) {
            $url = self::normalizeIconPath($iconClassOrPath);
            $iconClassOrPath = '';
        }

        if ($url !== '') {
            return '<img src="' . self::escape($url) . '" class="' . self::escape($imgClass) . '" alt="" loading="lazy" aria-hidden="true" />';
        }

        if ($iconClassOrPath !== '') {
            $classes = trim($iconBaseClass . ' ' . self::sanitizeClassList($iconClassOrPath));
            if ($classes !== $iconBaseClass) {
                return '<span class="' . self::escape($classes) . '" aria-hidden="true"></span>';
            }
        }

        return '<span class="' . self::escape($fallbackClass) . '" aria-hidden="true"></span>';
    }

    private static function readProperty(mixed $item, string $property): mixed
    {
        if ($item === null) {
            return null;
        }

        if (is_array($item)) {
            return $item[$property] ?? null;
        }

        if (is_object($item)) {
            $method = 'get' . ucfirst($property);
            if (method_exists($item, $method)) {
                try {
                    return $item->{$method}();
                } catch (\Throwable) {
                    return null;
                }
            }
        }

        return null;
    }

    private static function resolveIconUrl(mixed $icon): string
    {
        if ($icon === null || $icon === '' || $icon === 0 || $icon === '0') {
            return '';
        }

        if (is_string($icon)) {
            return self::normalizeIconPath($icon);
        }

        if (is_array($icon)) {
            foreach (['originalResource', 'publicUrl', 'url', 'src'] as $key) {
                if (isset($icon[$key])) {
                    $resolved = self::resolveIconUrl($icon[$key]);
                    if ($resolved !== '') {
                        return $resolved;
                    }
                }
            }
            foreach ($icon as $value) {
                $resolved = self::resolveIconUrl($value);
                if ($resolved !== '') {
                    return $resolved;
                }
            }
            return '';
        }

        if (is_iterable($icon)) {
            foreach ($icon as $value) {
                $resolved = self::resolveIconUrl($value);
                if ($resolved !== '') {
                    return $resolved;
                }
            }
            return '';
        }

        if (is_object($icon)) {
            foreach (['getOriginalResource', 'getOriginalFile', 'getFile'] as $method) {
                if (method_exists($icon, $method)) {
                    try {
                        $resolved = self::resolveIconUrl($icon->{$method}());
                        if ($resolved !== '') {
                            return $resolved;
                        }
                    } catch (\Throwable) {
                        // Try the next possible accessor.
                    }
                }
            }

            foreach (['getPublicUrl', 'getUrl'] as $method) {
                if (method_exists($icon, $method)) {
                    try {
                        $url = (string)$icon->{$method}();
                        if (trim($url) !== '') {
                            return self::normalizeIconPath($url);
                        }
                    } catch (\Throwable) {
                        // Graceful fallback instead of a broken detail view.
                    }
                }
            }
        }

        return '';
    }

    private static function normalizeIconPath(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, 'EXT:')) {
            try {
                $absolutePath = GeneralUtility::getFileAbsFileName($value);
                if ($absolutePath !== '' && is_file($absolutePath)) {
                    return PathUtility::getAbsoluteWebPath($absolutePath);
                }
            } catch (\Throwable) {
                return '';
            }
        }

        if (preg_match('#^(?:https?:)?//#i', $value) === 1 || str_starts_with($value, '/') || str_starts_with($value, 'data:image/')) {
            return $value;
        }

        if (preg_match('#^(?:fileadmin/|typo3conf/|_assets/|_processed/)#i', $value) === 1) {
            return '/' . ltrim($value, '/');
        }

        return '';
    }

    private static function looksLikeImageReference(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        return preg_match('#(?:^EXT:|^/|^https?://|^fileadmin/|^typo3conf/|^_assets/|^_processed/|\.(?:svg|png|jpe?g|gif|webp)(?:[?#].*)?$)#i', $value) === 1;
    }

    private static function sanitizeClassList(string $classes): string
    {
        $tokens = preg_split('/\s+/', trim($classes)) ?: [];
        $safe = [];
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token !== '' && preg_match('/^[A-Za-z0-9_:\-]+$/', $token) === 1) {
                $safe[] = $token;
            }
        }

        return implode(' ', array_unique($safe));
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
