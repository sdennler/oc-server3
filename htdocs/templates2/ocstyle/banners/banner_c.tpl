{literal}

    <div id="oc-banner-c-overlay" role="dialog" aria-modal="true" aria-labelledby="oc-banner-c-headline" style="display:none">
        <style>
            #oc-banner-c-overlay {
                --oc-banner-overlay-bg: rgba(58, 102, 58, 0.75);
                --oc-banner-card-bg: #ffffff;
                --oc-banner-text: #4b4b4b;
                --oc-banner-heading: #3a663a;
                --oc-banner-accent: #3a663a;
                --oc-banner-btn-bg: #3a663a;
                --oc-banner-btn-text: #ffffff;
                --oc-banner-dismiss-link: #3a6d8f;
                --oc-banner-close-color: #666666;

                position: fixed;
                inset: 0;
                z-index: 9999;
                background-color: var(--oc-banner-overlay-bg);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 16px;
                box-sizing: border-box;
            }

            #oc-banner-c-overlay.oc-banner-c-visible {
                animation: ocBannerCFadeIn 150ms ease forwards;
            }

            #oc-banner-c-overlay.oc-banner-c-visible .oc-banner-c__card {
                animation: ocBannerCScaleIn 150ms ease forwards;
            }

            #oc-banner-c-overlay.oc-banner-dismissing {
                transition: opacity 150ms ease;
                opacity: 0;
            }

            @keyframes ocBannerCFadeIn {
                from { opacity: 0; }
                to   { opacity: 1; }
            }

            @keyframes ocBannerCScaleIn {
                from { transform: scale(0.95); opacity: 0; }
                to   { transform: scale(1);    opacity: 1; }
            }

            @media (prefers-reduced-motion: reduce) {
                #oc-banner-c-overlay.oc-banner-c-visible,
                #oc-banner-c-overlay.oc-banner-c-visible .oc-banner-c__card {
                    animation: none;
                }
                #oc-banner-c-overlay.oc-banner-dismissing {
                    transition: none;
                }
            }

            .oc-banner-c__card {
                position: relative;
                background-color: var(--oc-banner-card-bg);
                border: 1px solid #646464;
                border-top: 3px solid #5890a8;
                max-width: 520px;
                width: 100%;
                padding: 32px 32px 28px;
                box-sizing: border-box;
                text-align: center;
                font-family: verdana, arial, sans-serif;
                color: var(--oc-banner-text);
            }

            .oc-banner-c__illustration {
                width: 72px;
                height: 72px;
                margin: 0 auto 16px;
                color: var(--oc-banner-accent);
            }

            .oc-banner-c__headline {
                font-size: 20px;
                font-weight: bold;
                color: var(--oc-banner-heading);
                margin: 0 0 12px;
                line-height: 1.3;
            }

            .oc-banner-c__body {
                font-size: 14px;
                line-height: 1.6;
                color: var(--oc-banner-text);
                margin: 0 0 24px;
            }

            .oc-banner-c__cta {
                display: inline-block;
                padding: 12px 32px;
                background-color: var(--oc-banner-btn-bg);
                color: var(--oc-banner-btn-text);
                font-weight: bold;
                font-size: 15px;
                text-decoration: none;
                text-transform: uppercase;
                margin-bottom: 16px;
            }

            .oc-banner-c__cta:hover,
            .oc-banner-c__cta:focus {
                background-color: #4d8a4d;
                color: #ffffff;
                text-decoration: none;
                outline: 2px solid #3a663a;
                outline-offset: 2px;
            }

            .oc-banner-c__dismiss-link {
                display: block;
                font-size: 13px;
                color: var(--oc-banner-dismiss-link);
                text-decoration: underline;
                cursor: pointer;
                background: none;
                border: none;
                padding: 4px 8px;
                margin: 0 auto;
            }

            .oc-banner-c__dismiss-link:hover,
            .oc-banner-c__dismiss-link:focus {
                color: #2e6080;
                outline: 2px solid var(--oc-banner-dismiss-link);
                outline-offset: 2px;
            }

            .oc-banner-c__close {
                position: absolute;
                top: 12px;
                right: 12px;
                background: none;
                border: none;
                color: var(--oc-banner-close-color);
                font-size: 20px;
                line-height: 1;
                cursor: pointer;
                padding: 4px 6px;
            }

            .oc-banner-c__close:hover,
            .oc-banner-c__close:focus {
                color: #333;
                background-color: rgba(0,0,0,0.07);
                outline: 2px solid #3a663a;
                outline-offset: 1px;
            }

            @media (max-width: 599px) {
                .oc-banner-c__card {
                    padding: 28px 20px 24px;
                }

                .oc-banner-c__headline {
                    font-size: 17px;
                }

                .oc-banner-c__body {
                    font-size: 13px;
                }
            }
        </style>

        <div class="oc-banner-c__card">
            <!-- SVG: compass/map pin motif representing geocaching + donation -->
            <svg
                class="oc-banner-c__illustration"
                aria-hidden="true"
                focusable="false"
                viewBox="0 0 72 72"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Background circle -->
                <circle cx="36" cy="36" r="34" fill="#3a663a" opacity="0.10"/>
                <circle cx="36" cy="36" r="34" stroke="#3a663a" stroke-width="2.5" fill="none"/>
                <!-- Compass rose simplified -->
                <circle cx="36" cy="36" r="14" fill="#3a663a" opacity="0.15"/>
                <circle cx="36" cy="36" r="14" stroke="#3a663a" stroke-width="2" fill="none"/>
                <!-- North arrow -->
                <polygon points="36,14 31,28 36,24 41,28" fill="#3a663a"/>
                <!-- South arrow -->
                <polygon points="36,58 41,44 36,48 31,44" fill="#3a663a" opacity="0.45"/>
                <!-- East/West ticks -->
                <line x1="58" y1="36" x2="52" y2="36" stroke="#3a663a" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="14" y1="36" x2="20" y2="36" stroke="#3a663a" stroke-width="2.5" stroke-linecap="round"/>
                <!-- Center dot -->
                <circle cx="36" cy="36" r="3" fill="#3a663a"/>
                <!-- Euro badge bottom-right -->
                <circle cx="54" cy="54" r="10" fill="#95cb66"/>
                <text
                    x="54"
                    y="58"
                    text-anchor="middle"
                    dominant-baseline="middle"
                    font-family="Verdana, Arial, sans-serif"
                    font-size="11"
                    font-weight="bold"
                    fill="#1a3a1a"
                >€</text>
            </svg>

            <h2 class="oc-banner-c__headline" id="oc-banner-c-headline">
                <!-- DE -->
                opencaching.de braucht deine Hilfe!
                <!-- EN: opencaching.de needs your help! -->
            </h2>

            <p class="oc-banner-c__body">
                <!-- DE -->
                Unsere Plattform ist kostenlos und werbefrei – finanziert nur durch Spenden.
                Bitte unterstütze uns, damit wir online bleiben können.
                <!-- EN: Our platform is free and ad-free – funded only by donations.
                     Please support us so we can stay online. -->
            </p>

            <a
                class="oc-banner-c__cta"
                id="oc-banner-c__cta-link"
                href="#"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Jetzt spenden — opencaching.de unterstützen"
            >
                <!-- DE -->Jetzt spenden<!-- EN: Donate now -->
            </a>

            <button
                class="oc-banner-c__dismiss-link"
                id="oc-banner-c__dismiss-btn"
                type="button"
            >
                <!-- DE -->Vielleicht später<!-- EN: Maybe later -->
            </button>

            <button
                class="oc-banner-c__close"
                id="oc-banner-c__close-btn"
                type="button"
                aria-label="Modal schließen"
            >&#x2715;</button>
        </div>
    </div>

    <script>
        (function () {
            'use strict';

            const DONATION_URL = 'https://www.opencaching.de/articles.php?page=donations';
            const DISMISS_DAYS = 1;
            const STORAGE_KEY  = 'oc_banner_dismissed';
            const OVERLAY_ID   = 'oc-banner-c-overlay';

            if (window.__ocBannerCInit) return;
            window.__ocBannerCInit = true;

            function isDismissed() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    if (!raw) return false;
                    const dismissedAt = new Date(raw).getTime();
                    const expiry = dismissedAt + DISMISS_DAYS * 60 * 1000;
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

            // Collect all focusable elements within a container
            function getFocusable(container) {
                return Array.from(container.querySelectorAll(
                    'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
                ));
            }

            function dismissModal(overlay, restoreFocusTo) {
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                // Unlock scroll
                document.body.style.overflow = '';

                if (prefersReduced) {
                    overlay.style.display = 'none';
                    setDismissed();
                    if (restoreFocusTo && restoreFocusTo.focus) restoreFocusTo.focus();
                    return;
                }
                overlay.classList.add('oc-banner-dismissing');
                var done = false;
                function finish() {
                    if (done) return;
                    done = true;
                    overlay.style.display = 'none';
                    setDismissed();
                    if (restoreFocusTo && restoreFocusTo.focus) restoreFocusTo.focus();
                }
                overlay.addEventListener('transitionend', finish, { once: true });
                setTimeout(finish, 400);
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (isDismissed()) return;

                var overlay     = document.getElementById(OVERLAY_ID);
                var closeBtn    = document.getElementById('oc-banner-c__close-btn');
                var dismissBtn  = document.getElementById('oc-banner-c__dismiss-btn');
                var ctaLink     = document.getElementById('oc-banner-c__cta-link');

                if (!overlay) return;

                ctaLink.href = DONATION_URL;

                // Show modal
                overlay.style.display = 'flex';
                overlay.classList.add('oc-banner-c-visible');

                // Lock scroll
                document.body.style.overflow = 'hidden';

                var prevFocus = document.activeElement;

                // Move focus into modal
                var focusable = getFocusable(overlay);
                if (focusable.length) focusable[0].focus();

                function handleDismiss() {
                    dismissModal(overlay, prevFocus);
                }

                closeBtn.addEventListener('click', handleDismiss);
                dismissBtn.addEventListener('click', handleDismiss);

                // Overlay click dismisses (click on backdrop, not card)
                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay) handleDismiss();
                });

                // Escape key dismisses
                document.addEventListener('keydown', function escHandler(e) {
                    if (e.key === 'Escape') {
                        document.removeEventListener('keydown', escHandler);
                        handleDismiss();
                    }
                });

                // Focus trap: keep Tab/Shift+Tab cycling inside the modal
                overlay.addEventListener('keydown', function (e) {
                    if (e.key !== 'Tab') return;
                    var focusableNow = getFocusable(overlay);
                    if (!focusableNow.length) return;
                    var first = focusableNow[0];
                    var last  = focusableNow[focusableNow.length - 1];

                    if (e.shiftKey) {
                        if (document.activeElement === first) {
                            e.preventDefault();
                            last.focus();
                        }
                    } else {
                        if (document.activeElement === last) {
                            e.preventDefault();
                            first.focus();
                        }
                    }
                });
            });
        }());
    </script>

{/literal}
