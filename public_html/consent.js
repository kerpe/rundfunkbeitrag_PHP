/* Cookie consent + Google tag (Consent Mode v2, basic mode).
   gtag.js is only loaded after the visitor clicks "Accept"; before that nothing is sent to Google.
   The choice is kept in the first-party cookie rb-consent (granted|denied) for 6 months.
   Any element with data-rb-consent (footer "Cookie-Einstellungen") reopens the banner. */
(function () {
    var me = document.currentScript, id = me && me.getAttribute('data-gtag');
    var NAME = 'rb-consent', AGE = 15552000, loaded = false, box = null;
    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push(arguments); }
    window.gtag = gtag;
    gtag('consent', 'default', { ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied' });
    gtag('set', 'ads_data_redaction', true);

    var TEXT = {
        de: {
            title: 'Datenschutz-Einstellungen',
            body: 'Mit Ihrer Einwilligung nutzen wir den Google-Tag von Google Ads, um zu messen, ob Besucher über unsere Anzeigen zu uns kommen und einen Auftrag erteilen. Dabei werden Cookies gesetzt und Daten an Google übermittelt, auch in die USA. Ohne Einwilligung wird nichts an Google gesendet. Sie können Ihre Wahl jederzeit über „Cookie-Einstellungen“ unten auf jeder Seite ändern. ',
            more: 'Datenschutzerklärung', reject: 'Ablehnen', accept: 'Akzeptieren'
        },
        en: {
            title: 'Privacy settings',
            body: 'With your consent we use the Google Ads tag to measure whether visitors reach us through our ads and place an order. This sets cookies and sends data to Google, including to the USA. Without your consent nothing is sent to Google. You can change your choice at any time via “Cookie settings” at the bottom of every page. ',
            more: 'Privacy policy', reject: 'Reject', accept: 'Accept'
        }
    };
    var CSS = '.rb-consent{position:fixed;z-index:2147483000;left:16px;right:16px;bottom:16px;max-width:560px;margin:0 auto;padding:20px;background:#fff;color:#1e3231;border:1px solid #e5e2de;border-radius:12px;box-shadow:0 10px 40px rgba(20,38,36,.18);font:400 15px/1.5 Manrope,"Segoe UI",Helvetica,Arial,sans-serif}'
        + '.rb-consent h2{margin:0 0 8px;font-size:17px;font-weight:650;color:#163734}.rb-consent p{margin:0 0 16px}.rb-consent a{color:#297369;text-decoration:underline}'
        + '.rb-consent-btns{display:flex;gap:12px}.rb-consent-btns button{flex:1;min-height:44px;padding:10px 16px;border:2px solid #297369;border-radius:8px;background:#fff;color:#1c4d47;font:inherit;font-weight:600;cursor:pointer}'
        + '.rb-consent-btns button:hover{background:#eef9f5}.rb-consent-btns button:focus-visible{outline:3px solid #297369;outline-offset:2px}';

    function read() {
        var m = document.cookie.match(/(?:^|;\s*)rb-consent=(granted|denied)/);
        return m ? m[1] : null;
    }
    function save(v) {
        document.cookie = NAME + '=' + v + '; Max-Age=' + AGE + '; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }
    function dropGoogleCookies() {
        var host = location.hostname, domains = ['', host, '.' + host, '.' + host.replace(/^www\./, '')];
        document.cookie.split(';').forEach(function (c) {
            var n = c.split('=')[0].trim();
            if (/^(_gcl|_ga|_gac|_gid|_gat)/.test(n)) {
                domains.forEach(function (d) { document.cookie = n + '=; Max-Age=0; Path=/' + (d ? '; Domain=' + d : ''); });
            }
        });
    }
    function load() {
        if (loaded || !id) return;
        loaded = true;
        gtag('consent', 'update', { ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted' });
        gtag('js', new Date());
        gtag('config', id);
        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
        document.head.appendChild(s);
    }
    function close() {
        if (box) box.remove();
        box = null;
    }
    function choose(v) {
        var was = read();
        save(v);
        close();
        if (v === 'granted') {
            load();
        } else {
            gtag('consent', 'update', { ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
            dropGoogleCookies();
            if (loaded || was === 'granted') location.reload();
        }
    }
    function show() {
        if (box) return;
        if (!document.getElementById('rb-consent-css')) {
            var st = document.createElement('style');
            st.id = 'rb-consent-css';
            st.textContent = CSS;
            document.head.appendChild(st);
        }
        var t = TEXT[document.documentElement.lang === 'en' ? 'en' : 'de'];
        box = document.createElement('div');
        box.className = 'rb-consent';
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-labelledby', 'rb-consent-title');
        box.setAttribute('translate', 'no');
        var h = document.createElement('h2'), p = document.createElement('p'), a = document.createElement('a'), row = document.createElement('div');
        h.id = 'rb-consent-title';
        h.textContent = t.title;
        p.textContent = t.body;
        a.href = '/datenschutz';
        a.textContent = t.more;
        p.appendChild(a);
        p.appendChild(document.createTextNode('.'));
        row.className = 'rb-consent-btns';
        [['denied', t.reject], ['granted', t.accept]].forEach(function (b) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = b[1];
            btn.addEventListener('click', function () { choose(b[0]); });
            row.appendChild(btn);
        });
        box.appendChild(h);
        box.appendChild(p);
        box.appendChild(row);
        document.body.appendChild(box);
    }

    // Google Ads conversion "Order placed": fired on the "Anliegen starten" buttons (links to /flow/reason), only with consent.
    // Navigation waits for Google's callback (at most 1 s) so the hit is not lost.
    document.addEventListener('click', function (e) {
        var a = e.target && e.target.closest && e.target.closest('a[href="/flow/reason"]');
        if (!a || !id || read() !== 'granted' || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        var url = a.href, done = false;
        function go() { if (!done) { done = true; location.href = url; } }
        load();
        gtag('event', 'conversion', { send_to: id + '/0r3tCPit7qwdEK-TkIFF', value: 49.0, currency: 'EUR', transaction_id: '', event_callback: go });
        setTimeout(go, 1000);
    }, true);
    document.addEventListener('click', function (e) {
        var el = e.target && e.target.closest && e.target.closest('[data-rb-consent]');
        if (!el) return;
        e.preventDefault();
        show();
    });
    var choice = read();
    if (choice === 'granted') {
        load();
    } else if (choice === null) {
        if (document.body) show();
        else document.addEventListener('DOMContentLoaded', show);
    }
})();
