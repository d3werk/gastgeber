<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Hooks;

use D3Werk\Gastgeber\Utility\Geocoder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class HostGeocodeDataHandlerHook
{
    public function processDatamap_afterDatabaseOperations(string $status, string $table, string|int $id, array $fieldArray, DataHandler $dataHandler): void
    {
        if ($table !== 'tx_gastgeber_domain_model_host') {
            return;
        }
        $uid = $status === 'new' && isset($dataHandler->substNEWwithIDs[(string)$id]) ? (int)$dataHandler->substNEWwithIDs[(string)$id] : (int)$id;
        if ($uid <= 0) {
            return;
        }
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $row = $connection->select(['*'], $table, ['uid' => $uid])->fetchAssociative();
        if (!is_array($row) || (int)($row['geocode_on_save'] ?? 0) !== 1) {
            return;
        }
        $street = trim((string)($row['street'] ?? ''));
        $city = trim((string)($row['city'] ?? ''));
        if ($street === '' || $city === '') {
            return;
        }
        $address = trim(implode(', ', array_filter([$street, trim((string)($row['zip'] ?? '') . ' ' . $city), (string)($row['country'] ?? '')])));
        $result = GeneralUtility::makeInstance(Geocoder::class)->geocodeAddress($address);
        if ($result === null) {
            return;
        }
        $connection->update($table, [
            'latitude' => $result['lat'],
            'longitude' => $result['lon'],
            'geocode_on_save' => 0,
            'show_on_map' => 1,
        ], ['uid' => $uid]);
    }
}
