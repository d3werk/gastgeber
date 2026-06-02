# Gastgeber

Eigenständige TYPO3-Erweiterung für Gastgeber, Unterkünfte, Filter, Listenansichten, Detailseiten, Galerien und Karten.

## Wichtig

Diese Version arbeitet **unabhängig von EXT:news**. Gastgeber werden in der eigenen Tabelle `tx_gastgeber_domain_model_host` gepflegt.

## Installation

```bash
composer require d3-werk/gastgeber
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
```

Danach Datenbankstruktur prüfen und übernehmen.

## Kategorien anlegen

```bash
vendor/bin/typo3 gastgeber:categories:create --pid=123
```

`123` ist die UID des Ordners, in dem `sys_category`-Datensätze liegen sollen.

## Seitenaufbau

- Seite „Gastgeber“: Inhaltselement **Gastgeber: Übersicht / Liste**
- Seite „Gastgeber Detail“: Inhaltselement **Gastgeber: Detailansicht**

Im Listen-Element die Detailseite auswählen.

## Backend-Pflege

Gastgeber werden als eigene Datensätze gepflegt. Die wichtigsten Reiter:

- Allgemein: Titel, URL, Kurztext, Beschreibung, Bilder, Kategorien
- Adresse / Karte: Anschrift, automatische Koordinaten, Kartenanzeige
- Kontakt / Buchung: Telefon, E-Mail, Website, Anfrage-/Buchungslink, Buchungstext
- Preise / Kapazität: Preise, Personen, Zimmer, Betten, Fläche
- Ausstattung / Hinweise: Ausstattungstext und Klassifizierung
- SEO / Suchmaschinen: SEO-Titel, Meta-Beschreibung, Social-Media-Texte

## Merkmale und Icons

Merkmale sind `sys_category`-Datensätze. Pro Kategorie können gepflegt werden:

- Merkmal-Icon als Datei
- Icon-CSS-Klasse
- Top-Merkmal für die Detailseite
- Im Filter ausblenden

## Karten

Koordinaten können manuell gepflegt oder beim Speichern über die Anschrift ermittelt werden. Die Kartenansicht nutzt Leaflet/OpenStreetMap.
