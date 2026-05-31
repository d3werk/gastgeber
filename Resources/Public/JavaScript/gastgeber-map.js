(function () {
  'use strict';

  function toFloat(value, fallback) {
    var parsed = parseFloat(String(value || '').replace(',', '.'));
    return Number.isFinite(parsed) ? parsed : fallback;
  }

  function createMessage(text, type) {
    var message = document.createElement('div');
    message.className = 'gastgeber-map__message alert alert-' + type + ' m-3';
    message.setAttribute('role', 'status');
    message.textContent = text;
    return message;
  }

  function initMap(wrapper) {
    var canvas = wrapper.querySelector('.js-gastgeber-map-canvas');
    if (!canvas) {
      return;
    }

    if (canvas.dataset.initialized === '1') {
      return;
    }
    canvas.dataset.initialized = '1';

    if (typeof window.L === 'undefined') {
      wrapper.classList.add('gastgeber-map--missing-leaflet');
      canvas.replaceWith(createMessage('Die Kartenbibliothek Leaflet wurde nicht geladen. Bitte Leaflet im Sitepackage oder über das Gastgeber-Site-Set einbinden.', 'warning'));
      return;
    }

    var defaultLat = toFloat(wrapper.dataset.defaultLat, 53.1966);
    var defaultLng = toFloat(wrapper.dataset.defaultLng, 9.9762);
    var defaultZoom = parseInt(wrapper.dataset.defaultZoom || '13', 10);
    var tileUrl = wrapper.dataset.tileUrl || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var attribution = wrapper.dataset.attribution || '&copy; OpenStreetMap contributors';
    var markers = Array.prototype.slice.call(wrapper.querySelectorAll('.js-gastgeber-map-marker'));

    var map = window.L.map(canvas, {
      scrollWheelZoom: false
    }).setView([defaultLat, defaultLng], defaultZoom);

    window.L.tileLayer(tileUrl, {
      maxZoom: 19,
      attribution: attribution
    }).addTo(map);

    var bounds = [];
    markers.forEach(function (markerElement) {
      var lat = toFloat(markerElement.dataset.lat, null);
      var lng = toFloat(markerElement.dataset.lng, null);
      if (lat === null || lng === null) {
        return;
      }

      var marker = window.L.marker([lat, lng], {
        title: markerElement.dataset.title || ''
      }).addTo(map);
      var popupHtml = markerElement.innerHTML.trim();
      if (popupHtml !== '') {
        marker.bindPopup(popupHtml);
      }
      bounds.push([lat, lng]);
    });

    if (bounds.length === 0) {
      wrapper.classList.add('gastgeber-map--empty');
      wrapper.appendChild(createMessage('Für diese Auswahl sind noch keine Kartenkoordinaten gepflegt.', 'info'));
    } else if (bounds.length === 1) {
      map.setView(bounds[0], Math.max(defaultZoom, 15));
    } else {
      map.fitBounds(bounds, { padding: [36, 36] });
    }

    setTimeout(function () {
      map.invalidateSize();
    }, 250);
  }

  function initAll() {
    Array.prototype.slice.call(document.querySelectorAll('.js-gastgeber-map')).forEach(initMap);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();
