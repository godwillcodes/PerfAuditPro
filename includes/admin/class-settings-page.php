<?php
/**
 * Settings page
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Admin
 */

namespace PerfAuditPro\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_Page {

    /**
     * Initialize settings page
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_settings_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }

    /**
     * Add settings submenu
     */
    public static function add_settings_menu() {
        add_submenu_page(
            'site-performance-tracker',
            __('Settings', 'site-performance-tracker'),
            __('Settings', 'site-performance-tracker'),
            'manage_options',
            'perfaudit-pro-settings',
            array(__CLASS__, 'render_page')
        );
    }

    /**
     * Register settings
     */
    public static function register_settings() {
        // API Settings
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_psi_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_api_token', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        // Default Thresholds
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_threshold_lcp', array(
            'type' => 'number',
            'sanitize_callback' => function($value) {
                return floatval($value);
            },
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_threshold_fid', array(
            'type' => 'number',
            'sanitize_callback' => function($value) {
                return floatval($value);
            },
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_threshold_cls', array(
            'type' => 'number',
            'sanitize_callback' => function($value) {
                return floatval($value);
            },
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_threshold_fcp', array(
            'type' => 'number',
            'sanitize_callback' => function($value) {
                return floatval($value);
            },
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_threshold_ttfb', array(
            'type' => 'number',
            'sanitize_callback' => function($value) {
                return floatval($value);
            },
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_threshold_performance_score', array(
            'type' => 'number',
            'sanitize_callback' => function($value) {
                return floatval($value);
            },
        ));

        // Notification Settings
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_notification_email', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_notification_enabled', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_webhook_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ));

        // Worker Settings
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_worker_interval', array(
            'type' => 'integer',
            'sanitize_callback' => function($value) {
                return absint($value);
            },
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_worker_max_concurrent', array(
            'type' => 'integer',
            'sanitize_callback' => function($value) {
                return absint($value);
            },
        ));

        // RUM Settings
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_rum_enabled', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_rum_sample_rate', array(
            'type' => 'number',
            'sanitize_callback' => function($value) {
                $value = floatval($value);
                return max(0, min(1, $value)); // Clamp between 0 and 1
            },
        ));

        // Data Retention Settings
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_audit_retention_days', array(
            'type' => 'integer',
            'sanitize_callback' => function($value) {
                $value = absint($value);
                return max(7, min(365, $value)); // Clamp between 7 and 365 days
            },
            'default' => 90,
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_rum_retention_days', array(
            'type' => 'integer',
            'sanitize_callback' => function($value) {
                $value = absint($value);
                return max(7, min(365, $value)); // Clamp between 7 and 365 days
            },
            'default' => 90,
        ));
        register_setting('perfaudit_pro_settings', 'perfaudit_pro_auto_cleanup', array(
            'type' => 'boolean',
            'default' => true,
        ));
    }

    /**
     * Render settings page
     */
    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'site-performance-tracker'));
        }

        include PERFAUDIT_PRO_PLUGIN_DIR . 'includes/admin/views/settings.php';
    }
}

