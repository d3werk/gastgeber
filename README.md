# EXT:gastgeber

Die Extension erweitert News-Datensätze um Gastgeber-, Adress-, Karten- und Buchungsfelder und liefert Bootstrap-5-kompatible Templates, Partials, CSS/SCSS, ein Merkmal-Filtermenü sowie Listen-, Card- und Kartenansichten.

## Ziel

Gastgeber werden als News-Datensätze gepflegt und über `sys_category` strukturiert:

- Hotel
- Pension
- Ferienwohnung
- Ferienhaus
- Pferdehof / Reiterhof
- Bauernhof / Urlaub auf dem Bauernhof
- Campingstellplatz / Wohnmobilstellplatz / Zeltplatz

Die Merkmale werden ebenfalls als Kategorien gepflegt. Dadurch können Redakteure Merkmale wie Balkon, Terrasse, Hunde erlaubt, barrierefrei, WLAN, Parkplatz, Sauna, Pferdeboxen usw. direkt im Backend erweitern, umbenennen, sortieren, deaktivieren und mit Icons versehen.

## Systemvoraussetzungen

- TYPO3 CMS `^13.4.20 || ^14.0`
- PHP `>= 8.2 < 8.6`
- EXT:news `^14.0`
- Bootstrap Package empfohlen

## Installation über Composer / Packagist

Nach Veröffentlichung auf GitHub und Packagist kann die Extension direkt installiert werden:

```bash
composer require d3-werk/gastgeber
composer dump-autoload
vendor/bin/typo3 cache:flush
vendor/bin/typo3 extension:setup
```

Die Versionierung erfolgt über Git-Tags, z. B.:

```bash
git tag 1.0.0
git push origin main --tags
```

Wichtig für GitHub/Packagist: Die `composer.json` muss direkt im Repository-Root liegen. Der Extension-Inhalt wird also direkt in das Repository gelegt, nicht nochmals in einen zusätzlichen Unterordner `gastgeber/`.

## Lokale Entwicklung als Path-Repository

Für die Entwicklung kann die Extension weiterhin lokal eingebunden werden:

```bash
mkdir -p packages
# Extension nach packages/gastgeber entpacken
composer require d3-werk/gastgeber:@dev
composer dump-autoload
vendor/bin/typo3 cache:flush
vendor/bin/typo3 extension:setup
```

Falls das lokale Repository noch nicht in der Haupt-`composer.json` registriert ist:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "packages/*",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

## Site Set einbinden

Im TYPO3 Backend unter **Sites > Setup** das Set **„Gastgeberanbieter auf Basis EXT:news“** hinzufügen.

Alternativ in `config/sites/<site>/config.yaml`:

```yaml
dependencies:
  - georgringer/news
  - d3-werk/gastgeber
```

Das Set registriert die Fluid-Pfade, CSS/SCSS, Leaflet-Assets und die Gastgeber-spezifischen News-Einstellungen.

## Kategorien anlegen

```bash
vendor/bin/typo3 gastgeber:categories:create --pid=123
```

`123` ist die UID des Ordners oder der Seite, in dem die Kategorien liegen sollen.

### Empfohlener Kategoriebaum

```text
Gastgeber
├── Gastgeber-Art
│   ├── Hotel
│   ├── Pension
│   ├── Ferienwohnung
│   ├── Ferienhaus
│   ├── Pferdehof
│   ├── Bauernhof
│   └── Campingstellplatz
├── Merkmale
│   ├── Allgemein
│   │   ├── Bett & Bike
│   │   ├── Nichtraucher
│   │   ├── Rollstuhlgerecht / barrierefrei
│   │   ├── WLAN
│   │   └── Restaurant im Haus
│   ├── Außenbereich
│   │   ├── Balkon
│   │   ├── Terrasse
│   │   ├── Garten / Wiese
│   │   ├── Parkplatz
│   │   └── E-Ladestation
│   ├── Freizeit & Wellness
│   │   ├── Fahrradverleih
│   │   ├── Sauna
│   │   └── Spielplatz / Spielgeräte
│   ├── Haustiere & Reiten
│   │   ├── Hunde erlaubt
│   │   ├── Haustiere erlaubt
│   │   ├── Haustiere auf Anfrage
│   │   ├── Pferdeboxen
│   │   └── Bett & Box
│   └── Ferienwohnung / Ferienhaus
│       ├── Küche
│       ├── Kochnische
│       ├── Kinderbett
│       ├── Waschmaschine
│       ├── Trockner
│       └── Geschirrspüler
└── Zielgruppen
    ├── Paare
    ├── Familien
    ├── Gruppen
    ├── Reiter
    ├── Radfahrer
    ├── Wanderer
    └── Wohnmobilreisende
```


## Merkmal-Icons pflegen

Jede `sys_category` erhält einen zusätzlichen Reiter **Gastgeber-Filter**. Dort können Redakteure pro Merkmal pflegen:

- **Merkmal-Icon**: SVG/PNG/JPG/WEBP als Datei-Upload über FAL
- **Icon-CSS-Klasse**: optional, z. B. Bootstrap Icons, Font Awesome oder eine eigene Klasse aus dem Sitepackage
- **Im Gastgeber-Filter ausblenden**: Kategorie bleibt am Datensatz nutzbar, erscheint aber nicht im Frontend-Filter

