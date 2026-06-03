# Gastgeber

Eigenständige TYPO3-Erweiterung für ein professionelles Gastgeber- und Unterkunftsverzeichnis.

## Architektur

Die Extension arbeitet unabhängig von EXT:news. Gastgeber, Unterkunftsarten, Merkmalsgruppen, Merkmale, Ortsteile und Zertifikate werden in eigenen Tabellen gespeichert:

- `tx_gastgeber_domain_model_host`
- `tx_gastgeber_domain_model_type`
- `tx_gastgeber_domain_model_featuregroup`
- `tx_gastgeber_domain_model_feature`
- `tx_gastgeber_domain_model_district`
- `tx_gastgeber_domain_model_certificate`

## Frontend-Plugins

- Gastgeber: Übersicht / Liste
- Gastgeber: Detailansicht
- Gastgeber: Kartenansicht
- Gastgeber: Teaser
- Gastgeber: Unterkunftsarten-Teaser
- Gastgeber: Filter

## Backend-Pflege

Gastgeber-Datensätze sind redaktionell gegliedert in:

- Allgemein
- Medien
- Adresse / Karte
- Kontakt / Buchung
- Preise / Kapazität
- Ausstattung / Merkmale
- SEO / Suchmaschinen

Merkmale haben eigene Felder für Icon, Icon-CSS-Klasse, Filterbarkeit, Anzeige in Cards, Anzeige auf der Detailseite und Top-Merkmal.

## Installation

```bash
composer require d3-werk/gastgeber
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
```

Danach in den Admin Tools die Datenbankstruktur analysieren und übernehmen.

## Standarddaten anlegen

```bash
vendor/bin/typo3 gastgeber:setup:defaults --pid=123
```

`123` ist die UID des Speicherordners für Gastgeber-Stammdaten.

## Empfohlener Seitenaufbau

```text
Gastgeber
├── Übersicht
│   └── Gastgeber: Übersicht / Liste
├── Detail
│   └── Gastgeber: Detailansicht
└── Karte
    └── Gastgeber: Kartenansicht
```

## Hinweise

- Die Karte basiert auf Leaflet/OpenStreetMap.
- Externe Kartenkacheln sollten im Projekt datenschutzrechtlich geprüft werden.
- Templates und SCSS können im Sitepackage über TYPO3-Template-Pfade überschrieben werden.
