document.addEventListener('DOMContentLoaded', function () {
    const banner = document.getElementById('cookie-consent-banner');
    const acceptBtn = document.getElementById('cookie-accept-all');
    const rejectBtn = document.getElementById('cookie-reject-all');

    if (!banner) return;



    if (acceptBtn) {
        acceptBtn.addEventListener('click', function () {
            setCookieConsent('accepted');
        });
    }

    if (rejectBtn) {
        rejectBtn.addEventListener('click', function () {
            setCookieConsent('refused');
        });
    }
});
