<?php
/**
 * Plugin Name: Conditional Content Pro
 * Plugin URI: https://amrshah.github.io/conditional-content-pro
 * Description: Advanced conditional content display - Elementor compatible with React interface
 * Version: 1.0.0
 * Author: Ali Raza
 * Author URI: https://amrshah.github.io
 * Text Domain: conditional-content-pro
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CCP_VERSION', '1.0.0');
define('CCP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CCP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CCP_PLUGIN_BASENAME', __FILE__);




// Main Plugin Class
class Conditional_Content_Pro {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        require_once CCP_PLUGIN_DIR . 'includes/class-condition-engine.php';
        require_once CCP_PLUGIN_DIR . 'includes/class-admin.php';
        require_once CCP_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once CCP_PLUGIN_DIR . 'includes/class-ajax-handler.php';

        if (did_action('elementor/loaded')) {
            require_once CCP_PLUGIN_DIR . 'includes/elementor/class-elementor-integration.php';
        }
    }
    
    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'register_post_type']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // Elementor integration
        add_action('elementor/widgets/register', [$this, 'register_elementor_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_category']);
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('conditional-content-pro', false, dirname(CCP_PLUGIN_BASENAME) . '/languages');
    }
    
    public function register_post_type() {
        $args = [
            'label' => __('Dynamic Content', 'conditional-content-pro'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-randomize',
            'capability_type' => 'post',
            'supports' => ['title', 'editor'],
            'has_archive' => false,
        ];
        register_post_type('ccp_content', $args);
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_style('ccp-frontend', CCP_PLUGIN_URL . 'assets/css/frontend.css', [], CCP_VERSION);
        wp_enqueue_script('ccp-frontend', CCP_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], CCP_VERSION, true);
        
        wp_localize_script('ccp-frontend', 'ccpData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ccp_nonce')
        ]);
    }
    
    public function enqueue_admin_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }
        
        global $post;
        if ($post && $post->post_type === 'ccp_content') {
            wp_enqueue_style('ccp-admin', CCP_PLUGIN_URL . 'assets/css/admin.css', [], CCP_VERSION);

            // React app
            wp_enqueue_script('ccp-admin-react', CCP_PLUGIN_URL . 'build/index.js', ['wp-element', 'wp-components'], CCP_VERSION, true);
            
            wp_localize_script('ccp-admin-react', 'ccpAdmin', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ccp_admin_nonce'),
                'postId' => $post->ID
            ]);
        }
    }
    
    public function add_elementor_category($elements_manager) {
        $elements_manager->add_category(
            'dynamic-content-pro',
            [
                'title' => __('Dynamic Content Pro', 'dynamic-content-pro'),
                'icon' => 'fa fa-plug',
            ]
        );
    }
    
    public function register_elementor_widgets($widgets_manager) {
        if (class_exists('CCP_Elementor_Widget')) {
            $widgets_manager->register(new CCP_Elementor_Widget());
        }
    }
}

// Initialize plugin
function CCP() {
    return Conditional_Content_Pro::instance();
}

CCP();

// Activation hook
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
    
    // Create default options
    if (!get_option('ccp_settings')) {
        update_option('ccp_settings', [
            'enable_cache' => true,
            'cache_duration' => 3600,
            'enable_analytics' => false
        ]);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});