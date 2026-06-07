<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Renders uploaded icons for host types, feature groups, features and certificates.
 *
 * Uploaded equipment icons are small SVG/PNG pictograms. They must not be sent
 * through TYPO3 image processing, because SVG processing or an unexpected FAL
 * relation shape can otherwise break list/detail views. This ViewHelper resolves
 * the public URL directly and falls back to icon_class or a neutral placeholder.
 */
final class IconViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /** @var array<string,string> */
    private static array $relationUrlCache = [];

    public function initializeArguments(): void
    {
        $this->registerArgument('item', 'mixed', 'Object or array with icon and iconClass/icon_class properties', true);
        $this->registerArgument('imgClass', 'string', 'CSS class for uploaded/path icons', false, 'gastgeber-icon-img');
        $this->registerArgument('iconClass', 'string', 'Base CSS class for CSS icons', false, 'gastgeber-icon');
        $this->registerArgument('fallbackClass', 'string', 'Fallback CSS class', false, 'gastgeber-icon gastgeber-icon--fallback');
    }

    public function render(): string
    {
        $item = $this->arguments['item'] ?? null;
        $imgClass = self::sanitizeClassList((string)($this->arguments['imgClass'] ?? 'gastgeber-icon-img')) ?: 'gastgeber-icon-img';
        $iconBaseClass = self::sanitizeClassList((string)($this->arguments['iconClass'] ?? 'gastgeber-icon')) ?: 'gastgeber-icon';
        $fallbackClass = self::sanitizeClassList((string)($this->arguments['fallbackClass'] ?? 'gastgeber-icon gastgeber-icon--fallback')) ?: 'gastgeber-icon gastgeber-icon--fallback';

        $icon = self::readProperty($item, 'icon');
        $configuredClassOrPath = trim((string)(self::readProperty($item, 'iconClass') ?: self::readProperty($item, 'icon_class')));

        $url = self::resolveIconUrl($icon);

        // If getIcon() cannot safely return the FAL relation because the hydrated
        // value has a different shape, resolve the file reference directly via
        // sys_file_reference. This keeps the frontend stable even with old caches
        // or records created by previous extension versions.
        if ($url === '') {
            $url = self::resolveIconUrlFromFileReferenceRelation($item);
        }

        // Editors sometimes paste /fileadmin/... or EXT:... into the Icon-CSS field.
        // Treat image-looking values as image paths, not as CSS classes.
        if ($url === '' && self::looksLikeImageReference($configuredClassOrPath)) {
            $url = self::normalizeIconPath($configuredClassOrPath);
            $configuredClassOrPath = '';
        }

        if ($url !== '') {
            return '<img src="' . self::escape($url) . '" class="' . self::escape($imgClass) . '" alt="" loading="lazy" aria-hidden="true" />';
        }

        $configuredClass = self::sanitizeClassList($configuredClassOrPath);
        if ($configuredClass !== '') {
            return '<span class="' . self::escape(trim($iconBaseClass . ' ' . $configuredClass)) . '" aria-hidden="true"></span>';
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

        if (!is_object($item)) {
            return null;
        }

        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
        if (method_exists($item, $method)) {
            try {
                return $item->{$method}();
            } catch (\Throwable) {
                return null;
            }
        }

        if (method_exists($item, '_loadRealInstance')) {
            try {
                $realInstance = $item->_loadRealInstance();
                if ($realInstance !== $item) {
                    return self::readProperty($realInstance, $property);
                }
            } catch (\Throwable) {
                return null;
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
                if (array_key_exists($key, $icon)) {
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

        if (!is_object($icon)) {
            return '';
        }

        foreach (['getOriginalResource', 'getOriginalFile', 'getFile'] as $method) {
            if (method_exists($icon, $method)) {
                try {
                    $resolved = self::resolveIconUrl($icon->{$method}());
                    if ($resolved !== '') {
                        return $resolved;
                    }
                } catch (\Throwable) {
                    // Try next accessor.
                }
            }
        }

        foreach (['getPublicUrl', 'getUrl'] as $method) {
            if (method_exists($icon, $method)) {
                try {
                    $url = trim((string)$icon->{$method}());
                    if ($url !== '') {
                        return self::normalizeIconPath($url);
                    }
                } catch (\Throwable) {
                    // Graceful fallback instead of a broken frontend view.
                }
            }
        }

        return '';
    }

    private static function resolveIconUrlFromFileReferenceRelation(mixed $item): string
    {
        if (!is_object($item)) {
            return '';
        }

        $uid = self::readUid($item);
        $tableName = self::resolveTableName($item);
        if ($uid <= 0 || $tableName === '') {
            return '';
        }

        $cacheKey = $tableName . ':' . $uid;
        if (array_key_exists($cacheKey, self::$relationUrlCache)) {
            return self::$relationUrlCache[$cacheKey];
        }

        try {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
            $row = $queryBuilder
                ->select('uid')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($tableName)),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('icon')),
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
                )
                ->orderBy('sorting_foreign', 'ASC')
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAssociative();

            if (!is_array($row) || empty($row['uid'])) {
                return self::$relationUrlCache[$cacheKey] = '';
            }

            $fileReference = GeneralUtility::makeInstance(ResourceFactory::class)->getFileReferenceObject((int)$row['uid']);
            $url = self::resolveIconUrl($fileReference);
            return self::$relationUrlCache[$cacheKey] = $url;
        } catch (\Throwable) {
            return self::$relationUrlCache[$cacheKey] = '';
        }
    }

    private static function readUid(object $item): int
    {
        foreach (['getUid', 'getLocalizedUid'] as $method) {
            if (method_exists($item, $method)) {
                try {
                    return (int)$item->{$method}();
                } catch (\Throwable) {
                    // Try next accessor.
                }
            }
        }
        return 0;
    }

    private static function resolveTableName(object $item): string
    {
        if (method_exists($item, '_loadRealInstance')) {
            try {
                $realInstance = $item->_loadRealInstance();
                if (is_object($realInstance) && $realInstance !== $item) {
                    return self::resolveTableName($realInstance);
                }
            } catch (\Throwable) {
                // Continue with proxy class name.
            }
        }

        $className = get_class($item);
        $shortName = substr(strrchr($className, '\\') ?: $className, 1) ?: $className;

        return match ($shortName) {
            'Feature' => 'tx_gastgeber_domain_model_feature',
            'FeatureGroup' => 'tx_gastgeber_domain_model_featuregroup',
            'HostType' => 'tx_gastgeber_domain_model_type',
            'Certificate' => 'tx_gastgeber_domain_model_certificate',
            default => '',
        };
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
