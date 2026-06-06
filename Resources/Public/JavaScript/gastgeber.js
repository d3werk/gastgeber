(function () {
  function escapeHtml(value) {
    return String(value || '').replace(/[&<>\"']/g, function (char) {
      return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;', "'": '&#039;'}[char]);
    });
  }



  function readNumber(value, fallback) {
    var number = parseFloat(value);
    return Number.isFinite(number) ? number : fallback;
  }

  function createMarkerIcon(mapElement) {
    var iconUrl = (mapElement.dataset.markerIconUrl || '').trim();
    if (!iconUrl) {
      return null;
    }

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
  }

  function refreshMap(mapElement, delay) {
    if (!mapElement || !mapElement._gastgeberMap) {
      return;
    }
    window.setTimeout(function () {
      mapElement._gastgeberMap.invalidateSize();
      if (mapElement._gastgeberBounds && mapElement._gastgeberBounds.length > 1) {
        mapElement._gastgeberMap.fitBounds(mapElement._gastgeberBounds, {padding: [24, 24]});
      } else if (mapElement._gastgeberBounds && mapElement._gastgeberBounds.length === 1) {
        mapElement._gastgeberMap.setView(mapElement._gastgeberBounds[0], parseInt(mapElement.dataset.zoom || '13', 10));
      }
    }, delay || 150);
  }

  function initMaps(root) {
    if (typeof L === 'undefined') {
      return;
    }
    var scope = root || document;
    scope.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
      if (mapElement.dataset.initialized === '1' && mapElement._gastgeberMap) {
        refreshMap(mapElement, 80);
        return;
      }
      mapElement.dataset.initialized = '1';
      var lat = parseFloat(mapElement.dataset.lat || '53.1966');
      var lng = parseFloat(mapElement.dataset.lng || '9.9762');
      var zoom = parseInt(mapElement.dataset.zoom || '13', 10);
      var map = L.map(mapElement).setView([lat, lng], zoom);
      mapElement._gastgeberMap = map;

      L.tileLayer(mapElement.dataset.tileUrl || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: mapElement.dataset.attribution || '© OpenStreetMap contributors'
      }).addTo(map);

      var markerScript = mapElement.parentElement.querySelector('.gastgeber-map-markers');
      var customMarkerIcon = createMarkerIcon(mapElement);
      var bounds = [];
      if (markerScript) {
        try {
          JSON.parse(markerScript.textContent || '[]').forEach(function (marker) {
            if (marker.lat === null || marker.lng === null || marker.lat === '' || marker.lng === '') return;
            var markerLat = parseFloat(marker.lat);
            var markerLng = parseFloat(marker.lng);
            if (!Number.isFinite(markerLat) || !Number.isFinite(markerLng)) return;
            var ll = [markerLat, markerLng];
            bounds.push(ll);
            L.marker(ll, customMarkerIcon ? {icon: customMarkerIcon} : {}).addTo(map).bindPopup('<strong>' + escapeHtml(marker.title) + '</strong><br>' + escapeHtml(marker.address || ''));
          });
        } catch (e) {}
      }
      mapElement._gastgeberBounds = bounds;
      if (bounds.length > 1) {
        map.fitBounds(bounds, {padding: [24, 24]});
      } else if (bounds.length === 1) {
        map.setView(bounds[0], zoom);
      }
      refreshMap(mapElement, 250);
    });
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

  function getGastgeberListParam(url, name) {
    return url.searchParams.get('tx_gastgeber_list[' + name + ']') || '';
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
      if (slug !== '' && hasGastgeberQueryParameters(currentUrl)) {
        var path = currentUrl.pathname.replace(/\/+$/, '');
        if (path === '') {
          path = '/';
        }
        if (path.slice(-1 * (slug.length + 1)) !== '/' + slug) {
          path = path.replace(/\/+$/, '') + '/' + encodeURIComponent(slug);
        }
        replaceBrowserUrl(currentUrl.origin + path + currentUrl.hash);
      }
      return;
    }

    var directory = (root || document).querySelector('[data-gastgeber-directory]');
    if (directory && hasGastgeberQueryParameters(currentUrl) && !hasActiveListArguments(currentUrl)) {
      replaceBrowserUrl(currentUrl.origin + currentUrl.pathname + currentUrl.hash);
    }
  }

  function cleanHrefForPushState(href) {
    if (!href || !window.URL) {
      return href;
    }

    try {
      var url = new URL(href, window.location.href);
      if (hasGastgeberQueryParameters(url) && !hasActiveListArguments(url)) {
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

  document.addEventListener('DOMContentLoaded', function () {
    initViewSwitches(document);
    initBackButtons(document);
    initMaps(document);
    cleanupGastgeberBrowserUrls(document);
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

  document.addEventListener('shown.bs.modal', function (event) {
    initMaps(event.target);
    event.target.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
      refreshMap(mapElement, 120);
    });
  });
})();
