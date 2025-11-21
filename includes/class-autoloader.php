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
     *
     * Registers the autoloader with PHP's spl_autoload_register.
     *
     * @return void
     */
    public static function init(): void {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Autoload classes
     *
     * Maps class names to file paths based on namespace and class name.
     * Only handles classes in the PerfAuditPro namespace.
     *
     * @param string $class_name Fully qualified class name
     * @return void
     */
    public static function autoload(string $class_name): void {
        if (strpos($class_name, 'PerfAuditPro\\') !== 0) {
            return;
        }

        $class_name = str_replace('PerfAuditPro\\', '', $class_name);
        $parts = explode('\\', $class_name);
        $class_part = array_pop($parts);
        $path_part = implode('/', $parts);
        
        // Convert class name: Rest_API -> rest-api
        $class_file = str_replace('_', '-', $class_part);
        $class_file = strtolower($class_file);
        
        // Build path: API/Rest_API -> api/class-rest-api.php
        $path = '';
        if (!empty($path_part)) {
            $path = strtolower($path_part) . '/';
        }
        
        $file_path = PERFAUDIT_PRO_PLUGIN_DIR . 'includes/' . $path . 'class-' . $class_file . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }
}

