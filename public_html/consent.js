/* Cookie consent + Google tag (Consent Mode v2, basic mode) + Microsoft Clarity (data-clarity).
   gtag.js is only loaded after the visitor clicks "Accept"; before that nothing is sent to Google.
   The choice is kept in the first-party cookie rb-consent (granted[-ga][-cl]|denied) for 6 months.
   The cookie value names the tools accepted (granted, granted-ga, granted-ga-cl); if more tools are active now, the banner asks again.
   Any element with data-rb-consent (footer "Cookie-Einstellungen") reopens the banner. */
(function () {
    var me = document.currentScript, id = me && me.getAttribute('data-gtag'), ga = me && me.getAttribute('data-ga'), cl = me && me.getAttribute('data-clarity');
    // Consent covers the tools named in the banner at that time: "granted" = Google Ads, "-ga" = + Analytics, "-cl" = + Clarity.
    var NEED = (ga ? ['ga'] : []).concat(cl ? ['cl'] : []);
    var NAME = 'rb-consent', AGE = 15552000, loaded = false, box = null;
    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push(arguments); }
    window.gtag = gtag;
    gtag('consent', 'default', { ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied' });
    gtag('set', 'ads_data_redaction', true);

    // Banner text lists only the tools that are switched on.
    var USE = {
        de: [ga && 'Google Analytics, um zu verstehen, wie unsere Website genutzt wird', cl && 'Microsoft Clarity, um anhand aufgezeichneter Klicks, Scroll- und Mausbewegungen zu erkennen, wo unsere Seiten verbessert werden können (Eingaben in Formularfelder werden nicht aufgezeichnet)', 'den Google-Tag von Google Ads, um zu messen, ob Besucher über unsere Anzeigen zu uns kommen und einen Auftrag erteilen'],
        en: [ga && 'Google Analytics to understand how our website is used', cl && 'Microsoft Clarity to see from recorded clicks, scrolling and mouse movements where our pages can be improved (what you type into form fields is not recorded)', 'the Google Ads tag to measure whether visitors reach us through our ads and place an order']
    };
    function list(a, and) {
        a = a.filter(Boolean);
        return a.length > 1 ? a.slice(0, -1).join(', ') + and + a[a.length - 1] : a[0];
    }
    var TEXT = {
        de: {
            title: 'Datenschutz-Einstellungen',
            body: 'Mit Ihrer Einwilligung nutzen wir ' + list(USE.de, ', und ') + '. Dabei werden Cookies gesetzt und Daten an ' + (cl ? 'Google und Microsoft' : 'Google') + ' übermittelt, auch in die USA. Ohne Einwilligung wird nichts an ' + (cl ? 'Google oder Microsoft' : 'Google') + ' gesendet. Sie können Ihre Wahl jederzeit über „Cookie-Einstellungen“ unten auf jeder Seite ändern. ',
            more: 'Datenschutzerklärung', reject: 'Ablehnen', accept: 'Akzeptieren',
            short: 'Nur mit Ihrer Einwilligung nutzen wir Cookies von ' + (cl ? 'Google und Microsoft' : 'Google') + ', um die Nutzung der Website und den Erfolg unserer Anzeigen zu messen.', details: 'Details'
        },
        en: {
            title: 'Privacy settings',
            body: 'With your consent we use ' + list(USE.en, ', and ') + '. This sets cookies and sends data to ' + (cl ? 'Google and Microsoft' : 'Google') + ', including to the USA. Without your consent nothing is sent to ' + (cl ? 'Google or Microsoft' : 'Google') + '. You can change your choice at any time via “Cookie settings” at the bottom of every page. ',
            more: 'Privacy policy', reject: 'Reject', accept: 'Accept',
            short: 'Only with your consent, we use cookies from ' + (cl ? 'Google and Microsoft' : 'Google') + ' to measure how the website is used and how our ads perform.', details: 'Details'
        }
    };
    var CSS = '.rb-consent{position:fixed;z-index:2147483000;left:16px;right:16px;bottom:16px;max-width:560px;margin:0 auto;padding:20px;background:#fff;color:#1e3231;border:1px solid #e5e2de;border-radius:12px;box-shadow:0 10px 40px rgba(20,38,36,.18);font:400 15px/1.5 Manrope,"Segoe UI",Helvetica,Arial,sans-serif}'
        + '.rb-consent h2{margin:0 0 8px;font-size:17px;font-weight:650;color:#163734}.rb-consent p{margin:0 0 16px}.rb-consent a{color:#297369;text-decoration:underline}'
        + '.rb-consent-btns{display:flex;gap:12px}.rb-consent-btns button{flex:1;min-height:44px;padding:10px 16px;border:2px solid #297369;border-radius:8px;background:#fff;color:#1c4d47;font:inherit;font-weight:600;cursor:pointer}'
        + '.rb-consent-btns button:hover{background:#eef9f5}.rb-consent-btns button:focus-visible{outline:3px solid #297369;outline-offset:2px}'
        // Phones: a small bar at the bottom (short text + "Details"); the full text opens on request. Both choices stay equally prominent.
        + '.rb-consent-short{display:none}.rb-consent-more{padding:0;border:0;background:none;color:#297369;font:inherit;font-weight:600;text-decoration:underline;cursor:pointer}'
        + '@media (max-width:640px){.rb-consent{left:8px;right:8px;bottom:8px;padding:12px 14px;font-size:13.5px;line-height:1.4;max-height:calc(100vh - 16px);overflow:auto}.rb-consent h2{margin:0 0 4px;font-size:15px}.rb-consent p{margin:0 0 10px}'
        + '.rb-consent .rb-consent-short{display:block}.rb-consent .rb-consent-full{display:none}.rb-consent.is-open .rb-consent-full{display:block}.rb-consent.is-open .rb-consent-short .rb-consent-more{display:none}.rb-consent-btns{gap:8px}'
        // Phones: a thin bar flush with the bottom edge, so it does not cover the page content; the heading shows only with the full text.
        + '.rb-consent{left:0;right:0;bottom:0;max-width:none;padding:8px 12px calc(8px + env(safe-area-inset-bottom));border-width:1px 0 0;border-radius:12px 12px 0 0;font-size:12.5px;line-height:1.35;box-shadow:0 -4px 20px rgba(20,38,36,.12)}'
        + '.rb-consent h2{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}.rb-consent.is-open h2{position:static;width:auto;height:auto;clip:auto;white-space:normal}'
        + '.rb-consent p{margin:0 0 6px}.rb-consent-btns button{min-height:40px;padding:6px 12px}}';

    function read() {
        var m = document.cookie.match(/(?:^|;\s*)rb-consent=(granted(?:-[a-z]+)*|denied)/);
        if (!m) return null;
        if (m[1] === 'denied') return 'denied';
        // A visitor who accepted fewer tools than are now active is asked again.
        var has = m[1].split('-').slice(1);
        return NEED.every(function (k) { return has.indexOf(k) >= 0; }) ? 'granted' : null;
    }
    function save(v) {
        document.cookie = NAME + '=' + (v === 'granted' ? ['granted'].concat(NEED).join('-') : v) + '; Max-Age=' + AGE + '; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    }
    function dropGoogleCookies() {
        var host = location.hostname, domains = ['', host, '.' + host, '.' + host.replace(/^www\./, '')];
        document.cookie.split(';').forEach(function (c) {
            var n = c.split('=')[0].trim();
            if (/^(_gcl|_ga|_gac|_gid|_gat|_clck|_clsk)/.test(n)) {
                domains.forEach(function (d) { document.cookie = n + '=; Max-Age=0; Path=/' + (d ? '; Domain=' + d : ''); });
            }
        });
    }
    function load() {
        if (loaded) return;
        loaded = true;
        if (cl) clarity();
        if (!(id || ga)) return;
        gtag('consent', 'update', { ad_storage: 'granted', ad_user_data: 'granted', ad_personalization: 'granted', analytics_storage: 'granted' });
        gtag('js', new Date());
        if (id) gtag('config', id);
        // Google Analytics 4: page views and the built-in enhanced measurement; IP addresses are never stored by GA4.
        if (ga) gtag('config', ga);
        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id || ga);
        document.head.appendChild(s);
        if (id) purchases();
    }
    // Microsoft Clarity: heatmaps and session recordings. Loaded only after consent, so the consent signal is "granted" for
    // analytics; nothing is shared for advertising. Order form and case pages carry data-clarity-mask (all text masked).
    // Public pages carry data-clarity-unmask so prices and durations are readable (Clarity's Balanced mode hides numbers);
    // every form field there (contact form, search) gets data-clarity-mask before Clarity sees it, so typed text stays hidden.
    // This observer is created before Clarity's own, so its callback runs first for each new node.
    var FIELDS = 'input,textarea,select,[contenteditable]';
    function maskFields(root) {
        if (!root.querySelectorAll) return;
        if (root.matches && root.matches(FIELDS)) root.setAttribute('data-clarity-mask', 'True');
        root.querySelectorAll(FIELDS).forEach(function (el) { el.setAttribute('data-clarity-mask', 'True'); });
    }
    function clarity() {
        maskFields(document);
        if (window.MutationObserver) {
            new MutationObserver(function (list) {
                list.forEach(function (m) { m.addedNodes.forEach(maskFields); });
            }).observe(document.documentElement, { childList: true, subtree: true });
        }
        var c = window.clarity = window.clarity || function () { (c.q = c.q || []).push(arguments); };
        c('consentv2', { ad_Storage: 'denied', analytics_Storage: 'granted' });
        var s = document.createElement('script');
        s.async = true;
        s.src = 'https://www.clarity.ms/tag/' + encodeURIComponent(cl);
        document.head.appendChild(s);
    }
    // Google Ads conversion "Purchase": orders staff marked as paid are reported once from the customer's own browser
    // (staff mark payment in their browser, which has no ad click). Case number = transaction_id, so Google drops duplicates;
    // an order stays listed until Google's callback has fired, so a lost hit is retried on the next visit.
    function purchases() {
        if (!window.fetch) return;
        fetch('/api/journey/ads-conversions', { credentials: 'same-origin' }).then(function (r) { return r.ok ? r.json() : null; }).then(function (d) {
            ((d && d.conversions) || []).forEach(function (c) {
                if (ga) gtag('event', 'purchase', { send_to: ga, transaction_id: c.reference, value: c.value, currency: 'EUR' });
                gtag('event', 'conversion', { send_to: id + '/z9lQCLSt9KwdEK-TkIFF', value: c.value, currency: 'EUR', transaction_id: c.reference, event_callback: function () {
                    fetch('/api/journey/ads-conversions', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (window.PHPBOOT || {}).csrf || '' }, body: JSON.stringify({ id: c.id }) });
                } });
            });
        }).catch(function () {});
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
            gtag('consent', 'update', { ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'denied' });
            if (window.clarity) window.clarity('consentv2', { ad_Storage: 'denied', analytics_Storage: 'denied' });
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
        var sp = document.createElement('p'), more = document.createElement('button');
        h.id = 'rb-consent-title';
        h.textContent = t.title;
        sp.className = 'rb-consent-short';
        sp.textContent = t.short + ' ';
        more.type = 'button';
        more.className = 'rb-consent-more';
        more.textContent = t.details;
        more.setAttribute('aria-expanded', 'false');
        more.addEventListener('click', function () { box.classList.add('is-open'); more.setAttribute('aria-expanded', 'true'); });
        sp.appendChild(more);
        p.className = 'rb-consent-full';
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
        box.appendChild(sp);
        box.appendChild(p);
        box.appendChild(row);
        document.body.appendChild(box);
    }

    // Order form funnel, only with consent. Google Analytics gets one event per form step the visitor reaches
    // (begin_checkout when the form is first shown, then form_details, form_documents, form_review) and order_placed when the order is recorded;
    // The e-mail address is confirmed after the order (link in the e-mail), so there is no e-mail step in the funnel;
    // Google Ads gets its "Order placed" conversion (ORDER_LABEL) at the same moment; form starts are counted by Google's own "Begin checkout". No form contents are sent.
    var ORDER_LABEL = '0r3tCPit7qwdEK-TkIFF', STEPS = ['begin_checkout', 'form_details', 'form_documents', 'form_review'], seen = {}, timer = 0;
    function service() {
        var m = location.pathname.match(/^\/auftrag\/(p-0[1-8])$/i);
        return m ? m[1].toUpperCase() : '';
    }
    function step() {
        timer = 0;
        if (!ga || read() !== 'granted') return;
        // Sidebar of the order form: one li per step, the current one has aria-current="step".
        var li = document.querySelector('.mj-sidebar li[aria-current="step"]');
        if (!li) return;
        var name = STEPS[Array.prototype.indexOf.call(li.parentNode.children, li)];
        load();
        // A service page opens the form at "Ihre Angaben", so the form start is counted at whichever step is shown first.
        if (!seen.begin_checkout) {
            seen.begin_checkout = true;
            gtag('event', 'begin_checkout', { send_to: ga, service: service(), value: 35.0, currency: 'EUR' });
        }
        if (!name || seen[name]) return;
        seen[name] = true;
        gtag('event', name, { send_to: ga, service: service() });
    }
    function ordered(reference) {
        if (read() !== 'granted') return;
        load();
        if (ga) gtag('event', 'order_placed', { send_to: ga, service: service(), transaction_id: reference, value: 35.0, currency: 'EUR' });
        if (id && ORDER_LABEL) gtag('event', 'conversion', { send_to: id + '/' + ORDER_LABEL, value: 35.0, currency: 'EUR', transaction_id: reference });
    }
    if (window.MutationObserver) {
        new MutationObserver(function () { if (!timer) timer = setTimeout(step, 300); }).observe(document.documentElement, { subtree: true, childList: true, attributes: true, attributeFilter: ['aria-current'] });
    }
    // The order is recorded by POST /api/journey/cases/<id>/order; a successful answer carries the order number.
    if (window.fetch) {
        var send = window.fetch;
        window.fetch = function (u, o) {
            var p = send.apply(this, arguments);
            try {
                var url = typeof u === 'string' ? u : (u && u.url) || '';
                if (/\/api\/journey\/cases\/[^\/?]+\/order$/.test(url) && o && o.method && o.method !== 'GET') {
                    p.then(function (r) {
                        if (!r.ok) return;
                        r.clone().json().then(function (d) { ordered((d && d.case && d.case.order && d.case.order.reference) || ''); }).catch(function () { ordered(''); });
                    }).catch(function () {});
                }
            } catch (e) {}
            return p;
        };
    }

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
