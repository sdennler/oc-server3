{literal}
<style id="oc-banner-layout-fix">
    body.oc-has-banner div#langstripe { position: relative !important; top: auto !important; left: auto !important; }
    body.oc-has-banner .page-container-1 { margin-top: 0 !important; }
</style>

    <div id="oc-banner-b2" role="banner" aria-label="Spendenaufruf opencaching.de">
        <style>
            #oc-banner-b2 {
                --oc-banner-bg: #e8eff2;
                --oc-banner-text: #4b4b4b;
                --oc-banner-accent: #3a6d8f;
                --oc-banner-btn-bg: #3a6d8f;
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

            #oc-banner-b2.oc-banner-dismissing {
                transition: opacity 200ms ease, max-height 200ms ease;
                opacity: 0; max-height: 0; border-bottom-width: 0; padding: 0;
            }

            @media (prefers-reduced-motion: reduce) {
                #oc-banner-b2.oc-banner-dismissing { transition: none; }
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
                flex: 0 0 auto;
                width: 52px;
                height: 52px;
                object-fit: contain;
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
            <img
                class="oc-banner-b__icon"
                src="/resource2/misc/donation/globi_box_smal.png"
                alt=""
                aria-hidden="true"
            >

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
            const DONATION_URL = '/articles.php?page=donations';
            const DISMISS_DAYS = 7;
            const STORAGE_KEY  = 'oc_banner_dismissed';
            const BANNER_ID    = 'oc-banner-b2';

            if (window.__ocBannerB2Init) return;
            window.__ocBannerB2Init = true;

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
                var closeBtn = document.getElementById('oc-banner-b2__close-btn');
                var ctaLink = document.getElementById('oc-banner-b2__cta-link');
                if (!banner) return;
                ctaLink.href = DONATION_URL;
                var prevFocus = document.activeElement;
                closeBtn.addEventListener('click', function () { dismissBanner(banner, prevFocus); });
            });
        }());
    </script>

{/literal}
