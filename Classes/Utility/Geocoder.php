<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Utility;

class Geocoder
{
    /** @return array{lat:string,lon:string}|null */
    public function geocodeAddress(string $address): ?array
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }
        $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . rawurlencode($address);
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => "User-Agent: d3-werk-gastgeber-typo3-extension/1.0\r\n",
            ],
        ]);
        $json = @file_get_contents($url, false, $context);
        if (!is_string($json) || $json === '') {
            return null;
        }
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data[0]['lat'], $data[0]['lon'])) {
            return null;
        }
        return ['lat' => (string)$data[0]['lat'], 'lon' => (string)$data[0]['lon']];
    }
}
