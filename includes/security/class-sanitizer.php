<?php
/**
 * Security sanitization utilities
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Security
 */

namespace PerfAuditPro\Security;

if (!defined('ABSPATH')) {
    exit;
}

class Sanitizer {

    /**
     * Sanitize metrics array
     *
     * Pure function that sanitizes RUM metrics, only allowing valid Web Vitals keys.
     *
     * @param array<string, mixed> $metrics Raw metrics from frontend
     * @return array<string, float> Sanitized metrics with only valid keys and float values
     */
    public static function sanitize_metrics(array $metrics): array {
        if (!is_array($metrics)) {
            return array();
        }

        $sanitized = array();
        $allowed_keys = array('lcp', 'fid', 'cls', 'fcp', 'ttfb');

        foreach ($allowed_keys as $key) {
            if (isset($metrics[$key])) {
                $sanitized[$key] = floatval($metrics[$key]);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize JSON string
     *
     * Validates and re-encodes JSON to ensure it's safe for storage.
     *
     * @param string|array<string, mixed> $json JSON string or array
     * @return string|false Sanitized JSON string or false on failure
     */
    public static function sanitize_json($json) {
        if (!is_string($json)) {
            return false;
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return wp_json_encode($decoded);
    }

    /**
     * Sanitize IP address
     *
     * Pure function that validates and sanitizes IP addresses.
     *
     * @param string $ip IP address to sanitize
     * @return string Validated IP address or '0.0.0.0' if invalid
     */
    public static function sanitize_ip(string $ip): string {
        return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }

    /**
     * Escape output for HTML
     *
     * @param mixed $value Value to escape
     * @return string Escaped HTML-safe string
     */
    public static function escape_html($value): string {
        return esc_html((string) $value);
    }

    /**
     * Escape output for JavaScript
     *
     * @param mixed $value Value to escape
     * @return string Escaped JavaScript-safe string
     */
    public static function escape_js($value): string {
        return esc_js((string) $value);
    }

    /**
     * Escape output for attributes
     *
     * @param mixed $value Value to escape
     * @return string Escaped attribute-safe string
     */
    public static function escape_attr($value): string {
        return esc_attr((string) $value);
    }

    /**
     * Escape output for URL
     *
     * @param mixed $value Value to escape
     * @return string Escaped URL-safe string
     */
    public static function escape_url($value): string {
        return esc_url_raw((string) $value);
    }
}

