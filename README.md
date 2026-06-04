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

## Frontend-Funktionen

- Gastgeberliste mit linker Filterspalte
- Filter als echte GET-Links mit teilbaren URLs
- Suchfeld über Name, Ort und Beschreibung
- Ansichten: Cards, Liste und Karte
- Icons in Filter, Cards, Listenansicht und Detailansicht
- Sterne-/Zertifikats-Badge oben rechts im Bild
- Detailseite mit großem Hero/Galerie-Layout
- Galerie-Modal für alle Bilder
- Ausstattungs-Modal ab mehr als sechs Merkmalen
- Top-Merkmale direkt unter dem Hero
- Anfragen-/Buchen-Box rechts neben dem Inhalt
- separate Preise-&-Kapazität-Box
- Leaflet/OpenStreetMap-Karte
- JSON-LD für `LodgingBusiness`

## Frontend-Plugins

- Gastgeber: Übersicht / Liste
- Gastgeber: Detailansicht
- Gastgeber: Kartenansicht
- Gastgeber: Teaser
- Gastgeber: Unterkunftsarten-Teaser
- Gastgeber: Filter

Empfohlen ist für normale Gastgeberseiten nur:

```text
1. Gastgeber: Übersicht / Liste
2. Gastgeber: Detailansicht auf separater Detailseite
```

Das separate Filter-Plugin ist nur für spezielle Einstiegsseiten vorgesehen.


## Verbindung Liste → Detailseite

Die Liste hat jetzt im Backend eine echte Seitenauswahl für die Detailseite. Dadurch muss keine UID mehr manuell eingetragen werden.

Empfohlenes Setup:

```text
Seite "Gastgeber / Übersicht"
└── Inhaltselement "Gastgeber: Übersicht / Liste"
    ├── Datensatz-Ordner / Storage PID = Ordner mit Gastgeber-Datensätzen
    └── Detailseite = Seite "Gastgeber / Detail"

Seite "Gastgeber / Detail"
└── Inhaltselement "Gastgeber: Detailansicht"
    ├── Datensatz-Ordner / Storage PID = gleicher Ordner wie in der Liste
    └── Übersichtsseite / Zurück-Link = Seite "Gastgeber / Übersicht"
```

Die Linkausgabe wurde wie bei etablierten TYPO3-Listen-/Detail-Plugins aufgebaut:

- Wenn in der Listenansicht eine Detailseite gewählt ist, werden Detail-Links gezielt an das Plugin `Gastgeber: Detailansicht` auf dieser Seite übergeben.
- Wenn keine Detailseite gewählt ist, kann die Listen-, Karten- oder Teaseransicht die Detailaktion als Fallback selbst anzeigen.
- Teaser- und Kartenplugin besitzen ebenfalls eine Detailseiten-Auswahl, damit Links von Startseiten oder Karten sauber zur Detailseite führen.

Für sprechende Detail-URLs liegt ein Beispiel unter:

```text
Configuration/Routes/GastgeberDetail.example.yaml
```

Der Inhalt kann in die jeweilige Site-Konfiguration übernommen und die UID der Detailseite angepasst werden.

## Backend-Pflege

Gastgeber-Datensätze sind redaktionell gegliedert in:

- Allgemein
- Medien
- Adresse / Karte
- Kontakt / Buchung
- Preise / Kapazität
- Ausstattung / Merkmale
- SEO / Suchmaschinen

Wichtig für Redakteure:

- erstes Bild in der Bildergalerie = Hero-Bild
- weitere Bilder erscheinen im Galerie-Modal
- Merkmale werden als eigene Datensätze gepflegt
- jedes Merkmal kann ein Icon oder eine CSS-Iconklasse bekommen
- jedes Merkmal kann für Filter, Cards, Detailseite und Top-Merkmal getrennt geschaltet werden
- Zertifikate/Sterne werden als eigene Datensätze gepflegt
- Koordinaten können manuell gepflegt oder per Geocoding aus der Anschrift ermittelt werden

## Installation

```bash
composer require d3-werk/gastgeber
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
vendor/bin/typo3 cache:warmup
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
- Bootstrap 5 wird für Modals und Buttons erwartet.
- Templates und SCSS können im Sitepackage über TYPO3-Template-Pfade überschrieben werden.

## Backend: Listenansicht sauber konfigurieren

Die Listenansicht verwendet eine eigene FlexForm `Configuration/FlexForms/List.xml`. Es gibt keine Abhängigkeit mehr zu EXT:news und keine News-Felder wie Sortierlogik, Kategorienmodus, Unterkategorien oder News-Pagination.

Für das Inhaltselement **Gastgeber: Übersicht / Liste** sind nur diese fachlichen Einstellungen relevant:

- Datensatz-Ordner
- Detailseite
- Standardansicht
- Standardsortierung
- Anzahl Gastgeber anzeigen
- Filterspalte anzeigen
- Ansicht-Umschalter anzeigen
- Kartenansicht aktivieren
- Unterkunftsarten vorauswählen
- Merkmalsgruppen im Filter einschränken
- Merkmalslogik
- Nur hervorgehobene Gastgeber anzeigen

Wenn im Backend weiterhin alte News-Felder erscheinen, ist meist noch ein alter TYPO3-Cache aktiv oder ein altes Inhaltselement vom Typ News-Plugin wird bearbeitet. In diesem Fall Cache leeren, das Inhaltselement als **Gastgeber: Übersicht / Liste** neu auswählen und den Datensatz einmal speichern.

### Merkmale in der Detailansicht

In der Gastgeber-Detailansicht werden maximal sechs Merkmale direkt sichtbar ausgegeben. Die Darstellung erfolgt als zweizeiliges 3er-Raster: links das gepflegte Icon, rechts der Merkmaltext. Weitere Merkmale werden über den Button „Weitere Merkmale anzeigen“ in einem Bootstrap-Modal ausgegeben. Im Backend wird die Merkmalsauswahl beim Gastgeber zusätzlich mit einem Icon-Hinweis und der Merkmalsgruppe im Auswahltext unterstützt.

## Backend-Beschriftungen / TCA

Alle fachlichen Tabellen der Standalone-Erweiterung besitzen jetzt explizite deutsche Feldbeschriftungen und Hilfetexte in der TCA. Dadurch erscheinen beim Anlegen und Bearbeiten von Gastgebern, Merkmalen, Merkmalsgruppen, Unterkunftsarten, Ortsteilen und Zertifikaten keine technischen Platzhalter wie `[title]`, `[slug]` oder `[description]` mehr.

Die Merkmalsauswahl im Gastgeber-Datensatz zeigt im Auswahltext zusätzlich ein kleines Text-Icon und die zugehörige Merkmalsgruppe. Dadurch können Redakteure die Merkmale schneller und sicherer auswählen.
