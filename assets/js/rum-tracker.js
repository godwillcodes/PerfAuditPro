(function() {
    'use strict';

    if (typeof window.PerfAuditPro === 'undefined') {
        window.PerfAuditPro = {};
    }

    const PerfAuditPro = window.PerfAuditPro;
    const apiUrl = PerfAuditPro.apiUrl || '/wp-json/perfaudit-pro/v1/rum-intake';
    const nonce = PerfAuditPro.nonce || '';

    function sendMetrics(url, metrics) {
        if (navigator.sendBeacon) {
            const data = new Blob([JSON.stringify({
                url: url,
                metrics: metrics
            })], { type: 'application/json' });
            navigator.sendBeacon(apiUrl + '?_wpnonce=' + encodeURIComponent(nonce), data);
        } else {
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify({
                    url: url,
                    metrics: metrics
                }),
                keepalive: true
            }).catch(function() {});
        }
    }

    function handleCLS(metric) {
        sendMetrics(window.location.href, {
            cls: metric.value
        });
    }

    function handleFCP(metric) {
        sendMetrics(window.location.href, {
            fcp: metric.value
        });
    }

    function handleFID(metric) {
        sendMetrics(window.location.href, {
            fid: metric.value
        });
    }

    function handleLCP(metric) {
        sendMetrics(window.location.href, {
            lcp: metric.value
        });
    }

    function handleTTFB(metric) {
        sendMetrics(window.location.href, {
            ttfb: metric.value
        });
    }

    if (typeof window.webVitals !== 'undefined') {
        window.webVitals.onCLS(handleCLS);
        window.webVitals.onFCP(handleFCP);
        window.webVitals.onFID(handleFID);
        window.webVitals.onLCP(handleLCP);
        window.webVitals.onTTFB(handleTTFB);
    } else {
        (function() {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/web-vitals@3/dist/web-vitals.attribution.iife.js';
            script.onload = function() {
                if (window.webVitals) {
                    window.webVitals.onCLS(handleCLS);
                    window.webVitals.onFCP(handleFCP);
                    window.webVitals.onFID(handleFID);
                    window.webVitals.onLCP(handleLCP);
                    window.webVitals.onTTFB(handleTTFB);
                }
            };
            document.head.appendChild(script);
        })();
    }
})();

