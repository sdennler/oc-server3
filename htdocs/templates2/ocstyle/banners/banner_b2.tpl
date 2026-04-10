{literal}

    <div id="oc-banner-b2" role="banner" aria-label="Spendenaufruf opencaching.de" style="display:none">
        <style>
            #oc-banner-b2 {
                --oc-banner-bg: #e8eff2;
                --oc-banner-text: #4b4b4b;
                --oc-banner-accent: #3a6d8f;
                --oc-banner-btn-bg: #3a6d8f;
                --oc-banner-btn-text: #ffffff;
                --oc-banner-border: #5890a8;
                --oc-banner-close-color: #666666;

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

            #oc-banner-b2.oc-banner-dismissing {
                transition: opacity 200ms ease, transform 200ms ease;
                opacity: 0;
                transform: translateY(-100%);
            }

            @media (prefers-reduced-motion: reduce) {
                #oc-banner-b2.oc-banner-dismissing {
                    transition: none;
                }
            }

            #oc-banner-b2 .oc-banner-b__inner {
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 12px 52px 12px 16px;
                max-width: 1200px;
                margin: 0 auto;
                box-sizing: border-box;
            }

            #oc-banner-b2 .oc-banner-b__icon {
                flex: 0 0 52px;
                width: 52px;
                height: 52px;
            }

            #oc-banner-b2 .oc-banner-b__copy {
                flex: 1 1 auto;
            }

            #oc-banner-b2 .oc-banner-b__headline {
                font-weight: bold;
                font-size: 15px;
                color: var(--oc-banner-accent);
                margin: 0 0 3px;
                line-height: 1.3;
            }

            #oc-banner-b2 .oc-banner-b__subtext {
                font-size: 13px;
                color: var(--oc-banner-text);
                margin: 0;
                line-height: 1.4;
            }

            #oc-banner-b2 .oc-banner-b__cta {
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

            #oc-banner-b2 .oc-banner-b__cta:hover,
            #oc-banner-b2 .oc-banner-b__cta:focus {
                background-color: #2e6080;
                color: #ffffff;
                text-decoration: none;
                outline: 2px solid #3a6d8f;
                outline-offset: 2px;
            }

            #oc-banner-b2 .oc-banner-b__close {
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

            #oc-banner-b2 .oc-banner-b__close:hover,
            #oc-banner-b2 .oc-banner-b__close:focus {
                color: #333;
                background-color: rgba(0,0,0,0.08);
                outline: 2px solid #3a6d8f;
                outline-offset: 1px;
            }

            @media (max-width: 599px) {
                #oc-banner-b2 .oc-banner-b__icon {
                    display: none;
                }

                #oc-banner-b2 .oc-banner-b__inner {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                    padding: 12px 44px 12px 12px;
                }

                #oc-banner-b2 .oc-banner-b__cta {
                    align-self: center;
                }
            }
        </style>

        <div class="oc-banner-b__inner">
            <!-- SVG: two person-silhouettes with a geocache map-pin between them -->
            <svg
                class="oc-banner-b__icon"
                aria-hidden="true"
                focusable="false"
                viewBox="0 0 52 52"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Background circle -->
                <circle cx="26" cy="26" r="24" fill="#3a6d8f" opacity="0.10"/>
                <circle cx="26" cy="26" r="24" stroke="#3a6d8f" stroke-width="2" fill="none"/>
                <!-- Left person silhouette -->
                <circle cx="16" cy="17" r="5" fill="#3a6d8f" opacity="0.75"/>
                <path d="M9 34 C9 27 23 27 23 34" stroke="#3a6d8f" stroke-width="2" fill="none" stroke-linecap="round"/>
                <!-- Right person silhouette -->
                <circle cx="36" cy="17" r="5" fill="#3a6d8f" opacity="0.75"/>
                <path d="M29 34 C29 27 43 27 43 34" stroke="#3a6d8f" stroke-width="2" fill="none" stroke-linecap="round"/>
                <!-- Map pin / geocache in the middle -->
                <path d="M26 22 C23.2 22 21 24.2 21 27 C21 30.8 26 37 26 37 C26 37 31 30.8 31 27 C31 24.2 28.8 22 26 22 Z" fill="#3a6d8f"/>
                <circle cx="26" cy="27" r="2.5" fill="#ffffff"/>
            </svg>

            <div class="oc-banner-b__copy">
                <p class="oc-banner-b__headline">
                    <!-- DE -->
                    opencaching.de – von der Community, für die Community.
                    <!-- EN: opencaching.de – by the community, for the community. -->
                </p>
                <p class="oc-banner-b__subtext">
                    <!-- DE -->
                    Unsere Plattform gehört keinem Konzern. Sie gehört euch. Bitte unterstützt sie.
                    <!-- EN: Our platform belongs to no corporation. It belongs to you. Please support it. -->
                </p>
            </div>

            <a
                class="oc-banner-b__cta"
                id="oc-banner-b2__cta-link"
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
            id="oc-banner-b2__close-btn"
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
            const BANNER_ID    = 'oc-banner-b2';

            if (window.__ocBannerB2Init) return;
            window.__ocBannerB2Init = true;

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
                var closeBtn = document.getElementById('oc-banner-b2__close-btn');
                var ctaLink  = document.getElementById('oc-banner-b2__cta-link');

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
