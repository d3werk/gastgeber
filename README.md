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
- Ansichten: Cards und Liste, zusätzliche Kartenansicht als Bootstrap-Modal
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


## Änderung: Kartenansicht in der Listenansicht

Der Ansicht-Umschalter der Gastgeberliste zeigt wieder drei Optionen: `Cards`, `Liste` und `Karte`.
Die Kartenoption öffnet jetzt ein Bootstrap-Modal mit der gefilterten Gastgeberkarte. Die einzelnen Gastgeber-Cards enthalten keinen separaten Link `Auf Karte` mehr. Die Personen-Kurzinfo in der Card wurde ebenfalls entfernt; Preis, Betten und Quadratmeter bleiben erhalten.

Wichtig für die Ausgabe: `settings.showMap = 1` ist im Set als Standard gesetzt. Wenn die Karte in einem bestehenden Inhaltselement weiterhin nicht erscheint, das Listen-Plugin einmal im Backend öffnen, `Kartenansicht aktivieren` prüfen und speichern.

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


## Sterne-Klassifizierung direkt an der Unterkunft

Gastgeber-Datensätze besitzen wieder ein eigenes Feld **Sterne-Klassifizierung**. Redakteure wählen dort direkt 1 bis 5 Sterne aus und können zusätzlich **Superior** aktivieren. Diese direkte Auswahl wird in Listen- und Detail-Hero als Badge angezeigt und für JSON-LD `starRating` genutzt. Die Relation **Klassifizierung / Sterne / Zertifikate** bleibt zusätzlich für DTV, Bett+Bike oder weitere Prüfsiegel erhalten und dient als Fallback, wenn keine direkte Sterne-Auswahl gepflegt wurde.

## Flexible Preise & Kapazität als Tabelle

Der Bereich **Preise / Kapazität** ist jetzt nicht mehr auf wenige starre Felder beschränkt. Im Gastgeber-Datensatz gibt es zusätzlich die Inline-Tabelle **Flexible Preistabelle**.

Redakteure können beliebig viele Preiszeilen anlegen, sortieren und bei Bedarf deaktivieren:

- **Preiszeile**: linke Spalte „Leistung / Bezeichnung“, rechte Spalte „Preis / Beschreibung“
- **Zwischenüberschrift**: trennt längere Preisblöcke, z. B. „Pferdeboxen“ oder „Ferienwohnungen“
- **Hinweis**: läuft über beide Spalten, z. B. Kurtaxe, Mindestaufenthalt oder Sonderbedingungen

Damit lassen sich einfache Preislisten wie „Einzelzimmer ab 70 €“ genauso pflegen wie umfangreiche Beschreibungen mit mehrzeiligen Bedingungen für Ferienwohnungen, Hunde, Pferdeboxen oder Zusatzleistungen. Das Feld **Preis ab / Kurzpreis** bleibt nur noch als optionale Kurzinfo für Cards, Listen und Schnellüberblick erhalten.

## Mobile- und Tablet-Optimierung

Die Frontend-Ausgabe wurde für Smartphone und Tablet überarbeitet:

- Die linke Filterspalte wird auf Tablet/Smartphone über einen mobilen Filter-Button ein- und ausgeblendet.
- Der Ansicht-Umschalter bricht sauber um und verursacht keine horizontale Überbreite mehr.
- Listen-Karten stapeln sich auf kleineren Displays sauber untereinander.
- Detail-Hero, Sterne-Badge und Galerie-Button sind für kleine Displays neu positioniert.
- In der Detailansicht erscheint direkt unter dem Hero eine mobile Schnellaktionsleiste für Buchung, E-Mail und Telefon.
- Merkmale, Top-Merkmale, Preistabelle, Kapazitätsboxen, Karte und Modale wurden responsiv nachgeschärft.
- Die flexible Preistabelle wird ab Smartphone-Breite als gestapelte Tabelle ausgegeben, damit lange Preisbeschreibungen lesbar bleiben.

Wichtig: Für den mobilen Filter-Button und die vorhandenen Modale wird Bootstrap-JavaScript benötigt. Im Bootstrap Package ist dies üblicherweise bereits vorhanden.

### Detail-Galerie: Desktop-Mosaik

Die Detailansicht zeigt auf Desktop und Tablet-Landscape links das Hauptbild und rechts exakt vier Vorschaubilder aus der Galerie. Die Galerie verwendet eine feste Mosaik-Höhe und ein 2×2-Raster, damit die rechte Bildspalte immer bündig mit dem Hauptbild abschließt. Ab Tablet-Portrait/Smartphone wird die rechte Bildspalte ausgeblendet; alle weiteren Bilder bleiben über den Button „Alle Bilder anzeigen“ im Modal erreichbar.

## Eigenes Marker-/Standort-Symbol für die Karte

Die Kartenansicht verwendet ein eigenes SVG-Standardsymbol aus `Resources/Public/Icons/map-marker.svg`, damit der Standort-Marker nicht von den Leaflet-Standardgrafiken auf externen Pfaden abhängt.

Ein eigenes Symbol kann pro Plugin im Backend-FlexForm-Feld **Eigenes Marker-/Standort-Symbol** eingetragen werden, z. B.:

```text
/fileadmin/undeloh/karte-marker.svg
```

Alternativ global per TypoScript:

```typoscript
plugin.tx_gastgeber.settings.mapMarkerIconUrl = /fileadmin/undeloh/karte-marker.svg
plugin.tx_gastgeber.settings.mapMarkerIconWidth = 38
plugin.tx_gastgeber.settings.mapMarkerIconHeight = 46
plugin.tx_gastgeber.settings.mapMarkerIconAnchorX = 19
plugin.tx_gastgeber.settings.mapMarkerIconAnchorY = 46
plugin.tx_gastgeber.settings.mapMarkerPopupAnchorY = -42
```

Für andere Symbolgrößen müssen Breite, Höhe und Anker passend gesetzt werden. Der Anker ist der Punkt des Bildes, der exakt auf der Koordinate liegen soll.

