# Sprechende Gastgeber-Detail-URLs

Der Gastgeber-Datensatz besitzt das Feld **URL-Segment / Slug** (`slug`).
Damit daraus eine Browser-URL wie

```text
/gastgeber/hotel-acht-linden
```

wird, muss der TYPO3 Route-Enhancer in der Site-Config aktiv sein.

## Ursache der URL mit Query-Parametern

Wenn die URL so aussieht:

```text
/gastgeber?tx_gastgeber_list%5Baction%5D=detail&tx_gastgeber_list%5Bcontroller%5D=Host&tx_gastgeber_list%5Bhost%5D=2&cHash=...
```

ist der Route-Enhancer für das **Plugin `List`** nicht aktiv oder nicht passend eingerichtet.
Ein Route-Enhancer für das Plugin `Detail` greift bei dieser URL nicht.

## Richtige Datei für die aktuelle Installation

Für eine Seite `/gastgeber`, auf der Liste und Detail über dasselbe Listen-Plugin laufen:

```text
EXT:gastgeber/Configuration/Routes/GastgeberListSamePage.example.yaml
```

Diesen Block in `config/sites/<site-identifier>/config.yaml` einfügen.
Wenn dort bereits `routeEnhancers:` existiert, nur den Block `GastgeberListDetail:` darunter einfügen.

## Prüfung

Nach dem Einbau und Cache-Flush sollte der Link nicht mehr so aussehen:

```text
/gastgeber?tx_gastgeber_list%5Bhost%5D=2&...
```

sondern so:

```text
/gastgeber/hotel-acht-linden
```
