<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
class WC_PV_Dependencies{
    private static $active_plugins;
    
    // Minimum version requirements
    const MIN_WP_VERSION = '6.0';
    const MIN_WC_VERSION = '7.0';
    const MIN_PHP_VERSION = '7.4';
    
    public static function init() {
        self::$active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            self::$active_plugins = array_merge(self::$active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
    }
    
    /**
     * Check if woocommerce exist
     * @return Boolean
     */
    public static function woocommerce_active_check() {
        if (!self::$active_plugins) {
            self::init();
        }
        return in_array('woocommerce/woocommerce.php', self::$active_plugins) || array_key_exists('woocommerce/woocommerce.php', self::$active_plugins);
    }

    /**
     * Check if woocommerce is active
     * @return Boolean
     */
    public static function is_woocommerce_active() {
        return self::woocommerce_active_check();
    }
    
    /**
     * Check if WordPress version meets minimum requirement
     * @return Boolean
     */
    public static function is_wordpress_version_compatible() {
        global $wp_version;
        return version_compare($wp_version, self::MIN_WP_VERSION, '>=');
    }
    
    /**
     * Check if WooCommerce version meets minimum requirement
     * @return Boolean
     */
    public static function is_woocommerce_version_compatible() {
        if (!self::is_woocommerce_active()) {
            return false;
        }
        
        if (!defined('WC_VERSION')) {
            return false;
        }
        
        return version_compare(WC_VERSION, self::MIN_WC_VERSION, '>=');
    }
    
    /**
     * Check if PHP version meets minimum requirement
     * @return Boolean
     */
    public static function is_php_version_compatible() {
        return version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=');
    }
    
    /**
     * Check if all dependencies are met
     * @return Boolean
     */
    public static function are_dependencies_met() {
        return self::is_php_version_compatible() && 
               self::is_wordpress_version_compatible() && 
               self::is_woocommerce_active() && 
               self::is_woocommerce_version_compatible();
    }
    
    /**
     * Get dependency error messages
     * @return Array
     */
    public static function get_dependency_errors() {
        $errors = array();
        
        if (!self::is_php_version_compatible()) {
            $errors[] = sprintf(
                __('Phone Validator for WooCommerce requires PHP version %s or higher. You are running version %s.', 'woo-phone-validator'),
                self::MIN_PHP_VERSION,
                PHP_VERSION
            );
        }
        
        if (!self::is_wordpress_version_compatible()) {
            global $wp_version;
            $errors[] = sprintf(
                __('Phone Validator for WooCommerce requires WordPress version %s or higher. You are running version %s.', 'woo-phone-validator'),
                self::MIN_WP_VERSION,
                $wp_version
            );
        }
        
        if (!self::is_woocommerce_active()) {
            $errors[] = __('Phone Validator for WooCommerce requires WooCommerce to be installed and activated.', 'woo-phone-validator');
        } elseif (!self::is_woocommerce_version_compatible()) {
            $errors[] = sprintf(
                __('Phone Validator for WooCommerce requires WooCommerce version %s or higher. You are running version %s.', 'woo-phone-validator'),
                self::MIN_WC_VERSION,
                defined('WC_VERSION') ? WC_VERSION : 'unknown'
            );
        }
        
        return $errors;
    }
}