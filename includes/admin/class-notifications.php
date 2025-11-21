<?php
/**
 * Notifications and alerts management
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Admin
 */

namespace PerfAuditPro\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Notifications {

    /**
     * Initialize notifications
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
        add_action('wp_ajax_perfaudit_update_notifications', array(__CLASS__, 'update_notifications'));
        add_action('perfaudit_pro_audit_completed', array(__CLASS__, 'check_audit_violations'), 10, 2);
    }

    /**
     * Update notifications
     */
    public static function update_notifications() {
        check_ajax_referer('perfaudit_notifications', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }

        if (!isset($_POST['notifications'])) {
            wp_send_json_error(array('message' => 'Notifications data missing'));
            return;
        }
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data is sanitized after decode
        $notifications = json_decode(stripslashes(wp_unslash($_POST['notifications'])), true);
        
        if (!is_array($notifications)) {
            wp_send_json_error(array('message' => 'Invalid notifications data'));
            return;
        }
        
        // Sanitize notifications array
        $notifications = array_map(function($notification) {
            if (is_array($notification)) {
                return array_map('sanitize_text_field', $notification);
            }
            return sanitize_text_field($notification);
        }, $notifications);
        
        update_option('perfaudit_pro_notifications', $notifications);
        wp_send_json_success();
    }

    /**
     * Add notifications menu
     */
    public static function add_menu() {
        $count = self::get_unread_count();
        $menu_title = __('Notifications', 'site-performance-tracker');
        if ($count > 0) {
            $menu_title .= ' <span class="awaiting-mod">' . $count . '</span>';
        }

        add_submenu_page(
            'site-performance-tracker',
            __('Notifications', 'site-performance-tracker'),
            $menu_title,
            'manage_options',
            'perfaudit-pro-notifications',
            array(__CLASS__, 'render_page')
        );
    }

    /**
     * Get unread notification count
     */
    private static function get_unread_count() {
        $notifications = get_option('perfaudit_pro_notifications', array());
        return count(array_filter($notifications, function($n) {
            return !isset($n['read']) || !$n['read'];
        }));
    }

    /**
     * Check audit for violations
     */
    public static function check_audit_violations($audit_id, $results) {
        if (!get_option('perfaudit_pro_notification_enabled', false)) {
            return;
        }

        require_once PERFAUDIT_PRO_PLUGIN_DIR . 'includes/rules/class-rules-engine.php';
        $rules_engine = new \PerfAuditPro\Rules\Rules_Engine();
        $rules = \PerfAuditPro\Admin\Rules_Page::get_rules();
        $enabled_rules = array_filter($rules, function($rule) {
            return !empty($rule['enabled']);
        });

        $evaluation = $rules_engine->evaluate($results, $enabled_rules);

        if (!$evaluation['passed']) {
            self::create_notification($audit_id, $evaluation);
            self::send_notifications($evaluation);
        }
    }

    /**
     * Create notification
     */
    private static function create_notification($audit_id, $evaluation) {
        $notifications = get_option('perfaudit_pro_notifications', array());
        $notifications[] = array(
            'id' => 'notif_' . time(),
            'type' => 'violation',
            'audit_id' => $audit_id,
            'violations' => $evaluation['violations'],
            'timestamp' => current_time('mysql'),
            'read' => false,
        );
        update_option('perfaudit_pro_notifications', array_slice($notifications, -100)); // Keep last 100
    }

    /**
     * Send notifications
     */
    private static function send_notifications($evaluation) {
        $email = get_option('perfaudit_pro_notification_email', get_option('admin_email'));
        $webhook_url = get_option('perfaudit_pro_webhook_url', '');

        if ($email) {
            self::send_email($email, $evaluation);
        }

        if ($webhook_url) {
            self::send_webhook($webhook_url, $evaluation);
        }
    }

    /**
     * Send email notification
     */
    private static function send_email($to, $evaluation) {
        $subject = 'Site Performance Tracker: Performance Violations Detected';
        $message = "Performance violations detected:\n\n";
        foreach ($evaluation['violations'] as $violation) {
            $message .= "- " . $violation['message'] . "\n";
        }
        wp_mail($to, $subject, $message);
    }

    /**
     * Send webhook
     */
    private static function send_webhook($url, $evaluation) {
        wp_remote_post($url, array(
            'body' => wp_json_encode($evaluation),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 5,
        ));
    }

    /**
     * Render notifications page
     */
    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'site-performance-tracker'));
        }

        include PERFAUDIT_PRO_PLUGIN_DIR . 'includes/admin/views/notifications.php';
    }
}

