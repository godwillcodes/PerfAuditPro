<?php
/**
 * Input validation utility
 *
 * Provides type-safe validation functions for all input boundaries.
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Utils
 * @since 1.0.0
 */

namespace PerfAuditPro\Utils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validator class for input validation
 */
class Validator {

    /**
     * Validate URL
     *
     * @param mixed $url URL to validate
     * @return bool True if valid
     */
    public static function is_valid_url($url): bool {
        if (!is_string($url)) {
            return false;
        }

        $url = trim($url);
        if (empty($url)) {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate and sanitize URL
     *
     * @param mixed $url URL to validate
     * @return string|null Sanitized URL or null if invalid
     */
    public static function validate_url($url): ?string {
        if (!self::is_valid_url($url)) {
            return null;
        }

        return esc_url_raw($url);
    }

    /**
     * Validate device type
     *
     * @param mixed $device Device type
     * @return bool True if valid
     */
    public static function is_valid_device($device): bool {
        return is_string($device) && in_array(strtolower($device), ['desktop', 'mobile'], true);
    }

    /**
     * Validate and sanitize device type
     *
     * @param mixed $device Device type
     * @param string $default Default value if invalid
     * @return string Validated device type
     */
    public static function validate_device($device, string $default = 'desktop'): string {
        if (!self::is_valid_device($device)) {
            return $default;
        }

        return strtolower($device);
    }

    /**
     * Validate audit type
     *
     * @param mixed $audit_type Audit type
     * @return bool True if valid
     */
    public static function is_valid_audit_type($audit_type): bool {
        return is_string($audit_type) && in_array(strtolower($audit_type), ['lighthouse'], true);
    }

    /**
     * Validate and sanitize audit type
     *
     * @param mixed $audit_type Audit type
     * @param string $default Default value if invalid
     * @return string Validated audit type
     */
    public static function validate_audit_type($audit_type, string $default = 'lighthouse'): string {
        if (!self::is_valid_audit_type($audit_type)) {
            return $default;
        }

        return strtolower($audit_type);
    }

    /**
     * Validate status
     *
     * @param mixed $status Status value
     * @return bool True if valid
     */
    public static function is_valid_status($status): bool {
        $valid_statuses = ['pending', 'processing', 'completed', 'failed'];
        return is_string($status) && in_array(strtolower($status), $valid_statuses, true);
    }

    /**
     * Validate positive integer
     *
     * @param mixed $value Value to validate
     * @param int $min Minimum value
     * @param int|null $max Maximum value (null for no limit)
     * @return bool True if valid
     */
    public static function is_positive_int($value, int $min = 1, ?int $max = null): bool {
        if (!is_numeric($value)) {
            return false;
        }

        $int_value = (int) $value;
        
        if ($int_value < $min) {
            return false;
        }

        if ($max !== null && $int_value > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate and sanitize positive integer
     *
     * @param mixed $value Value to validate
     * @param int $default Default value if invalid
     * @param int $min Minimum value
     * @param int|null $max Maximum value
     * @return int Validated integer
     */
    public static function validate_positive_int($value, int $default, int $min = 1, ?int $max = null): int {
        if (!self::is_positive_int($value, $min, $max)) {
            return $default;
        }

        return (int) $value;
    }

    /**
     * Validate non-negative float
     *
     * @param mixed $value Value to validate
     * @return bool True if valid
     */
    public static function is_non_negative_float($value): bool {
        if (!is_numeric($value)) {
            return false;
        }

        return (float) $value >= 0.0;
    }

    /**
     * Validate and sanitize non-negative float
     *
     * @param mixed $value Value to validate
     * @param float $default Default value if invalid
     * @return float Validated float
     */
    public static function validate_non_negative_float($value, float $default = 0.0): float {
        if (!self::is_non_negative_float($value)) {
            return $default;
        }

        return (float) $value;
    }

    /**
     * Validate array of audit IDs
     *
     * @param mixed $ids Array of IDs
     * @return bool True if valid
     */
    public static function is_valid_audit_ids($ids): bool {
        if (!is_array($ids)) {
            return false;
        }

        if (empty($ids)) {
            return false;
        }

        foreach ($ids as $id) {
            if (!self::is_positive_int($id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate and sanitize array of audit IDs
     *
     * @param mixed $ids Array of IDs
     * @return array<int> Validated array of IDs
     */
    public static function validate_audit_ids($ids): array {
        if (!is_array($ids)) {
            return [];
        }

        $valid_ids = [];
        foreach ($ids as $id) {
            if (self::is_positive_int($id)) {
                $valid_ids[] = (int) $id;
            }
        }

        return array_unique($valid_ids);
    }
}

