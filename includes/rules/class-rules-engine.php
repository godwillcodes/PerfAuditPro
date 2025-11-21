<?php
/**
 * Rules engine for performance threshold evaluation
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro\Rules
 */

namespace PerfAuditPro\Rules;

if (!defined('ABSPATH')) {
    exit;
}

class Rules_Engine {

    /**
     * Evaluate rules against metrics
     *
     * @param array<string, mixed> $metrics Metrics to evaluate
     * @param array<int, array<string, mixed>> $rules Rules configuration
     * @return array{passed: bool, violations: array<int, array<string, mixed>>, warnings: array<int, array<string, mixed>>} Evaluation results
     */
    public function evaluate(array $metrics, array $rules): array {
        $results = array(
            'passed' => true,
            'violations' => array(),
            'warnings' => array(),
        );

        foreach ($rules as $rule) {
            $evaluation = $this->evaluate_rule($metrics, $rule);

            if ($evaluation['status'] === 'fail') {
                $results['passed'] = false;
                $results['violations'][] = $evaluation;
            } elseif ($evaluation['status'] === 'warning') {
                $results['warnings'][] = $evaluation;
            }
        }

        return $results;
    }

    /**
     * Evaluate a single rule
     *
     * @param array<string, mixed> $metrics Metrics
     * @param array<string, mixed> $rule Rule configuration
     * @return array<string, mixed> Evaluation result
     */
    private function evaluate_rule(array $metrics, array $rule): array {
        $metric_name = $rule['metric'];
        $threshold = $rule['threshold'];
        $operator = $rule['operator'] ?? 'gt';
        $enforcement = $rule['enforcement'] ?? 'soft';

        if (!isset($metrics[$metric_name])) {
            return array(
                'status' => 'skip',
                'metric' => $metric_name,
                'message' => 'Metric not available',
            );
        }

        $value = floatval($metrics[$metric_name]);
        $comparison = $this->compare($value, $threshold, $operator);

        if ($comparison) {
            return array(
                'status' => $enforcement === 'hard' ? 'fail' : 'warning',
                'metric' => $metric_name,
                'value' => $value,
                'threshold' => $threshold,
                'operator' => $operator,
                'enforcement' => $enforcement,
                'message' => $this->generate_message($metric_name, $value, $threshold, $operator),
            );
        }

        return array(
            'status' => 'pass',
            'metric' => $metric_name,
            'value' => $value,
        );
    }

    /**
     * Compare value with threshold
     *
     * Pure function for comparing values with operators.
     *
     * @param float $value Value to compare
     * @param float $threshold Threshold value
     * @param string $operator Comparison operator (gt, gte, lt, lte, eq, neq)
     * @return bool True if condition is met (violation condition)
     */
    private function compare(float $value, float $threshold, string $operator): bool {
        switch ($operator) {
            case 'gt':
                return $value > $threshold;
            case 'gte':
                return $value >= $threshold;
            case 'lt':
                return $value < $threshold;
            case 'lte':
                return $value <= $threshold;
            case 'eq':
                return abs($value - $threshold) < 0.0001;
            case 'neq':
                return abs($value - $threshold) >= 0.0001;
            default:
                return false;
        }
    }

    /**
     * Generate violation message
     *
     * Pure function for generating human-readable violation messages.
     *
     * @param string $metric Metric name
     * @param float $value Actual value
     * @param float $threshold Threshold value
     * @param string $operator Comparison operator
     * @return string Human-readable violation message
     */
    private function generate_message(string $metric, float $value, float $threshold, string $operator): string {
        $metric_labels = array(
            'lcp' => 'Largest Contentful Paint',
            'fid' => 'First Input Delay',
            'cls' => 'Cumulative Layout Shift',
            'fcp' => 'First Contentful Paint',
            'ttfb' => 'Time to First Byte',
            'performance_score' => 'Performance Score',
        );

        $metric_label = $metric_labels[$metric] ?? $metric;
        $operator_labels = array(
            'gt' => 'greater than',
            'gte' => 'greater than or equal to',
            'lt' => 'less than',
            'lte' => 'less than or equal to',
        );

        $operator_label = $operator_labels[$operator] ?? $operator;

        return sprintf(
            '%s is %s (value: %.2f, threshold: %.2f)',
            $metric_label,
            $operator_label,
            $value,
            $threshold
        );
    }

    /**
     * Execute enforcement actions
     *
     * @param array<string, mixed> $results Evaluation results
     * @param array<int, array<string, mixed>> $actions Actions configuration
     * @return array<int, array<string, mixed>> Action results
     */
    public function execute_actions(array $results, array $actions): array {
        $action_results = array();

        if (!$results['passed'] && !empty($results['violations'])) {
            foreach ($actions as $action) {
                $action_result = $this->execute_action($action, $results);
                if ($action_result) {
                    $action_results[] = $action_result;
                }
            }
        }

        return $action_results;
    }

    /**
     * Execute a single action
     *
     * @param array<string, mixed> $action Action configuration
     * @param array<string, mixed> $results Evaluation results
     * @return array<string, mixed>|null Action result or null if action type is unknown
     */
    private function execute_action(array $action, array $results): ?array {
        $type = $action['type'] ?? '';

        switch ($type) {
            case 'email':
                return $this->send_email($action, $results);
            case 'log':
                return $this->log_violation($action, $results);
            case 'webhook':
                return $this->send_webhook($action, $results);
            default:
                return null;
        }
    }

    /**
     * Send email notification
     *
     * @param array<string, mixed> $action Action configuration
     * @param array<string, mixed> $results Evaluation results
     * @return array<string, mixed> Action result
     */
    private function send_email(array $action, array $results): array {
        $to = $action['to'] ?? get_option('admin_email');
        $subject = $action['subject'] ?? 'Performance Audit Violation';
        $message = $this->format_email_message($results);

        $sent = wp_mail($to, $subject, $message);

        return array(
            'type' => 'email',
            'success' => $sent,
            'recipient' => $to,
        );
    }

    /**
     * Log violation
     *
     * @param array<string, mixed> $action Action configuration
     * @param array<string, mixed> $results Evaluation results
     * @return array<string, mixed> Action result
     */
    private function log_violation(array $action, array $results): array {
        require_once PERFAUDIT_PRO_PLUGIN_DIR . 'includes/utils/class-logger.php';
        \PerfAuditPro\Utils\Logger::warning('Performance violation detected', $results);

        return array(
            'type' => 'log',
            'success' => true,
        );
    }

    /**
     * Send webhook
     *
     * @param array<string, mixed> $action Action configuration
     * @param array<string, mixed> $results Evaluation results
     * @return array<string, mixed> Action result
     */
    private function send_webhook(array $action, array $results): array {
        $url = $action['url'] ?? '';

        if (empty($url)) {
            return array(
                'type' => 'webhook',
                'success' => false,
                'error' => 'Webhook URL not configured',
            );
        }

        $response = wp_remote_post($url, array(
            'body' => wp_json_encode($results),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 5,
        ));

        $success = !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;

        return array(
            'type' => 'webhook',
            'success' => $success,
            'url' => $url,
        );
    }

    /**
     * Format email message
     *
     * Pure function for formatting email messages from evaluation results.
     *
     * @param array<string, mixed> $results Evaluation results
     * @return string Formatted email message
     */
    private function format_email_message(array $results): string {
        $message = "Performance audit violations detected:\n\n";

        foreach ($results['violations'] as $violation) {
            $message .= sprintf(
                "- %s: %s\n",
                $violation['metric'],
                $violation['message']
            );
        }

        return $message;
    }
}

