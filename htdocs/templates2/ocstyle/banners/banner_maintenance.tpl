{* Maintenance Banner Template - Placeholder *}
{literal}<div id="oc-banner-a" role="banner" aria-label="Maintenance banner" style="display:none">
    <style>
        #oc-banner-a {
            --oc-banner-bg: #CC0000;
            --oc-banner-text: #ffffff;
            --oc-banner-btn-bg: #FF6666;
            --oc-banner-btn-text: #000000;
            --oc-banner-close-color: rgba(255,255,255,0.85);

            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 9999;
            background-color: var(--oc-banner-bg);
            color: var(--oc-banner-text);
            font-family: verdana, arial, sans-serif;
            font-size: 14px;
            border-bottom: 2px solid #660000;
            box-sizing: border-box;
        }

        #oc-banner-a.oc-banner-dismissing {
            transition: opacity 200ms ease, transform 200ms ease;
            opacity: 0;
            transform: translateY(-100%);
        }

        @media (prefers-reduced-motion: reduce) {
            #oc-banner-a.oc-banner-dismissing {
                transition: none;
            }
        }

        .oc-banner-a__inner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 10px 48px 10px 16px;
            min-height: 44px;
            box-sizing: border-box;
        }

        .oc-banner-a__text {
            flex: 1 1 auto;
            text-align: center;
            line-height: 1.4;
        }

        .oc-banner-a__cta {
            flex: 0 0 auto;
            display: inline-block;
            padding: 6px 16px;
            background-color: var(--oc-banner-btn-bg);
            color: var(--oc-banner-btn-text);
            font-weight: bold;
            font-size: 13px;
            text-decoration: none;
            text-transform: uppercase;
            white-space: nowrap;
            line-height: 1.4;
        }

        .oc-banner-a__cta:hover,
        .oc-banner-a__cta:focus {
            background-color: #FF9999;
            color: var(--oc-banner-btn-text);
            text-decoration: none;
            outline: 2px solid #ffffff;
            outline-offset: 2px;
        }

        .oc-banner-a__close {
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

        .oc-banner-a__close:hover,
        .oc-banner-a__close:focus {
            color: #ffffff;
            background-color: rgba(255,255,255,0.15);
            outline: 2px solid #ffffff;
            outline-offset: 1px;
        }

        @media (max-width: 599px) {
            .oc-banner-a__inner {
                flex-direction: column;
                text-align: center;
                padding: 12px 44px 12px 12px;
                gap: 8px;
            }

            .oc-banner-a__text {
                font-size: 13px;
            }
        }
    </style>

    <div class="oc-banner-a__inner">
    <span class="oc-banner-a__text">
      ⚠️ Maintenance Banner - TODO: Customize with maintenance information
    </span>
        <a
            class="oc-banner-a__cta"
            id="oc-banner-a__cta-link"
            href="#"
            target="_blank"
            rel="noopener noreferrer"
        >
            More info
        </a>
    </div>

    <button
        class="oc-banner-a__close"
        id="oc-banner-a__close-btn"
        type="button"
        aria-label="Banner schließen"
    >&#x2715;</button>
</div>

<script>
    (function () {
        'use strict';

        const MAINTENANCE_URL = '#';
        const DISMISS_DAYS = 1;
        const STORAGE_KEY  = 'oc_banner_maintenance_dismissed';
        const BANNER_ID    = 'oc-banner-a';

        if (window.__ocBannerMaintenanceInit) return;
        window.__ocBannerMaintenanceInit = true;

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

            var banner    = document.getElementById(BANNER_ID);
            var closeBtn  = document.getElementById('oc-banner-a__close-btn');
            var ctaLink   = document.getElementById('oc-banner-a__cta-link');

            if (!banner) return;

            ctaLink.href = MAINTENANCE_URL;
            banner.style.display = 'block';

            var prevFocus = document.activeElement;

            closeBtn.addEventListener('click', function () {
                dismissBanner(banner, prevFocus);
            });
        });
    }());
</script>{/literal}

