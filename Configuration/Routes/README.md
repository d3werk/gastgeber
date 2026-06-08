# Gastgeber Routing

Diese Extension liefert eine Route-Enhancer-Vorlage für SEO-freundliche Gastgeber-Detail-URLs.

## Ziel

Aus einer technischen Extbase-URL:

```text
/gastgeber?tx_gastgeber_list[action]=detail&tx_gastgeber_list[controller]=Host&tx_gastgeber_list[host]=2&cHash=...
```

wird direkt serverseitig:

```text
/gastgeber/hotel-acht-linden
```

## Einbindung in die Site-Konfiguration

In `config/sites/<site-identifier>/config.yaml` eintragen:

```yaml
imports:
  - { resource: "EXT:gastgeber/Configuration/Routes/Gastgeber.yaml" }
```

Alternativ den Block `GastgeberListDetail` aus `Configuration/Routes/Gastgeber.yaml` direkt unter `routeEnhancers` kopieren.

## Wichtige Begrenzung

Empfohlen ist `limitToPages` mit der UID der Seite `/gastgeber`:

```yaml
limitToPages:
  - 123
```

Dadurch wird der Platzhalter `/{host}` nur auf der Gastgeberseite ausgewertet.
Der Platzhalter `host` wird über den TYPO3 `PersistedAliasMapper` auf `tx_gastgeber_domain_model_host.slug` begrenzt.

## JavaScript-Fallback

`Resources/Public/JavaScript/gastgeber.js` kürzt Detail-URLs nur noch dann per History API, wenn wirklich Detail-Parameter vorhanden sind. Filter-, Listen- und Ansicht-URLs werden dadurch nicht mehr versehentlich als Detail-URL behandelt.

Kennung im Code:

```text
GASTGEBER_SEO_ROUTE_FINAL_2026_06_07
```
