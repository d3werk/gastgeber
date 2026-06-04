(function () {
  function escapeHtml(value) {
    return String(value || '').replace(/[&<>\"']/g, function (char) {
      return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;', "'": '&#039;'}[char]);
    });
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
      var bounds = [];
      if (markerScript) {
        try {
          JSON.parse(markerScript.textContent || '[]').forEach(function (marker) {
            if (!marker.lat || !marker.lng) return;
            var ll = [parseFloat(marker.lat), parseFloat(marker.lng)];
            bounds.push(ll);
            L.marker(ll).addTo(map).bindPopup('<strong>' + escapeHtml(marker.title) + '</strong><br>' + escapeHtml(marker.address || ''));
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

  document.addEventListener('DOMContentLoaded', function () {
    initMaps(document);
  });

  document.addEventListener('shown.bs.modal', function (event) {
    initMaps(event.target);
    event.target.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
      refreshMap(mapElement, 120);
    });
  });
})();
