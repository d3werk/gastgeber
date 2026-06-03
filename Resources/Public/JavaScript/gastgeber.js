(function () {
  function escapeHtml(value) {
    return String(value || '').replace(/[&<>\"']/g, function (char) {
      return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;', "'": '&#039;'}[char]);
    });
  }

  function initMaps() {
    if (typeof L === 'undefined') {
      return;
    }
    document.querySelectorAll('[data-gastgeber-map]').forEach(function (mapElement) {
      if (mapElement.dataset.initialized === '1') {
        return;
      }
      mapElement.dataset.initialized = '1';
      var lat = parseFloat(mapElement.dataset.lat || '53.1966');
      var lng = parseFloat(mapElement.dataset.lng || '9.9762');
      var zoom = parseInt(mapElement.dataset.zoom || '13', 10);
      var map = L.map(mapElement).setView([lat, lng], zoom);
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
      if (bounds.length > 1) {
        map.fitBounds(bounds, {padding: [24, 24]});
      }
      setTimeout(function () { map.invalidateSize(); }, 250);
    });
  }
  document.addEventListener('DOMContentLoaded', initMaps);
  document.addEventListener('shown.bs.modal', initMaps);
})();
