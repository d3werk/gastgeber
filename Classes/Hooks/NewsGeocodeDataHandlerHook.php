<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Hooks;

use D3Werk\Gastgeber\Utility\Geocoder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Fills latitude/longitude from address data during backend save.
 *
 * The hook never blocks saving. If geocoding fails, existing/manual values stay
 * untouched and the record can still be saved normally.
 */
final class NewsGeocodeDataHandlerHook
{
    private const TABLE = 'tx_news_domain_model_news';

    /**
     * @param array<string, mixed> $incomingFieldArray
     */
    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, string $table, string|int $id, DataHandler $dataHandler): void
    {
        if ($table !== self::TABLE) {
            return;
        }

        if (!$this->recordContainsGastgeberFields($incomingFieldArray)) {
            return;
        }

        $geocoder = GeneralUtility::makeInstance(Geocoder::class);

        // Normalize manually entered decimal comma values before TYPO3 persists them.
        if (array_key_exists('tx_gastgeber_latitude', $incomingFieldArray)) {
            $incomingFieldArray['tx_gastgeber_latitude'] = $geocoder->normalizeCoordinate($incomingFieldArray['tx_gastgeber_latitude']);
        }
        if (array_key_exists('tx_gastgeber_longitude', $incomingFieldArray)) {
            $incomingFieldArray['tx_gastgeber_longitude'] = $geocoder->normalizeCoordinate($incomingFieldArray['tx_gastgeber_longitude']);
        }

        $existingRecord = $this->getExistingRecord($id);
        $address = $this->buildAddress($incomingFieldArray, $existingRecord);
        if ($address === '') {
            return;
        }

        $refreshRequested = (bool)($incomingFieldArray['tx_gastgeber_geocode_on_save'] ?? false);
        $latitude = $incomingFieldArray['tx_gastgeber_latitude'] ?? ($existingRecord['tx_gastgeber_latitude'] ?? null);
        $longitude = $incomingFieldArray['tx_gastgeber_longitude'] ?? ($existingRecord['tx_gastgeber_longitude'] ?? null);
        $coordinatesMissing = $geocoder->isEmptyCoordinate($latitude) || $geocoder->isEmptyCoordinate($longitude);

        if (!$refreshRequested && !$coordinatesMissing) {
            return;
        }

        $result = $geocoder->geocodeAddress($address);
        if ($result === null) {
            return;
        }

        $incomingFieldArray['tx_gastgeber_latitude'] = $result['latitude'];
        $incomingFieldArray['tx_gastgeber_longitude'] = $result['longitude'];
        // Reset refresh flag after a successful lookup. Editors can enable it again
        // later if an address was corrected and the coordinates should be updated.
        $incomingFieldArray['tx_gastgeber_geocode_on_save'] = 0;
    }

    /**
     * @param array<string, mixed> $incomingFieldArray
     */
    private function recordContainsGastgeberFields(array $incomingFieldArray): bool
    {
        foreach (array_keys($incomingFieldArray) as $fieldName) {
            if (str_starts_with((string)$fieldName, 'tx_gastgeber_')) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function getExistingRecord(string|int $id): array
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            return [];
        }

        try {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable(self::TABLE);
            $row = $queryBuilder
                ->select(
                    'uid',
                    'tx_gastgeber_street',
                    'tx_gastgeber_zip',
                    'tx_gastgeber_city',
                    'tx_gastgeber_country',
                    'tx_gastgeber_latitude',
                    'tx_gastgeber_longitude'
                )
                ->from(self::TABLE)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$id, \PDO::PARAM_INT))
                )
                ->executeQuery()
                ->fetchAssociative();
        } catch (\Throwable) {
            return [];
        }

        return is_array($row) ? $row : [];
    }

    /**
     * @param array<string, mixed> $incomingFieldArray
     * @param array<string, mixed> $existingRecord
     */
    private function buildAddress(array $incomingFieldArray, array $existingRecord): string
    {
        $street = trim((string)($incomingFieldArray['tx_gastgeber_street'] ?? ($existingRecord['tx_gastgeber_street'] ?? '')));
        $city = trim((string)($incomingFieldArray['tx_gastgeber_city'] ?? ($existingRecord['tx_gastgeber_city'] ?? '')));

        // Avoid geocoding all new records to a town centre just because PLZ/Ort/Land
        // have defaults. A street and a city are the minimum for a useful lookup.
        if ($street === '' || $city === '') {
            return '';
        }

        $parts = [];
        foreach (['tx_gastgeber_street', 'tx_gastgeber_zip', 'tx_gastgeber_city', 'tx_gastgeber_country'] as $fieldName) {
            $value = $incomingFieldArray[$fieldName] ?? ($existingRecord[$fieldName] ?? '');
            $value = trim((string)$value);
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return trim(implode(', ', $parts));
    }
}
