(function () {
  // GASTGEBER_MAP_CSP_NOOP_FINAL_2026_06_08: CSP-Datei bewusst als No-op, lokale Leaflet-Assets bleiben aktiv.
  // GASTGEBER_MAP_503_CSP_SAFE_FINAL_2026_06_08: CSP-Datei entfernt; lokale Leaflet-Assets bleiben aktiv und werden robust nachgeladen.
  // GASTGEBER_MAP_JS_FORCE_FINAL_2026_06_08:
  // Diese Datei initialisiert Karten bewusst unabhängig von älteren
  // GastgeberFrontendLoaded-Guards. Wenn auf der Seite noch eine ältere
  // gastgeber.js-Version gecacht oder zusätzlich eingebunden ist, darf diese
  // Version nicht abbrechen, sondern muss die Karten sicher initialisieren.
  window.GastgeberFrontendLoaded = true;

  // GASTGEBER_SEO_ROUTE_FINAL_2026_06_07: Detail-URLs werden gezielt und reload-sicher bereinigt.
  function escapeHtml(value) {
    return String(value || '').replace(/[&<>\"']/g, function (char) {
      return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;', "'": '&#039;'}[char]);
    });
  }




  var leafletState = {
    attempted: false,
    callbacks: [],
    retryTimer: null,
    failed: false
  };

  function resolveLocalAssetUrl(relativePath) {
    var script = document.querySelector('script[data-gastgeber-extension-js]') || document.currentScript;
    var src = script && script.src ? String(script.src) : '';
    if (src.indexOf('/JavaScript/gastgeber.js') !== -1) {
      return src.replace('/JavaScript/gastgeber.js', '/' + relativePath.replace(/^\/+/, ''));
    }
    return '';
  }

  var LEAFLET_JS_URL = resolveLocalAssetUrl('Vendor/Leaflet/leaflet.min.js');
  var LEAFLET_CSS_URL = resolveLocalAssetUrl('Vendor/Leaflet/Css/Leaflet.css');

  function normalizeLeafletGlobal() {
    // maps2/Leaflet 1.9.4 kann je nach Bundle zuerst window.leaflet setzen.
    // Die Gastgeber-Logik arbeitet mit window.L. Deshalb hier absichern.
    if ((typeof window.L === 'undefined' || !window.L) && window.leaflet && typeof window.leaflet.map === 'function') {
      window.L = window.leaflet;
    }
  }

  function hasLeaflet() {
    normalizeLeafletGlobal();
    return typeof window.L !== 'undefined' && typeof window.L.map === 'function' && typeof window.L.tileLayer === 'function';
  }

  function appendLeafletCss() {
    if (document.querySelector('link[href*="leaflet"], link[data-gastgeber-leaflet-css="1"]')) {
      return;
    }

    if (!LEAFLET_CSS_URL) {
      return;
    }

    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = LEAFLET_CSS_URL;
    link.dataset.gastgeberLeafletCss = '1';
    document.head.appendChild(link);
  }

  function drainLeafletCallbacks() {
    if (!hasLeaflet()) {
      return;
    }

    leafletState.failed = false;
    if (leafletState.retryTimer) {
      window.clearTimeout(leafletState.retryTimer);
      leafletState.retryTimer = null;
    }

    var callbacks = leafletState.callbacks.splice(0);
    callbacks.forEach(function (callback) {
      try {
        callback();
      } catch (e) {
        if (window.console && window.console.warn) {
          window.console.warn('Gastgeber: Karteninitialisierung fehlgeschlagen.', e);
        }
      }
    });
  }

  function renderMapFallback(mapElement, message) {
    if (!mapElement || mapElement.dataset.initialized === '1') {
      return;
    }

    mapElement.classList.add('gastgeber-map--unavailable');
    mapElement.innerHTML = '<div class="gastgeber-map__fallback"><strong>Karte konnte nicht geladen werden.</strong><span>' + escapeHtml(message || 'Bitte prüfen, ob Leaflet geladen wird und externe Karten-Skripte nicht durch Cookie-/CSP-Einstellungen blockiert sind.') + '</span></div>';
  }

  function markMapsUnavailable(root, message) {
    var scope = root || document;
    scope.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
      renderMapFallback(mapElement, message);
    });
  }

  function pollLeaflet(root, attempt) {
    if (hasLeaflet()) {
      drainLeafletCallbacks();
      return;
    }

    if (attempt >= 80) {
      leafletState.failed = true;
      leafletState.retryTimer = null;
      leafletState.callbacks = [];
      markMapsUnavailable(root || document, 'Leaflet wurde nicht geladen. Prüfe im Browser-Netzwerk, ob leaflet.js/leaflet.css durch CSP, Cookie-Manager oder einen Adblocker blockiert wird.');
      return;
    }

    leafletState.retryTimer = window.setTimeout(function () {
      pollLeaflet(root, attempt + 1);
    }, 100);
  }

  function ensureLeaflet(root, callback) {
    if (hasLeaflet()) {
      callback();
      return;
    }

    leafletState.callbacks.push(callback);
    appendLeafletCss();

    if (!leafletState.attempted) {
      leafletState.attempted = true;

      var existingScript = document.querySelector('script[src*="leaflet"], script[data-gastgeber-leaflet-js="1"]');
      if (!existingScript) {
        if (!LEAFLET_JS_URL) {
          leafletState.failed = true;
          leafletState.callbacks = [];
          markMapsUnavailable(root || document, 'Leaflet ist nicht eingebunden. Prüfe, ob das Gastgeber-Layout die lokalen Karten-Assets ausgibt.');
          return;
        }

        var script = document.createElement('script');
        script.src = LEAFLET_JS_URL;
        script.async = true;
        script.defer = true;
        script.dataset.gastgeberLeafletJs = '1';
        script.onload = drainLeafletCallbacks;
        script.onerror = function () {
          leafletState.failed = true;
          leafletState.callbacks = [];
          markMapsUnavailable(root || document, 'leaflet.js konnte nicht geladen werden. Prüfe Cookie-Manager, CSP und externe Skripte.');
        };
        document.head.appendChild(script);
      } else if (existingScript.dataset.gastgeberLoaded === '1') {
        drainLeafletCallbacks();
      } else {
        existingScript.addEventListener('load', function () {
          existingScript.dataset.gastgeberLoaded = '1';
          drainLeafletCallbacks();
        }, {once: true});
        existingScript.addEventListener('error', function () {
          leafletState.failed = true;
          leafletState.callbacks = [];
          markMapsUnavailable(root || document, 'Das vorhandene Leaflet-Script konnte nicht geladen werden.');
        }, {once: true});
      }
    }

    if (!leafletState.retryTimer && !leafletState.failed) {
      pollLeaflet(root || document, 0);
    }
  }

  function readNumber(value, fallback) {
    var number = parseFloat(value);
    return Number.isFinite(number) ? number : fallback;
  }

  function createMarkerIcon(mapElement) {
    if (!hasLeaflet()) {
      return null;
    }

    var iconUrl = (mapElement.dataset.markerIconUrl || '').trim();
    if (!iconUrl) {
      return null;
    }

    try {
      var iconWidth = readNumber(mapElement.dataset.markerIconWidth, 38);
      var iconHeight = readNumber(mapElement.dataset.markerIconHeight, 46);
      var iconAnchorX = readNumber(mapElement.dataset.markerIconAnchorX, iconWidth / 2);
      var iconAnchorY = readNumber(mapElement.dataset.markerIconAnchorY, iconHeight);
      var popupAnchorX = readNumber(mapElement.dataset.markerPopupAnchorX, 0);
      var popupAnchorY = readNumber(mapElement.dataset.markerPopupAnchorY, -Math.round(iconHeight * 0.9));

      var iconOptions = {
        iconUrl: iconUrl,
        iconSize: [iconWidth, iconHeight],
        iconAnchor: [iconAnchorX, iconAnchorY],
        popupAnchor: [popupAnchorX, popupAnchorY],
        className: 'gastgeber-map-marker-icon'
      };

      if ((mapElement.dataset.markerIconRetinaUrl || '').trim()) {
        iconOptions.iconRetinaUrl = mapElement.dataset.markerIconRetinaUrl.trim();
      }

      if ((mapElement.dataset.markerShadowUrl || '').trim()) {
        iconOptions.shadowUrl = mapElement.dataset.markerShadowUrl.trim();
        iconOptions.shadowSize = [
          readNumber(mapElement.dataset.markerShadowWidth, iconWidth),
          readNumber(mapElement.dataset.markerShadowHeight, iconHeight)
        ];
        iconOptions.shadowAnchor = [
          readNumber(mapElement.dataset.markerShadowAnchorX, iconAnchorX),
          readNumber(mapElement.dataset.markerShadowAnchorY, iconAnchorY)
        ];
      }

      return L.icon(iconOptions);
    } catch (e) {
      if (window.console && window.console.warn) {
        window.console.warn('Gastgeber: Marker-Icon konnte nicht erstellt werden. Standardmarker wird verwendet.', e);
      }
      return null;
    }
  }

  function refreshMap(mapElement, delay) {
    if (!mapElement || !mapElement._gastgeberMap) {
      return;
    }
    window.setTimeout(function () {
      try {
        mapElement._gastgeberMap.invalidateSize();
        if (mapElement._gastgeberBounds && mapElement._gastgeberBounds.length > 1) {
          mapElement._gastgeberMap.fitBounds(mapElement._gastgeberBounds, {padding: [24, 24]});
        } else if (mapElement._gastgeberBounds && mapElement._gastgeberBounds.length === 1) {
          mapElement._gastgeberMap.setView(mapElement._gastgeberBounds[0], parseInt(mapElement.dataset.zoom || '13', 10));
        }
      } catch (e) {}
    }, delay || 150);
  }

  function parseMarkers(mapElement) {
    var markerScript = mapElement.parentElement ? mapElement.parentElement.querySelector('.gastgeber-map-markers') : null;
    if (!markerScript) {
      return [];
    }

    try {
      var markers = JSON.parse(markerScript.textContent || '[]');
      return Array.isArray(markers) ? markers : [];
    } catch (e) {
      if (window.console && window.console.warn) {
        window.console.warn('Gastgeber: Marker-JSON konnte nicht gelesen werden.', e);
      }
      return [];
    }
  }

  function isMapCurrentlyRenderable(mapElement) {
    // Karten in Bootstrap-Modals sind beim ersten Seitenladen display:none.
    // Leaflet darf dann noch nicht initialisiert werden, sonst entsteht eine
    // leere/0px Karte. Initialisierung erfolgt beim shown.bs.modal erneut.
    if (!mapElement) {
      return false;
    }
    var rect = mapElement.getBoundingClientRect ? mapElement.getBoundingClientRect() : null;
    var width = rect ? rect.width : mapElement.offsetWidth;
    var height = rect ? rect.height : mapElement.offsetHeight;
    return width > 20 && height > 20;
  }

  function initSingleMap(mapElement) {
    if (!mapElement || mapElement.dataset.initialized === '1') {
      if (mapElement && mapElement._gastgeberMap) {
        refreshMap(mapElement, 80);
      }
      return;
    }

    if (!isMapCurrentlyRenderable(mapElement)) {
      mapElement.dataset.initialized = 'pending';
      return;
    }

    var lat = readNumber(mapElement.dataset.lat, 53.1966);
    var lng = readNumber(mapElement.dataset.lng, 9.9762);
    var zoom = parseInt(mapElement.dataset.zoom || '13', 10);
    if (!Number.isFinite(zoom)) {
      zoom = 13;
    }

    try {
      mapElement.classList.remove('gastgeber-map--unavailable');
      mapElement.innerHTML = '';

      var map = L.map(mapElement).setView([lat, lng], zoom);
      mapElement._gastgeberMap = map;

      L.tileLayer(mapElement.dataset.tileUrl || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: mapElement.dataset.attribution || '© OpenStreetMap contributors'
      }).addTo(map);

      var customMarkerIcon = createMarkerIcon(mapElement);
      var bounds = [];
      parseMarkers(mapElement).forEach(function (marker) {
        if (marker.lat === null || marker.lng === null || marker.lat === '' || marker.lng === '') return;
        var markerLat = parseFloat(marker.lat);
        var markerLng = parseFloat(marker.lng);
        if (!Number.isFinite(markerLat) || !Number.isFinite(markerLng)) return;
        var ll = [markerLat, markerLng];
        bounds.push(ll);
        L.marker(ll, customMarkerIcon ? {icon: customMarkerIcon} : {}).addTo(map).bindPopup('<strong>' + escapeHtml(marker.title) + '</strong><br>' + escapeHtml(marker.address || ''));
      });

      mapElement._gastgeberBounds = bounds;
      mapElement.dataset.initialized = '1';

      if (bounds.length > 1) {
        map.fitBounds(bounds, {padding: [24, 24]});
      } else if (bounds.length === 1) {
        map.setView(bounds[0], zoom);
      }

      refreshMap(mapElement, 120);
      refreshMap(mapElement, 450);
    } catch (e) {
      mapElement.dataset.initialized = '0';
      if (window.console && window.console.error) {
        window.console.error('Gastgeber: Karte konnte nicht initialisiert werden.', e);
      }
      renderMapFallback(mapElement, 'Die Karte konnte im Browser nicht initialisiert werden. Prüfe die JavaScript-Konsole auf Leaflet- oder CSP-Fehler.');
    }
  }

  function initMaps(root) {
    var scope = root || document;
    var mapElements = scope.querySelectorAll('[data-gastgeber-map]');
    if (!mapElements.length) {
      return;
    }

    // GASTGEBER_MAP_DEPENDENCY_FINAL_2026_06_07:
    // Karten hängen von Leaflet, Bootstrap-Modal-Events, TYPO3-Asset-Reihenfolge und
    // Cookie-/CSP-Regeln ab. Deshalb wird Leaflet mehrfach geprüft, bei Bedarf nachgeladen
    // und die Karte nach Modal-Öffnung sowie Window-Load erneut aktualisiert.
    if (!hasLeaflet()) {
      ensureLeaflet(scope, function () {
        initMaps(scope);
      });
      return;
    }

    mapElements.forEach(initSingleMap);
  }

  function setListView(directory, viewMode) {
    if (!directory || (viewMode !== 'cards' && viewMode !== 'list')) {
      return;
    }

    var isList = viewMode === 'list';
    var results = directory.querySelector('[data-gastgeber-results]');
    if (!results) {
      return;
    }

    results.classList.toggle('gastgeber-results--list', isList);
    results.classList.toggle('row', !isList);
    results.classList.toggle('g-4', !isList);

    results.querySelectorAll('[data-gastgeber-result-item]').forEach(function (item) {
      item.className = isList ? 'gastgeber-list-row' : 'col-12 col-md-6 col-xl-4';
    });

    results.querySelectorAll('[data-gastgeber-card]').forEach(function (card) {
      card.classList.toggle('gastgeber-card--list', isList);
    });

    directory.querySelectorAll('[data-gastgeber-view-toggle]').forEach(function (toggle) {
      var active = toggle.dataset.gastgeberViewToggle === viewMode;
      toggle.classList.toggle('is-active', active);
      toggle.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    directory.dataset.gastgeberViewMode = viewMode;
  }

  function initViewSwitches(root) {
    var scope = root || document;
    scope.querySelectorAll('[data-gastgeber-view-toggle]').forEach(function (toggle) {
      if (toggle.dataset.gastgeberSwitchInitialized === '1') {
        return;
      }
      toggle.dataset.gastgeberSwitchInitialized = '1';
      toggle.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          return;
        }
        var viewMode = toggle.dataset.gastgeberViewToggle;
        if (viewMode !== 'cards' && viewMode !== 'list') {
          return;
        }
        var directory = toggle.closest('[data-gastgeber-directory]') || toggle.closest('.gastgeber-directory');
        if (!directory || !directory.querySelector('[data-gastgeber-results]')) {
          return;
        }
        event.preventDefault();
        setListView(directory, viewMode);
        if (toggle.href && window.history && window.history.pushState) {
          window.history.pushState({gastgeberViewMode: viewMode}, '', cleanHrefForPushState(toggle.href));
        }
      });
    });
  }

  function hasGastgeberQueryParameters(url) {
    var found = false;
    url.searchParams.forEach(function (value, key) {
      if (key.indexOf('tx_gastgeber_') === 0) {
        found = true;
      }
    });
    return found;
  }

  function getGastgeberParam(url, plugin, name) {
    return url.searchParams.get('tx_gastgeber_' + plugin + '[' + name + ']') || '';
  }

  function getGastgeberListParam(url, name) {
    return getGastgeberParam(url, 'list', name);
  }

  function hasGastgeberDetailArguments(url) {
    var listAction = (getGastgeberParam(url, 'list', 'action') || '').trim();
    var listHost = (getGastgeberParam(url, 'list', 'host') || '').trim();
    var detailHost = (getGastgeberParam(url, 'detail', 'host') || '').trim();

    return (listAction === 'detail' && listHost !== '') || detailHost !== '';
  }

  function buildGastgeberDetailPath(currentPath, slug) {
    var path = String(currentPath || '').replace(/\/+$/, '');
    var cleanSlug = String(slug || '').trim().replace(/^\/+|\/+$/g, '');

    if (path === '') {
      path = '/gastgeber';
    }
    if (cleanSlug === '') {
      return path;
    }
    if (path.slice(-1 * (cleanSlug.length + 1)) === '/' + cleanSlug) {
      return path;
    }

    return path + '/' + encodeURIComponent(cleanSlug);
  }

  function hasActiveListArguments(url) {
    if ((getGastgeberListParam(url, 'search') || '').trim() !== '') {
      return true;
    }
    if ((getGastgeberListParam(url, 'sort') || '').trim() !== '') {
      return true;
    }

    var view = (getGastgeberListParam(url, 'view') || '').trim();
    if (view !== '' && view !== 'cards') {
      return true;
    }

    var hasActiveFilter = false;
    url.searchParams.forEach(function (value, key) {
      if (value === '') {
        return;
      }
      if (key.indexOf('tx_gastgeber_list[types]') === 0 ||
          key.indexOf('tx_gastgeber_list[features]') === 0 ||
          key.indexOf('tx_gastgeber_list[districts]') === 0) {
        hasActiveFilter = true;
      }
    });

    return hasActiveFilter;
  }

  function replaceBrowserUrl(cleanUrl) {
    if (!cleanUrl || !window.history || !window.history.replaceState) {
      return;
    }
    if (cleanUrl === window.location.href) {
      return;
    }
    window.history.replaceState(window.history.state || {}, document.title, cleanUrl);
  }

  function cleanupGastgeberBrowserUrls(root) {
    if (!window.URL) {
      return;
    }

    var currentUrl;
    try {
      currentUrl = new URL(window.location.href);
    } catch (e) {
      return;
    }

    var detailElement = (root || document).querySelector('[data-gastgeber-detail-slug]');
    if (detailElement) {
      var slug = (detailElement.dataset.gastgeberDetailSlug || '').trim();

      // GASTGEBER_SEO_ROUTE_FINAL_2026_06_07:
      // Nur echte Detail-URLs werden auf /gastgeber/{slug} gekürzt.
      // Listen-, Filter- und Ansichtsschalter-URLs bleiben unangetastet.
      // Wenn der TYPO3 Route Enhancer aktiv ist, liefert der Server die saubere
      // URL ohnehin direkt aus; diese Logik ist nur ein Fallback für alte Links.
      if (slug !== '' && hasGastgeberQueryParameters(currentUrl) && hasGastgeberDetailArguments(currentUrl)) {
        replaceBrowserUrl(currentUrl.origin + buildGastgeberDetailPath(currentUrl.pathname, slug) + currentUrl.hash);
      }

      return;
    }

    var directory = (root || document).querySelector('[data-gastgeber-directory]');
    if (directory && hasGastgeberQueryParameters(currentUrl) && !hasGastgeberDetailArguments(currentUrl) && !hasActiveListArguments(currentUrl)) {
      replaceBrowserUrl(currentUrl.origin + currentUrl.pathname + currentUrl.hash);
    }
  }

  function cleanHrefForPushState(href) {
    if (!href || !window.URL) {
      return href;
    }

    try {
      var url = new URL(href, window.location.href);
      if (hasGastgeberQueryParameters(url) && !hasGastgeberDetailArguments(url) && !hasActiveListArguments(url)) {
        return url.origin + url.pathname + url.hash;
      }
      return url.href;
    } catch (e) {
      return href;
    }
  }

  function initBackButtons(root) {
    var scope = root || document;
    scope.querySelectorAll('[data-gastgeber-back-button]').forEach(function (button) {
      if (button.dataset.gastgeberBackInitialized === '1') {
        return;
      }
      button.dataset.gastgeberBackInitialized = '1';
      button.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          return;
        }

        var referrer = document.referrer || '';
        var hasSameOriginReferrer = false;
        var referrerHasGastgeberQuery = false;
        try {
          var referrerUrl = new URL(referrer);
          hasSameOriginReferrer = referrer !== '' && referrerUrl.origin === window.location.origin;
          referrerHasGastgeberQuery = hasGastgeberQueryParameters(referrerUrl);
        } catch (e) {}

        // Wenn die vorherige Seite eine alte Extbase-URL war, nicht per Browser-History
        // zurückspringen, weil sonst wieder ?tx_gastgeber_list[...] in der Adresszeile steht.
        // In diesem Fall wird der saubere href des Buttons verwendet.
        if (window.history && window.history.length > 1 && hasSameOriginReferrer && !referrerHasGastgeberQuery) {
          event.preventDefault();
          window.history.back();
          return;
        }

        if (button.tagName.toLowerCase() !== 'a') {
          event.preventDefault();
        }
      });
    });
  }

  function initFrontend(root) {
    initViewSwitches(root || document);
    initBackButtons(root || document);
    initMaps(root || document);
    window.setTimeout(function () { initMaps(root || document); }, 80);
    window.setTimeout(function () { initMaps(root || document); }, 300);
    window.setTimeout(function () { initMaps(root || document); }, 1000);
    cleanupGastgeberBrowserUrls(root || document);
  }

  document.addEventListener('DOMContentLoaded', function () {
    initFrontend(document);
  });

  if (document.readyState === 'interactive' || document.readyState === 'complete') {
    initFrontend(document);
  }

  window.addEventListener('load', function () {
    initMaps(document);
    window.setTimeout(function () { initMaps(document); }, 400);
    window.setTimeout(function () { initMaps(document); }, 1200);
  });

  window.addEventListener('pageshow', function () {
    initMaps(document);
    window.setTimeout(function () { initMaps(document); }, 300);
  });

  window.addEventListener('popstate', function (event) {
    if (!event.state || !event.state.gastgeberViewMode) {
      return;
    }
    document.querySelectorAll('[data-gastgeber-directory]').forEach(function (directory) {
      setListView(directory, event.state.gastgeberViewMode);
    });
    cleanupGastgeberBrowserUrls(document);
  });

  document.addEventListener('show.bs.modal', function (event) {
    window.setTimeout(function () { initMaps(event.target); }, 60);
  });

  document.addEventListener('shown.bs.modal', function (event) {
    event.target.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
      if (mapElement.dataset.initialized === 'pending') {
        mapElement.dataset.initialized = '0';
      }
    });
    initMaps(event.target);
    event.target.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
      refreshMap(mapElement, 120);
      refreshMap(mapElement, 500);
      refreshMap(mapElement, 1000);
    });
  });

  document.addEventListener('click', function (event) {
    var trigger = event.target && event.target.closest ? event.target.closest('[data-bs-target*="gastgeber-map-modal"], [data-gastgeber-open-map]') : null;
    if (!trigger) {
      return;
    }
    window.setTimeout(function () { initMaps(document); }, 200);
  });

  if ('ResizeObserver' in window) {
    var mapResizeObserver = new ResizeObserver(function (entries) {
      entries.forEach(function (entry) {
        var mapElement = entry.target;
        if (mapElement.dataset.initialized === 'pending' && isMapCurrentlyRenderable(mapElement)) {
          mapElement.dataset.initialized = '0';
          initMaps(document);
        }
        refreshMap(mapElement, 80);
      });
    });
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
        try { mapResizeObserver.observe(mapElement); } catch (e) {}
      });
    });
  }

  if ('IntersectionObserver' in window) {
    var mapIntersectionObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          if (entry.target.dataset.initialized === 'pending') {
            entry.target.dataset.initialized = '0';
          }
          initMaps(document);
          refreshMap(entry.target, 100);
        }
      });
    }, {threshold: 0.01});
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
        try { mapIntersectionObserver.observe(mapElement); } catch (e) {}
      });
    });
  }

  window.GastgeberMaps = window.GastgeberMaps || {};
  window.GastgeberMaps.init = initMaps;
  window.GastgeberMaps.refresh = function () {
    document.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
      if (mapElement.dataset.initialized === 'pending' && isMapCurrentlyRenderable(mapElement)) {
        mapElement.dataset.initialized = '0';
        initSingleMap(mapElement);
      }
      refreshMap(mapElement, 80);
    });
  };
  window.GastgeberMaps.debug = function () {
    return Array.prototype.slice.call(document.querySelectorAll('[data-gastgeber-map]')).map(function (mapElement) {
      var rect = mapElement.getBoundingClientRect ? mapElement.getBoundingClientRect() : {width: mapElement.offsetWidth, height: mapElement.offsetHeight};
      return {
        hasLeaflet: hasLeaflet(),
        initialized: mapElement.dataset.initialized || '',
        width: Math.round(rect.width || 0),
        height: Math.round(rect.height || 0),
        markerCount: parseMarkers(mapElement).length,
        hasMapObject: !!mapElement._gastgeberMap,
        className: mapElement.className
      };
    });
  };
})();
