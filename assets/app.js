
import './styles/app.css';

window.detectUserLocation = function () {
    const btn = document.getElementById('btnDetectLocation');
    const geoStatus = document.getElementById('geoStatus');
    const inputLat = document.getElementById('inputLat');
    const inputLon = document.getElementById('inputLon');
    const form = document.getElementById('stopsSearchForm');

    if (!navigator.geolocation) {
        if (geoStatus) geoStatus.textContent = '[!] Браузер не підтримує GPS';
        return;
    }

    if (geoStatus) geoStatus.textContent = 'Отримуємо координати GPS...';
    if (btn) btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        function (position) {
            if (inputLat) inputLat.value = position.coords.latitude.toFixed(6);
            if (inputLon) inputLon.value = position.coords.longitude.toFixed(6);
            if (geoStatus) geoStatus.textContent = 'Знайдено! Оновлення...';
            if (form) form.submit();
        },
        function (error) {
            if (btn) btn.disabled = false;
            if (geoStatus) geoStatus.textContent = '[!] Помилка або заборона доступу до GPS';
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
};

window.clearGeoLocation = function () {
    const inputLat = document.getElementById('inputLat');
    const inputLon = document.getElementById('inputLon');
    const form = document.getElementById('stopsSearchForm');
    if (inputLat) inputLat.value = '';
    if (inputLon) inputLon.value = '';
    if (form) form.submit();
};

window.handleRadiusChange = function () {
    const inputLat = document.getElementById('inputLat');
    const inputLon = document.getElementById('inputLon');
    const form = document.getElementById('stopsSearchForm');
    if (inputLat && inputLat.value && inputLon && inputLon.value && form) {
        form.submit();
    }
};
