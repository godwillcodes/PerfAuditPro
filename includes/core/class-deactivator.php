<?php
/**
 * Plugin deactivation handler
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Core
 */

namespace PerfAuditPro\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Deactivator {

    /**
     * Deactivate the plugin
     *
     * Cleans up scheduled events and transients.
     * Called by WordPress on plugin deactivation.
     *
     * @return void
     */
    public static function deactivate(): void {
        self::clear_scheduled_events();
        flush_rewrite_rules();
    }

    /**
     * Clear scheduled cron events
     */
    private static function clear_scheduled_events() {
        $timestamp = wp_next_scheduled('perfaudit_pro_run_audit');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'perfaudit_pro_run_audit');
        }
    }
}

