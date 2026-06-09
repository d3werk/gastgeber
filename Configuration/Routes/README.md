# Gastgeber Routing

Diese Extension liefert eine Route-Enhancer-Konfiguration für SEO-freundliche und reload-sichere Gastgeber-Detail-URLs.

## Ziel

Aus einer technischen Extbase-URL:

```text
/gastgeber?tx_gastgeber_list[action]=detail&tx_gastgeber_list[controller]=Host&tx_gastgeber_list[host]=2&cHash=...
```

wird serverseitig:

```text
/gastgeber/hotel-acht-linden
```

## Einbindung in die echte Site-Konfiguration

Die Datei muss in der TYPO3-Site-Konfiguration geladen werden:

```text
config/sites/<site-identifier>/config.yaml
```

Dort auf oberster Ebene eintragen:

```yaml
imports:
  - { resource: "EXT:gastgeber/Configuration/Routes/Gastgeber.yaml" }
```

Wichtig: Diese Zeile gehört nicht in `Configuration/Sets/Gastgeber/config.yaml` der Extension und nicht in das Sitepackage-Site-Set.

## Slug-Fallback gegen 404

Kennung:

```text
GASTGEBER_ROUTE_SLUG_FALLBACK_FINAL_2026_06_09 / GASTGEBER_ROUTE_NO_CHASH_MIDDLEWARE_FINAL_2026_06_09 / GASTGEBER_ROUTE_PRESITE_REWRITE_FINAL_2026_06_09
```

Die Route enthält absichtlich zwei Varianten:

1. `/{slug}` ohne `PersistedAliasMapper` für eingehende URLs wie `/gastgeber/hotel-acht-linden`.
2. `/{host}` mit `PersistedAliasMapper` für die SEO-Link-Erzeugung aus Extbase-Links.

Dadurch wird ein gültiger URL-Slug nicht bereits im TYPO3-Routing mit 404 abgewiesen, wenn der Mapper den Alias nicht direkt auflösen kann. Der Controller erhält den Slug und löst den Gastgeber selbst auf.

## Empfohlene Begrenzung

Empfohlen ist `limitToPages` mit der UID der Seite `/gastgeber`:

```yaml
limitToPages:
  - 123
```

Dadurch wird der Platzhalter nur auf der Gastgeberseite ausgewertet.

## Middleware-Fallback

Zusätzlich enthält die Extension eine Middleware, die `/gastgeber/{slug}` vor dem PageResolver intern auf `/gastgeber` mit Extbase-Detailargumenten zurückschreibt. Auch diese Middleware ist auf den Pfadbestandteil `gastgeber` begrenzt.