Empfohlen ist ein einheitliches SVG-Iconset, möglichst quadratisch und einfarbig. Wenn sowohl ein Upload-Icon als auch eine CSS-Klasse gepflegt ist, hat der Upload Vorrang. Die per CLI angelegten Standard-Merkmale erhalten einfache Fallback-CSS-Klassen, die später im Backend durch echte Piktogramme ersetzt werden können.

## Backend-Pflege

Ein Gastgeber wird als News-Datensatz angelegt. Zusätzlich zu Titel, Teaser, Text, Medien und Kategorien werden vier übersichtliche Reiter ergänzt:

1. **Adresse / Karte**
   - Straße / Hausnummer
   - Adresszusatz
   - PLZ
   - Ort
   - Land
   - Breitengrad
   - Längengrad
   - Auf Karte anzeigen

2. **Kontakt / Buchung**
   - Telefon
   - E-Mail
   - Webseite
   - Buchungslink / Anfrage

3. **Preise / Kapazität**
   - Preis ab
   - Personen
   - Zimmer / Einheiten
   - Betten
   - Preis-Hinweis

4. **Ausstattung / Hinweise**
   - Ausstattung als RTE-Text
   - Öffnungszeiten / Saison
   - Klassifizierung / Zertifikate

Die filterbaren Merkmale werden nicht als feste Checkbox-Felder gespeichert, sondern als Kategorien im News-Reiter **Kategorien**. Das ist absichtlich so: Redakteure können den Merkmalbaum ohne Codeänderung im Listenmodul pflegen.

## Frontend-Aufbau wie Unterkunftsportal

Die Gastgeberseite besteht idealerweise aus diesen Inhaltselementen:

```text
[Einleitung / Header]
[Gastgeber: Merkmal-Filter]
[News: List]
```

Das Filterelement liegt direkt über der News-Liste. Es zeigt wie ein Such-/Buchungsmodul zuerst eine kompakte Kopfzeile mit Überschrift, Ansichtswahl und Filter-Schaltfläche. Darunter öffnet sich ein Filtermenü mit den Backend-Kategorien.

### Besucher-Ansichten

Die News-Listenansicht unterstützt drei Darstellungen:

- **Cards**: Bootstrap-Card-Grid, gut für emotionale Gastgeberdarstellung
- **Liste**: kompakte horizontale Darstellung, gut für viele Ergebnisse
- **Karte**: OpenStreetMap-/Leaflet-Karte links und Ergebnisliste rechts

URL-Parameter:

```text
tx_gastgeber_view=cards
tx_gastgeber_view=list
tx_gastgeber_view=map
```

Die Merkmal-Filterlinks behalten die gewählte Ansicht bei. Besucher können also in der Kartenansicht bleiben und dort z. B. nach „Hunde erlaubt“, „Terrasse“ oder „Pferdeboxen“ filtern.

## Filter einrichten

1. Auf der Gastgeber-Übersichtsseite ein neues Inhaltselement anlegen.
2. **Plugins > Gastgeber: Merkmal-Filter** auswählen.
3. Im Reiter **Filter-Einstellungen** die Merkmal-Wurzelkategorie wählen, meistens **Gastgeber > Merkmale**.
4. Zielseite mit dem News-Listen-Plugin auswählen. Wenn Filter und Liste auf derselben Seite liegen, kann das Feld leer bleiben.
5. Verknüpfung wählen:
   - **ODER**: Gastgeber mit mindestens einem gewählten Merkmal anzeigen.
   - **UND**: Gastgeber müssen alle gewählten Merkmale besitzen.

Wichtig: Im News-Listen-Plugin darf **Disable override demand** nicht aktiviert sein, da der Filter `tx_news_pi1[overwriteDemand][categories]` nutzt.

## Kartenansicht / OpenStreetMap

Die Kartenansicht verwendet Leaflet und OpenStreetMap-Kacheln. Das Site Set bindet Leaflet per CDN ein:

```typoscript
page.includeCSSLibs.gastgeberLeaflet = https://unpkg.com/leaflet@1.9.4/dist/leaflet.css
page.includeJSLibs.gastgeberLeaflet = https://unpkg.com/leaflet@1.9.4/dist/leaflet.js
page.includeJSFooter.gastgeberMap = EXT:gastgeber/Resources/Public/JavaScript/gastgeber-map.js
```

Für produktive Projekte mit strengen Datenschutzanforderungen kann Leaflet lokal ins Sitepackage gelegt und die TypoScript-Einbindung überschrieben werden. Die OpenStreetMap-Attribution darf nicht entfernt werden.

### Koordinaten

Für Kartenmarker müssen pro Gastgeber **Breitengrad** und **Längengrad** als Dezimalwerte gepflegt werden. Die Adresse allein wird bewusst nicht automatisch geokodiert, damit keine externen Geocoding-Anfragen aus dem Backend ausgelöst werden.

Beispiel Undeloh:

```text
Breitengrad: 53.1966000
Längengrad: 9.9762000
```

