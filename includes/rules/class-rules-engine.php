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
     * @param array $metrics Metrics to evaluate
     * @param array $rules Rules configuration
     * @return array Evaluation results
     */
    public function evaluate($metrics, $rules) {
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
     * @param array $metrics Metrics
     * @param array $rule Rule configuration
     * @return array Evaluation result
     */
    private function evaluate_rule($metrics, $rule) {
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
     * @param float $value Value to compare
     * @param float $threshold Threshold
     * @param string $operator Comparison operator
     * @return bool True if condition is met
     */
    private function compare($value, $threshold, $operator) {
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
     * @param string $metric Metric name
     * @param float $value Actual value
     * @param float $threshold Threshold
     * @param string $operator Operator
     * @return string Message
     */
    private function generate_message($metric, $value, $threshold, $operator) {
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
     * @param array $results Evaluation results
     * @param array $actions Actions configuration
     * @return array Action results
     */
    public function execute_actions($results, $actions) {
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
     * @param array $action Action configuration
     * @param array $results Evaluation results
     * @return array|null Action result
     */
    private function execute_action($action, $results) {
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
     * @param array $action Action config
     * @param array $results Evaluation results
     * @return array Action result
     */
    private function send_email($action, $results) {
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
     * @param array $action Action config
     * @param array $results Evaluation results
     * @return array Action result
     */
    private function log_violation($action, $results) {
        error_log('PerfAudit Pro Violation: ' . wp_json_encode($results));

        return array(
            'type' => 'log',
            'success' => true,
        );
    }

    /**
     * Send webhook
     *
     * @param array $action Action config
     * @param array $results Evaluation results
     * @return array Action result
     */
    private function send_webhook($action, $results) {
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
     * @param array $results Evaluation results
     * @return string Formatted message
     */
    private function format_email_message($results) {
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

