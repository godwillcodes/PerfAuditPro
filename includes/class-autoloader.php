<?php
/**
 * Autoloader for PerfAudit Pro
 *
 * @package PerfAuditPro
 * @namespace PerfAuditPro
 */

namespace PerfAuditPro;

if (!defined('ABSPATH')) {
    exit;
}

class Autoloader {

    /**
     * Initialize the autoloader
     */
    public static function init() {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Autoload classes
     *
     * @param string $class_name Class name to load
     */
    public static function autoload($class_name) {
        if (strpos($class_name, 'PerfAuditPro\\') !== 0) {
            return;
        }

        $class_name = str_replace('PerfAuditPro\\', '', $class_name);
        $class_name = str_replace('\\', '/', $class_name);
        $class_name = str_replace('_', '-', $class_name);
        $class_name = strtolower($class_name);

        $file_path = PERFAUDIT_PRO_PLUGIN_DIR . 'includes/' . $class_name . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}