## Wichtige Dateien

```text
Classes/Command/CreateCategoriesCommand.php
Classes/Controller/FilterController.php
Classes/Domain/Model/News.php
Classes/Utility/CategoryIconResolver.php
Classes/ViewHelpers/CategoryIconViewHelper.php
Classes/ViewHelpers/CurrentViewViewHelper.php
Configuration/FlexForms/Filter.xml
Configuration/Sets/Gastgeber/setup.typoscript
Configuration/TCA/Overrides/tt_content.php
Configuration/TCA/Overrides/sys_category.php
Configuration/TCA/Overrides/tx_news_domain_model_news.php
Resources/Private/Templates/Filter/Index.html
Resources/Private/Templates/News/List.html
Resources/Private/Templates/News/Detail.html
Resources/Private/Partials/Category/Icon.html
Resources/Private/Partials/Category/Items.html
Resources/Private/Partials/Filter/Items.html
Resources/Private/Partials/List/Item.html
Resources/Private/Partials/List/Map.html
Resources/Private/Partials/Map/Container.html
Resources/Private/Partials/Map/Detail.html
Resources/Private/Partials/Map/List.html
Resources/Private/Partials/Map/MarkerSource.html
Resources/Private/Partials/Map/Popup.html
Resources/Private/Partials/View/Switch.html
Resources/Public/JavaScript/gastgeber-map.js
Resources/Public/Scss/gastgeber.scss
Resources/Public/Css/gastgeber.css
```

## TypoScript-Optionen

```typoscript
plugin.tx_news.settings.gastgeber {
  defaultView = cards
  viewParameter = tx_gastgeber_view
  showViewSwitch = 1
  map {
    defaultLatitude = 53.1966
    defaultLongitude = 9.9762
    defaultZoom = 13
    detailZoom = 16
    tileUrl = https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
    attribution = &copy; OpenStreetMap contributors
  }
}
```

## Hinweise zum bestehenden Sitepackage

Wenn im vorhandenen Sitepackage bereits eigene News-Templates registriert sind, entscheidet die Fluid-Pfad-Priorität. Dieses Set nutzt Priorität `160`. Soll diese Gastgeber-Extension gewinnen, das Set wie geliefert lassen. Soll das Sitepackage gewinnen, dort eine höhere Priorität setzen.


## Struktur der Kartenansicht

Die produktive Gastgeber-Liste wird weiterhin über `Resources/Private/Templates/News/List.html` gerendert, weil EXT:news die Listenaktion controller-/aktionsbasiert mit diesem Template verbindet. Die eigentliche Kartenansicht ist jetzt jedoch sauber ausgelagert:

```text
Resources/Private/Partials/Map/View.html
Resources/Private/Partials/Map/Container.html
Resources/Private/Partials/Map/MarkerSource.html
Resources/Private/Partials/Map/Popup.html
Resources/Private/Partials/Map/List.html
Resources/Private/Partials/Map/Detail.html
```

Zusätzlich liegt `Resources/Private/Templates/News/Map.html` als vorbereitete Standalone-Template-Datei bei, falls später ein eigenes Map-Plugin oder eine eigene Map-Action ergänzt wird. Für die aktuelle News-Integration ist `News/List.html` der aktive Einstiegspunkt.

## Responsive-Verhalten

Die Templates nutzen Bootstrap-5-Grids:

- Cards: `col-12 col-md-6 col-xl-4`
- Listenansicht: `col-12`
- Kartenansicht: Karte und Ergebnisliste sind auf großen Bildschirmen zweispaltig und auf Tablet/Mobil untereinander
- Filtergruppen: `col-12 col-lg-6 col-xxl-4`

Zusätzliche CSS-Regeln sorgen für mobile volle Breite der Umschalter, flexible Button-Zeilen, eine mobile Kartenhöhe und sauberen Umbruch langer Adressen, URLs und Überschriften.

## UX-Detailansicht und SEO-Felder

Die Detailansicht ist auf Gastgeber-Einträge ausgerichtet und nicht mehr wie ein reiner News-Artikel aufgebaut:

1. Hero-Bereich mit Bild, Gastgeber-Titel, Adresse, Kurztext, Kurzinfos und primären CTAs.
2. Inhaltsbereich mit Beschreibung, Merkmalen, Ausstattung, Klassifizierung und optionalen Inhaltselementen.
3. Sticky-Kontaktkarte mit Preis/Kapazität, Adresse, Telefon, E-Mail, Webseite und Buchungslink.
4. Optionale Kartenansicht, wenn `Auf Karte anzeigen`, Breitengrad und Längengrad gepflegt sind.

Im Backend gibt es zusätzlich den Reiter **SEO / Suchmaschinen** mit folgenden Feldern:

- SEO-Titel
- Meta-Beschreibung
- Fokus-Suchbegriff als redaktioneller Hinweis
- Social-Media-Titel
- Social-Media-Beschreibung
- Detailseite nicht indexieren

Die Fluid-Partial `Detail/SeoMeta.html` setzt daraus Seitentitel, Meta-Description, OpenGraph-Tags und optional `robots=noindex,follow`.
