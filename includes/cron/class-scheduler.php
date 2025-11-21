<?php
/**
 * Cron scheduler for audit jobs
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Cron
 */

namespace PerfAuditPro\Cron;

if (!defined('ABSPATH')) {
    exit;
}

class Scheduler {

    /**
     * Initialize cron scheduling
     */
    public static function init() {
        add_action('perfaudit_pro_run_audit', array(__CLASS__, 'process_audit_queue'));
        add_action('init', array(__CLASS__, 'schedule_events'));
    }

    /**
     * Schedule cron events
     */
    public static function schedule_events() {
        if (!wp_next_scheduled('perfaudit_pro_run_audit')) {
            wp_schedule_event(time(), 'hourly', 'perfaudit_pro_run_audit');
        }
    }

    /**
     * Process audit queue
     */
    public static function process_audit_queue() {
        require_once PERFAUDIT_PRO_PLUGIN_DIR . 'includes/cron/class-audit-worker.php';
        $worker = new \PerfAuditPro\Cron\Audit_Worker();
        $worker->process_pending_audits();
    }

    /**
     * Enqueue audit job
     *
     * @param string $url URL to audit
     * @param string $audit_type Type of audit
     * @return int|\WP_Error Audit ID
     */
    public static function enqueue_audit($url, $audit_type = 'lighthouse') {
        require_once PERFAUDIT_PRO_PLUGIN_DIR . 'includes/database/class-audit-repository.php';
        $repository = new \PerfAuditPro\Database\Audit_Repository();

        return $repository->create_synthetic_audit($url, $audit_type);
    }
}

