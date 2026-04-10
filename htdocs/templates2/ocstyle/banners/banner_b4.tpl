{literal}

    <div id="oc-banner-b4" role="banner" aria-label="Spendenaufruf opencaching.de" style="display:none">
        <style>
            #oc-banner-b4 {
                --oc-banner-bg: #d8e5c6;
                --oc-banner-text: #4b4b4b;
                --oc-banner-accent: #2e5e2e;
                --oc-banner-btn-bg: #2e5e2e;
                --oc-banner-btn-text: #ffffff;
                --oc-banner-border: #5890a8;
                --oc-banner-close-color: #555555;

                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 9999;
                background-color: var(--oc-banner-bg);
                color: var(--oc-banner-text);
                font-family: verdana, arial, sans-serif;
                font-size: 14px;
                border-bottom: 3px solid var(--oc-banner-border);
                box-sizing: border-box;
            }

            #oc-banner-b4.oc-banner-dismissing {
                transition: opacity 200ms ease, transform 200ms ease;
                opacity: 0;
                transform: translateY(-100%);
            }

            @media (prefers-reduced-motion: reduce) {
                #oc-banner-b4.oc-banner-dismissing {
                    transition: none;
                }
            }

            #oc-banner-b4 .oc-banner-b__inner {
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 12px 52px 12px 16px;
                max-width: 1200px;
                margin: 0 auto;
                box-sizing: border-box;
            }

            #oc-banner-b4 .oc-banner-b__icon {
                flex: 0 0 52px;
                width: 52px;
                height: 52px;
            }

            #oc-banner-b4 .oc-banner-b__copy {
                flex: 1 1 auto;
            }

            #oc-banner-b4 .oc-banner-b__headline {
                font-weight: bold;
                font-size: 15px;
                color: var(--oc-banner-accent);
                margin: 0 0 3px;
                line-height: 1.3;
            }

            #oc-banner-b4 .oc-banner-b__subtext {
                font-size: 13px;
                color: var(--oc-banner-text);
                margin: 0;
                line-height: 1.4;
            }

            #oc-banner-b4 .oc-banner-b__cta {
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

            #oc-banner-b4 .oc-banner-b__cta:hover,
            #oc-banner-b4 .oc-banner-b__cta:focus {
                background-color: #3a7a3a;
                color: #ffffff;
                text-decoration: none;
                outline: 2px solid #2e5e2e;
                outline-offset: 2px;
            }

            #oc-banner-b4 .oc-banner-b__close {
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

            #oc-banner-b4 .oc-banner-b__close:hover,
            #oc-banner-b4 .oc-banner-b__close:focus {
                color: #222;
                background-color: rgba(0,0,0,0.08);
                outline: 2px solid #2e5e2e;
                outline-offset: 1px;
            }

            @media (max-width: 599px) {
                #oc-banner-b4 .oc-banner-b__icon {
                    display: none;
                }

                #oc-banner-b4 .oc-banner-b__inner {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                    padding: 12px 44px 12px 12px;
                }

                #oc-banner-b4 .oc-banner-b__cta {
                    align-self: center;
                }
            }
        </style>

        <div class="oc-banner-b__inner">
            <!-- SVG: pine tree with a small geocache box at its base -->
            <svg
                class="oc-banner-b__icon"
                aria-hidden="true"
                focusable="false"
                viewBox="0 0 52 52"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Background circle -->
                <circle cx="26" cy="26" r="24" fill="#2e5e2e" opacity="0.10"/>
                <circle cx="26" cy="26" r="24" stroke="#2e5e2e" stroke-width="2" fill="none"/>
                <!-- Pine tree trunk -->
                <rect x="24" y="38" width="4" height="6" rx="1" fill="#2e5e2e" opacity="0.6"/>
                <!-- Pine tree — bottom tier -->
                <polygon points="26,14 14,36 38,36" fill="#2e5e2e" opacity="0.85"/>
                <!-- Pine tree — middle tier (lighter overlap for depth) -->
                <polygon points="26,10 16,30 36,30" fill="#2e5e2e"/>
                <!-- Pine tree — top tier -->
                <polygon points="26,6 19,24 33,24" fill="#2e5e2e"/>
                <!-- Small geocache box at base, right of trunk -->
                <rect x="31" y="36" width="7" height="6" rx="1" fill="#2e5e2e" opacity="0.7" stroke="#2e5e2e" stroke-width="1"/>
                <!-- Cache lid line -->
                <line x1="31" y1="38.5" x2="38" y2="38.5" stroke="#f0f7ee" stroke-width="1"/>
                <!-- Cache latch dot -->
                <circle cx="34.5" cy="37" r="0.8" fill="#f0f7ee"/>
            </svg>

            <div class="oc-banner-b__copy">
                <p class="oc-banner-b__headline">
                    <!-- DE -->
                    Natur entdecken. Kostenlos. Werbefrei.
                    <!-- EN: Discover nature. Free. Ad-free. -->
                </p>
                <p class="oc-banner-b__subtext">
                    <!-- DE -->
                    Geocaching auf opencaching.de ist und bleibt kostenlos – finanziert von Menschen wie dir.
                    <!-- EN: Geocaching on opencaching.de is and will remain free – funded by people like you. -->
                </p>
            </div>

            <a
                class="oc-banner-b__cta"
                id="oc-banner-b4__cta-link"
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
            id="oc-banner-b4__close-btn"
            type="button"
            aria-label="Banner schließen"
        >&#x2715;</button>
    </div>

    <script>
        (function () {
            'use strict';

            const DONATION_URL = 'https://www.opencaching.de/articles.php?page=donations';
            const DISMISS_DAYS = 7;
            const STORAGE_KEY  = 'oc_banner_dismissed';
            const BANNER_ID    = 'oc-banner-b4';

            if (window.__ocBannerB4Init) return;
            window.__ocBannerB4Init = true;

            function isDismissed() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    if (!raw) return false;
                    const dismissedAt = new Date(raw).getTime();
                    const expiry = dismissedAt + 1 * 60 * 1000;
                    return Date.now() < expiry;
                } catch (_) {
                    return false;
                }
            }

            function setDismissed() {
                try {
                    localStorage.setItem(STORAGE_KEY, new Date().toISOString());
                } catch (_) {}
            }

            function dismissBanner(banner, restoreFocusTo) {
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (prefersReduced) {
                    banner.style.display = 'none';
                    setDismissed();
                    if (restoreFocusTo && restoreFocusTo.focus) restoreFocusTo.focus();
                    return;
                }
                banner.classList.add('oc-banner-dismissing');
                var done = false;
                function finish() {
                    if (done) return;
                    done = true;
                    banner.style.display = 'none';
                    setDismissed();
                    if (restoreFocusTo && restoreFocusTo.focus) restoreFocusTo.focus();
                }
                banner.addEventListener('transitionend', finish, { once: true });
                setTimeout(finish, 400);
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (isDismissed()) return;

                var banner   = document.getElementById(BANNER_ID);
                var closeBtn = document.getElementById('oc-banner-b4__close-btn');
                var ctaLink  = document.getElementById('oc-banner-b4__cta-link');

                if (!banner) return;

                ctaLink.href = DONATION_URL;
                banner.style.display = 'block';

                var prevFocus = document.activeElement;

                closeBtn.addEventListener('click', function () {
                    dismissBanner(banner, prevFocus);
                });
            });
        }());
    </script>

{/literal}
