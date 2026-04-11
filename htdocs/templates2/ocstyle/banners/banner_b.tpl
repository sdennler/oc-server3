{literal}
<style id="oc-banner-layout-fix">
    body.oc-has-banner div#langstripe {
        position: relative !important;
        top: auto !important;
        left: auto !important;
    }
    body.oc-has-banner .page-container-1 {
        margin-top: 0 !important;
    }
</style>
    <div id="oc-banner-b" role="banner" aria-label="Spendenaufruf opencaching.de">
        <style>
            #oc-banner-b {
                --oc-banner-bg: #dbe6f1;
                --oc-banner-text: #4b4b4b;
                --oc-banner-accent: #3a663a;
                --oc-banner-btn-bg: #3a663a;
                --oc-banner-btn-text: #ffffff;
                --oc-banner-border: #5890a8;
                --oc-banner-close-color: #666666;

                position: relative;
                z-index: 6;
                background-color: var(--oc-banner-bg);
                color: var(--oc-banner-text);
                font-family: verdana, arial, sans-serif;
                font-size: 14px;
                border-bottom: 3px solid var(--oc-banner-border);
                box-sizing: border-box;
                overflow: hidden;
            }

            #oc-banner-b.oc-banner-dismissing {
                transition: opacity 200ms ease, max-height 200ms ease;
                opacity: 0;
                max-height: 0;
                border-bottom-width: 0;
                padding: 0;
            }

            @media (prefers-reduced-motion: reduce) {
                #oc-banner-b.oc-banner-dismissing { transition: none; }
            }

            .oc-banner-b__inner { display: flex; align-items: center; gap: 16px; padding: 12px 52px 12px 16px; max-width: 1200px; margin: 0 auto; box-sizing: border-box; }
            .oc-banner-b__icon { flex: 0 0 52px; width: 52px; height: 52px; color: var(--oc-banner-accent); }
            .oc-banner-b__copy { flex: 1 1 auto; }
            .oc-banner-b__headline { font-weight: bold; font-size: 15px; color: var(--oc-banner-accent); margin: 0 0 3px; line-height: 1.3; }
            .oc-banner-b__subtext { font-size: 13px; color: var(--oc-banner-text); margin: 0; line-height: 1.4; }
            .oc-banner-b__cta { flex: 0 0 auto; display: inline-block; padding: 9px 20px; background-color: var(--oc-banner-btn-bg); color: var(--oc-banner-btn-text); font-weight: bold; font-size: 14px; text-decoration: none; text-transform: uppercase; white-space: nowrap; line-height: 1.4; }
            .oc-banner-b__cta:hover, .oc-banner-b__cta:focus { background-color: #4d8a4d; color: #ffffff; text-decoration: none; outline: 2px solid #3a663a; outline-offset: 2px; }
            .oc-banner-b__close { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); background: none; border: none; color: var(--oc-banner-close-color); font-size: 20px; line-height: 1; cursor: pointer; padding: 4px 6px; }
            .oc-banner-b__close:hover, .oc-banner-b__close:focus { color: #333; background-color: rgba(0,0,0,0.08); outline: 2px solid #3a663a; outline-offset: 1px; }
            @media (max-width: 599px) {
                .oc-banner-b__icon { display: none; }
                .oc-banner-b__inner { flex-direction: column; align-items: flex-start; gap: 10px; padding: 12px 44px 12px 12px; }
                .oc-banner-b__cta { align-self: center; }
            }
        </style>

        <div class="oc-banner-b__inner">
            <svg class="oc-banner-b__icon" aria-hidden="true" focusable="false" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="26" cy="26" r="24" fill="#3a663a" opacity="0.12"/>
                <circle cx="26" cy="26" r="24" stroke="#3a663a" stroke-width="2" fill="none"/>
                <path d="M26 38 C26 38 12 28 12 19.5 C12 15.4 15.1 12 19 12 C21.6 12 24 13.8 26 16 C28 13.8 30.4 12 33 12 C36.9 12 40 15.4 40 19.5 C40 28 26 38 26 38 Z" fill="#3a663a" opacity="0.85"/>
                <text x="26" y="27" text-anchor="middle" dominant-baseline="middle" font-family="Verdana, Arial, sans-serif" font-size="13" font-weight="bold" fill="#ffffff">€</text>
            </svg>

            <div class="oc-banner-b__copy">
                <p class="oc-banner-b__headline">Hilf uns, opencaching.de am Leben zu erhalten!</p>
                <p class="oc-banner-b__subtext">Der Betrieb dieser Seite kostet Geld. Jede Spende hilft – egal wie klein.</p>
            </div>

            <a class="oc-banner-b__cta" id="oc-banner-b__cta-link" href="#" target="_blank" rel="noopener noreferrer" aria-label="Jetzt spenden — opencaching.de unterstützen">Jetzt spenden</a>
        </div>

        <button class="oc-banner-b__close" id="oc-banner-b__close-btn" type="button" aria-label="Banner schließen">&#x2715;</button>
    </div>

    <script>
        (function () {
            'use strict';
            const DONATION_URL = '/articles.php?page=donations';
            const DISMISS_DAYS = 1;
            const STORAGE_KEY  = 'oc_banner_dismissed';
            const BANNER_ID    = 'oc-banner-b';

            if (window.__ocBannerBInit) return;
            window.__ocBannerBInit = true;

            function isDismissed() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    if (!raw) return false;
                    return Date.now() < new Date(raw).getTime() + DISMISS_DAYS * 60 * 1000;
                } catch (_) { return false; }
            }
            function setDismissed() { try { localStorage.setItem(STORAGE_KEY, new Date().toISOString()); } catch (_) {} }
            function removeBannerLayout() { document.body.classList.remove('oc-has-banner'); }
            function dismissBanner(banner, restoreFocusTo) {
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (prefersReduced) { banner.style.display = 'none'; removeBannerLayout(); setDismissed(); if (restoreFocusTo && restoreFocusTo.focus) restoreFocusTo.focus(); return; }
                banner.classList.add('oc-banner-dismissing');
                var done = false;
                function finish() { if (done) return; done = true; banner.style.display = 'none'; removeBannerLayout(); setDismissed(); if (restoreFocusTo && restoreFocusTo.focus) restoreFocusTo.focus(); }
                banner.addEventListener('transitionend', finish, { once: true });
                setTimeout(finish, 400);
            }

            if (!isDismissed()) { document.body.classList.add('oc-has-banner'); }
            else { var b = document.getElementById(BANNER_ID); if (b) b.style.display = 'none'; }

            document.addEventListener('DOMContentLoaded', function () {
                if (isDismissed()) { var b = document.getElementById(BANNER_ID); if (b) b.style.display = 'none'; removeBannerLayout(); return; }
                var banner = document.getElementById(BANNER_ID);
                var closeBtn = document.getElementById('oc-banner-b__close-btn');
                var ctaLink = document.getElementById('oc-banner-b__cta-link');
                if (!banner) return;
                ctaLink.href = DONATION_URL;
                var prevFocus = document.activeElement;
                closeBtn.addEventListener('click', function () { dismissBanner(banner, prevFocus); });
            });
        }());
    </script>
{/literal}
