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
 * auch dann, wenn der Site-Route-Enhancer noch nicht sauber greift.
 *
 * GASTGEBER_ROUTE_SLUG_FALLBACK_FINAL_2026_06_09:
 * Die Middleware hängt nicht mehr zwingend davon ab, dass der Slug vorab in der
 * Datenbank gefunden wird. Sie schreibt den internen Pfad auf die Listenseite
 * zurück und übergibt den Slug als Extbase-Argument. Der Controller entscheidet
 * anschließend, ob ein Gastgeber gefunden wird. So entsteht kein Routing-404
 * mehr, nur weil der PersistedAliasMapper den Alias nicht auflösen konnte.
 */
final class SlugRoutingMiddleware implements MiddlewareInterface
{
    private const PLUGIN_NAMESPACE_LIST = 'tx_gastgeber_list';
    private const PLUGIN_NAMESPACE_DETAIL = 'tx_gastgeber_detail';
    private const GASTGEBER_PAGE_SEGMENT = 'gastgeber';

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
        $parentSegment = (string)($segments[count($segments) - 2] ?? '');

        // Begrenzung auf /gastgeber/{slug}. Dadurch werden andere Seitenpfade
        // nicht versehentlich als Gastgeber-Detailseite interpretiert.
        if (strtolower($parentSegment) !== self::GASTGEBER_PAGE_SEGMENT) {
            return $handler->handle($request);
        }

        if (!$this->isSlugCandidate($slug)) {
            return $handler->handle($request);
        }

        $hostUid = $this->findHostUidBySlug($slug);

        array_pop($segments);
        $internalPath = '/' . implode('/', $segments);
        if ($internalPath === '/') {
            return $handler->handle($request);
        }

        $queryParams = $this->withDetailArguments($queryParams, $slug, $hostUid);
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
                ->withAttribute('gastgeberResolvedSlug', $slug)
                ->withAttribute('gastgeberResolvedHostUid', $hostUid > 0 ? (string)$hostUid : '')
                ->withAttribute('gastgeberOriginalPath', $path)
        );
    }

    /**
     * @param array<string,mixed> $queryParams
     * @return array<string,mixed>
     */
    private function withDetailArguments(array $queryParams, string $slug, int $hostUid): array
    {
        $hostIdentifier = $hostUid > 0 ? (string)$hostUid : $slug;
        $arguments = [
            'action' => 'detail',
            'controller' => 'Host',
            'host' => $hostIdentifier,
            'slug' => $slug,
        ];

        // Je nach Einbau wird das Listen-Plugin oder Detail-Plugin gerendert.
        // Beide Namespaces bekommen dieselben Argumente; nur das vorhandene
        // Plugin wertet sie aus.
        $queryParams[self::PLUGIN_NAMESPACE_LIST] = $arguments;
        $queryParams[self::PLUGIN_NAMESPACE_DETAIL] = $arguments;

        return $queryParams;
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
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \Doctrine\DBAL\ParameterType::INTEGER)),
                    $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, \Doctrine\DBAL\ParameterType::INTEGER))
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
