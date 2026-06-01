<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Utility;

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Small backend geocoder for Gastgeber records.
 *
 * Uses the public OpenStreetMap Nominatim search endpoint for occasional
 * editorial geocoding. It is intentionally only used during backend save when
 * coordinates are missing or when the editor explicitly requests refresh.
 */
final class Geocoder
{
    private const ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    public function __construct(
        private readonly ?RequestFactory $requestFactory = null
    ) {}

    /**
     * @return array{latitude: string, longitude: string}|null
     */
    public function geocodeAddress(string $address): ?array
    {
        $address = trim(preg_replace('/\s+/', ' ', $address) ?? '');
        if ($address === '') {
            return null;
        }

        $requestFactory = $this->requestFactory ?? GeneralUtility::makeInstance(RequestFactory::class);

        try {
            $response = $requestFactory->request(self::ENDPOINT, 'GET', [
                'query' => [
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'addressdetails' => 0,
                    'countrycodes' => 'de',
                    'q' => $address,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    // Nominatim requires a meaningful User-Agent for API requests.
                    'User-Agent' => 'd3-werk/gastgeber TYPO3 extension; https://github.com/d3werk/gastgeber',
                ],
                'timeout' => 8,
            ]);
        } catch (\Throwable) {
            return null;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return null;
        }

        $payload = json_decode((string)$response->getBody(), true);
        if (!is_array($payload) || $payload === [] || !isset($payload[0]['lat'], $payload[0]['lon'])) {
            return null;
        }

        $latitude = (float)$payload[0]['lat'];
        $longitude = (float)$payload[0]['lon'];

        if (!$this->isValidLatitude($latitude) || !$this->isValidLongitude($longitude)) {
            return null;
        }

        return [
            'latitude' => number_format($latitude, 7, '.', ''),
            'longitude' => number_format($longitude, 7, '.', ''),
        ];
    }

    public function isEmptyCoordinate(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        $normalized = str_replace(',', '.', trim((string)$value));
        if ($normalized === '') {
            return true;
        }
        return abs((float)$normalized) < 0.0000001;
    }

    public function normalizeCoordinate(mixed $value): string
    {
        $normalized = str_replace(',', '.', trim((string)$value));
        if ($normalized === '') {
            return '0.0000000';
        }
        return number_format((float)$normalized, 7, '.', '');
    }

    private function isValidLatitude(float $latitude): bool
    {
        return $latitude >= -90.0 && $latitude <= 90.0;
    }

    private function isValidLongitude(float $longitude): bool
    {
        return $longitude >= -180.0 && $longitude <= 180.0;
    }
}
