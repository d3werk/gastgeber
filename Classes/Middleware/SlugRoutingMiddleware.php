<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Cache\CacheHashCalculator;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Unterstützt sprechende Gastgeber-Detailpfade wie /gastgeber/hotel-acht-linden
 * auch dann, wenn der Site-Route-Enhancer noch nicht aktiv greift.
 *
 * Die Middleware läuft vor dem TYPO3 PageResolver. Sie erkennt den letzten
 * Pfadbestandteil als Gastgeber-Slug, entfernt ihn für die interne Seitenauflösung
 * und ergänzt intern die Extbase-Argumente für das Listen-Plugin.
 */
final class SlugRoutingMiddleware implements MiddlewareInterface
{
    private const PLUGIN_NAMESPACE = 'tx_gastgeber_list';

    /** @var array<int,string> */
    private const ALLOWED_PARENT_SEGMENTS = [
        'gastgeber',
        'gastgeberverzeichnis',
        'unterkuenfte',
        'unterkünfte',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        if ($method !== 'GET' && $method !== 'HEAD') {
            return $handler->handle($request);
        }

        $queryParams = $request->getQueryParams();
        if ($this->hasGastgeberArguments($queryParams)) {
            return $handler->handle($request);
        }

        $uri = $request->getUri();
        $path = rawurldecode($uri->getPath());
        $segments = array_values(array_filter(explode('/', trim($path, '/')), static fn (string $segment): bool => $segment !== ''));

        if (count($segments) < 2) {
            return $handler->handle($request);
        }

        $slug = (string)end($segments);
        if (!$this->isSlugCandidate($slug)) {
            return $handler->handle($request);
        }

        $parentSegment = strtolower((string)($segments[count($segments) - 2] ?? ''));
        if (!in_array($parentSegment, self::ALLOWED_PARENT_SEGMENTS, true)) {
            return $handler->handle($request);
        }

        $hostUid = $this->findHostUidBySlug($slug);
        if ($hostUid <= 0) {
            return $handler->handle($request);
        }

        array_pop($segments);
        $internalPath = '/' . implode('/', $segments);
        if ($internalPath === '/') {
            return $handler->handle($request);
        }

        $queryParams[self::PLUGIN_NAMESPACE] = [
            'action' => 'detail',
            'controller' => 'Host',
            'host' => (string)$hostUid,
        ];
        unset($queryParams['cHash']);

        $queryStringWithoutHash = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        if ($queryStringWithoutHash !== '') {
            try {
                $queryParams['cHash'] = GeneralUtility::makeInstance(CacheHashCalculator::class)
                    ->generateForParameters($queryStringWithoutHash);
            } catch (\Throwable) {
                // Falls die cHash-Erzeugung in einer Sonderumgebung nicht verfügbar ist,
                // wird die Anfrage trotzdem weitergereicht. TYPO3 entscheidet dann selbst.
            }
        }

        $newUri = $uri
            ->withPath($internalPath)
            ->withQuery(http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986));

        return $handler->handle(
            $request
                ->withUri($newUri)
                ->withQueryParams($queryParams)
        );
    }

    /** @param array<string,mixed> $queryParams */
    private function hasGastgeberArguments(array $queryParams): bool
    {
        foreach (array_keys($queryParams) as $key) {
            if (str_starts_with((string)$key, 'tx_gastgeber_')) {
                return true;
            }
        }

        return false;
    }

    private function isSlugCandidate(string $slug): bool
    {
        if ($slug === '' || strlen($slug) > 180) {
            return false;
        }

        if (str_contains($slug, '.') || str_contains($slug, '?') || str_contains($slug, '&')) {
            return false;
        }

        return preg_match('/^[a-z0-9][a-z0-9\-_]*$/i', $slug) === 1;
    }

    private function findHostUidBySlug(string $slug): int
    {
        try {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('tx_gastgeber_domain_model_host');

            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder->getRestrictions()->removeAll();

            $row = $queryBuilder
                ->select('uid')
                ->from('tx_gastgeber_domain_model_host')
                ->where(
                    $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)),
                    $queryBuilder->expr()->eq('deleted', 0),
                    $queryBuilder->expr()->eq('hidden', 0)
                )
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAssociative();
        } catch (\Throwable) {
            return 0;
        }

        return (int)($row['uid'] ?? 0);
    }
}
