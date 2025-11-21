<?php
/**
 * Admin dashboard view
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Admin
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap perfaudit-pro-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="perfaudit-pro-card" style="margin-bottom: 20px;">
        <h2><?php esc_html_e('Create New Audit', 'perfaudit-pro'); ?></h2>
        <p><?php esc_html_e('Create a new synthetic audit. Note: You need an external worker to process audits. See WORKER_SETUP.md for details.', 'perfaudit-pro'); ?></p>
        <form id="create-audit-form" style="display: flex; gap: 10px; align-items: flex-end;">
            <div style="flex: 1;">
                <label for="audit-url" style="display: block; margin-bottom: 5px; font-weight: 600;"><?php esc_html_e('URL to Audit', 'perfaudit-pro'); ?></label>
                <input type="url" id="audit-url" name="url" value="<?php echo esc_attr(home_url()); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
            </div>
            <div>
                <button type="submit" class="button button-primary" style="height: 36px;"><?php esc_html_e('Create Audit', 'perfaudit-pro'); ?></button>
            </div>
        </form>
        <div id="create-audit-message" style="margin-top: 10px;"></div>
    </div>

    <div class="perfaudit-pro-grid">
        <div class="perfaudit-pro-card">
            <h2><?php esc_html_e('Synthetic Audits', 'perfaudit-pro'); ?></h2>
            <div class="perfaudit-pro-chart-container">
                <canvas id="audit-timeline-chart"></canvas>
            </div>
        </div>

        <div class="perfaudit-pro-card">
            <h2><?php esc_html_e('Performance Score Distribution', 'perfaudit-pro'); ?></h2>
            <div class="perfaudit-pro-chart-container">
                <canvas id="score-distribution-chart"></canvas>
            </div>
        </div>

        <div class="perfaudit-pro-card">
            <h2><?php esc_html_e('RUM Metrics - LCP', 'perfaudit-pro'); ?></h2>
            <div class="perfaudit-pro-chart-container">
                <canvas id="rum-lcp-chart"></canvas>
            </div>
        </div>

        <div class="perfaudit-pro-card">
            <h2><?php esc_html_e('RUM Metrics - CLS', 'perfaudit-pro'); ?></h2>
            <div class="perfaudit-pro-chart-container">
                <canvas id="rum-cls-chart"></canvas>
            </div>
        </div>
    </div>

    <div class="perfaudit-pro-card">
        <h2><?php esc_html_e('Recent Audits', 'perfaudit-pro'); ?></h2>
        <div id="recent-audits-table">
            <p><?php esc_html_e('Loading...', 'perfaudit-pro'); ?></p>
        </div>
    </div>

    <div class="perfaudit-pro-card" style="margin-top: 20px; background: #f0f6fc; border-left: 4px solid #2271b1;">
        <h3 style="margin-top: 0;"><?php esc_html_e('How Synthetic Audits Work', 'perfaudit-pro'); ?></h3>
        <ol style="line-height: 1.8;">
            <li><strong><?php esc_html_e('Create Audit', 'perfaudit-pro'); ?></strong>: <?php esc_html_e('Use the form above to create a new audit. This creates a "pending" record in the database.', 'perfaudit-pro'); ?></li>
            <li><strong><?php esc_html_e('External Worker', 'perfaudit-pro'); ?></strong>: <?php esc_html_e('An external worker (Node.js/Puppeteer) polls for pending audits and runs Lighthouse tests.', 'perfaudit-pro'); ?></li>
            <li><strong><?php esc_html_e('Submit Results', 'perfaudit-pro'); ?></strong>: <?php esc_html_e('The worker submits results back via REST API, and the dashboard displays them.', 'perfaudit-pro'); ?></li>
        </ol>
        <p><strong><?php esc_html_e('Note', 'perfaudit-pro'); ?>:</strong> <?php esc_html_e('Without an external worker, audits will remain in "pending" status. See WORKER_SETUP.md for setup instructions.', 'perfaudit-pro'); ?></p>
    </div>
</div>

