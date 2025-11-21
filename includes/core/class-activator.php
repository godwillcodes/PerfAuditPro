<?php
/**
 * Plugin activation handler
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Core
 */

namespace PerfAuditPro\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Activator {

    /**
     * Activate the plugin
     *
     * Creates database tables and sets default options.
     * Called by WordPress on plugin activation.
     *
     * @return void
     */
    public static function activate(): void {
        self::create_tables();
        self::set_default_options();
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     *
     * @return void
     */
    private static function create_tables(): void {
        require_once PERFAUDIT_PRO_PLUGIN_DIR . 'includes/database/class-schema.php';
        \PerfAuditPro\Database\Schema::create_tables();
    }

    /**
     * Set default plugin options
     *
     * Generates API token and configures worker if not already set.
     *
     * @return void
     */
    private static function set_default_options(): void {
        if (!get_option('perfaudit_pro_version')) {
            add_option('perfaudit_pro_version', PERFAUDIT_PRO_VERSION);
        }

        // Auto-generate API token if not exists
        if (!get_option('perfaudit_pro_api_token')) {
            $token = bin2hex(random_bytes(32));
            add_option('perfaudit_pro_api_token', $token);
        }

        // Auto-configure worker
        require_once PERFAUDIT_PRO_PLUGIN_DIR . 'includes/admin/class-worker-manager.php';
        \PerfAuditPro\Admin\Worker_Manager::auto_configure();
    }
}

