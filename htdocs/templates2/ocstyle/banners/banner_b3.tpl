{literal}
<style id="oc-banner-layout-fix">
    body.oc-has-banner div#langstripe { position: relative !important; top: auto !important; left: auto !important; }
    body.oc-has-banner .page-container-1 { margin-top: 0 !important; }
</style>

    <div id="oc-banner-b3" role="banner" aria-label="Spendenaufruf opencaching.de">
        <style>
            #oc-banner-b3 {
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

            #oc-banner-b3.oc-banner-dismissing {
                transition: opacity 200ms ease, max-height 200ms ease;
                opacity: 0; max-height: 0; border-bottom-width: 0; padding: 0;
            }

            @media (prefers-reduced-motion: reduce) {
                #oc-banner-b3.oc-banner-dismissing { transition: none; }
            }

            #oc-banner-b3 .oc-banner-b__inner {
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 12px 52px 12px 16px;
                max-width: 1200px;
                margin: 0 auto;
                box-sizing: border-box;
            }

            #oc-banner-b3 .oc-banner-b__icon {
                flex: 0 0 52px;
                width: 52px;
                height: 52px;
            }

            #oc-banner-b3 .oc-banner-b__copy {
                flex: 1 1 auto;
            }

            #oc-banner-b3 .oc-banner-b__headline {
                font-weight: bold;
                font-size: 15px;
                color: var(--oc-banner-accent);
                margin: 0 0 3px;
                line-height: 1.3;
            }

            #oc-banner-b3 .oc-banner-b__subtext {
                font-size: 13px;
                color: var(--oc-banner-text);
                margin: 0;
                line-height: 1.4;
            }

            #oc-banner-b3 .oc-banner-b__cta {
                flex: 0 0 auto;
                display: inline-block;
                padding: 9px 20px;
                background-color: var(--oc-banner-btn-bg);
                color: var(--oc-banner-btn-text);
                font-weight: bold;
                font-size: 14px;
                text-decoration: none;
                text-transform: uppercase;
                white-space: nowrap;
                line-height: 1.4;
            }

            #oc-banner-b3 .oc-banner-b__cta:hover,
            #oc-banner-b3 .oc-banner-b__cta:focus {
                background-color: #4d8a4d;
                color: #ffffff;
                text-decoration: none;
                outline: 2px solid #3a663a;
                outline-offset: 2px;
            }

            #oc-banner-b3 .oc-banner-b__close {
                position: absolute;
                top: 50%;
                right: 12px;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: var(--oc-banner-close-color);
                font-size: 20px;
                line-height: 1;
                cursor: pointer;
                padding: 4px 6px;
            }

            #oc-banner-b3 .oc-banner-b__close:hover,
            #oc-banner-b3 .oc-banner-b__close:focus {
                color: #333;
                background-color: rgba(0,0,0,0.08);
                outline: 2px solid #3a663a;
                outline-offset: 1px;
            }

            @media (max-width: 599px) {
                #oc-banner-b3 .oc-banner-b__icon {
                    display: none;
                }

                #oc-banner-b3 .oc-banner-b__inner {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                    padding: 12px 44px 12px 12px;
                }

                #oc-banner-b3 .oc-banner-b__cta {
                    align-self: center;
                }
            }
        </style>

        <div class="oc-banner-b__inner">
            <!-- SVG: geocache ammo-box with three rising bar-chart bars visible inside -->
            <svg
                class="oc-banner-b__icon"
                aria-hidden="true"
                focusable="false"
                viewBox="0 0 52 52"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Background circle -->
                <circle cx="26" cy="26" r="24" fill="#3a663a" opacity="0.10"/>
                <circle cx="26" cy="26" r="24" stroke="#3a663a" stroke-width="2" fill="none"/>
                <!-- Ammo-box body -->
                <rect x="11" y="20" width="30" height="20" rx="2" fill="#3a663a" opacity="0.15" stroke="#3a663a" stroke-width="1.5"/>
                <!-- Ammo-box lid -->
                <rect x="11" y="16" width="30" height="6" rx="2" fill="#3a663a" opacity="0.85"/>
                <!-- Latch -->
                <rect x="23" y="14" width="6" height="4" rx="1" fill="#3a663a"/>
                <!-- Bar chart bars inside box -->
                <rect x="16" y="32" width="4" height="6" rx="1" fill="#3a663a" opacity="0.9"/>
                <rect x="22" y="28" width="4" height="10" rx="1" fill="#3a663a" opacity="0.9"/>
                <rect x="28" y="24" width="4" height="14" rx="1" fill="#3a663a" opacity="0.9"/>
                <!-- Upward arrow -->
                <polyline points="33,30 36,26 39,30" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>

            <div class="oc-banner-b__copy">
                <p class="oc-banner-b__headline">
                    <!-- DE -->
                    Tausende Caches. Eine unabhängige Plattform. Deine Spende.
                    <!-- EN: Thousands of caches. One independent platform. Your donation. -->
                </p>
                <p class="oc-banner-b__subtext">
                    <!-- DE -->
                    opencaching.de ist kostenlos und werbefrei – und bleibt es durch Spenden wie deiner.
                    <!-- EN: opencaching.de is free and ad-free – and stays that way through donations like yours. -->
                </p>
            </div>

            <a
                class="oc-banner-b__cta"
                id="oc-banner-b3__cta-link"
                href="#"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Jetzt spenden — opencaching.de unterstützen"
            >
                <!-- DE -->Jetzt spenden<!-- EN: Donate now -->
            </a>
        </div>

        <button
            class="oc-banner-b__close"
            id="oc-banner-b3__close-btn"
            type="button"
            aria-label="Banner schließen"
        >&#x2715;</button>
    </div>

    <script>
        (function () {
            'use strict';
            const DONATION_URL = '/articles.php?page=donations';
            const STORAGE_KEY  = 'oc_banner_dismissed';
            const BANNER_ID    = 'oc-banner-b3';

            if (window.__ocBannerB3Init) return;
            window.__ocBannerB3Init = true;

            function isDismissed() { try { const raw = localStorage.getItem(STORAGE_KEY); if (!raw) return false; return Date.now() < new Date(raw).getTime() + 1 * 60 * 1000; } catch (_) { return false; } }
            function setDismissed() { try { localStorage.setItem(STORAGE_KEY, new Date().toISOString()); } catch (_) {} }
            function removeBannerLayout() { document.body.classList.remove('oc-has-banner'); }
            function dismissBanner(banner, restoreFocusTo) {
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (prefersReduced) { banner.style.display = 'none'; removeBannerLayout(); setDismissed(); if (restoreFocusTo && restoreFocusTo.focus) restoreFocusTo.focus(); return; }
                banner.classList.add('oc-banner-dismissing');
                var done = false;
                function finish() { if (done) return; done = true; banner.style.display = 'none'; removeBannerLayout(); setDismissed(); if (restoreFocusTo && restoreFocusTo.focus) restoreFocusTo.focus(); }
                banner.addEventListener('transitionend', finish, { once: true }); setTimeout(finish, 400);
            }

            if (!isDismissed()) { document.body.classList.add('oc-has-banner'); }
            else { var b = document.getElementById(BANNER_ID); if (b) b.style.display = 'none'; }

            document.addEventListener('DOMContentLoaded', function () {
                if (isDismissed()) { var b = document.getElementById(BANNER_ID); if (b) b.style.display = 'none'; removeBannerLayout(); return; }
                var banner = document.getElementById(BANNER_ID);
                var closeBtn = document.getElementById('oc-banner-b3__close-btn');
                var ctaLink = document.getElementById('oc-banner-b3__cta-link');
                if (!banner) return;
                ctaLink.href = DONATION_URL;
                var prevFocus = document.activeElement;
                closeBtn.addEventListener('click', function () { dismissBanner(banner, prevFocus); });
            });
        }());
    </script>

{/literal}
