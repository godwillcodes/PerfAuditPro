<?php
/**
 * RUM script enqueue handler
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Frontend
 */

namespace PerfAuditPro\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

class RUM_Enqueue {

    /**
     * Initialize RUM enqueue
     */
    public static function init() {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_rum_script'));
    }

    /**
     * Enqueue RUM tracking script
     */
    public static function enqueue_rum_script() {
        $script_url = PERFAUDIT_PRO_PLUGIN_URL . 'assets/js/rum-tracker.js';
        $api_url = rest_url('perfaudit-pro/v1/rum-intake');
        $nonce = wp_create_nonce('wp_rest');

        wp_enqueue_script(
            'perfaudit-pro-rum',
            $script_url,
            array(),
            PERFAUDIT_PRO_VERSION,
            true
        );

        wp_localize_script('perfaudit-pro-rum', 'PerfAuditPro', array(
            'apiUrl' => esc_url_raw($api_url),
            'nonce' => $nonce,
        ));
    }
}

