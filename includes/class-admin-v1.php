<?php
/**
 * Admin functionality for Dynamic Content Pro
 */

if (!defined('ABSPATH')) exit;

class CCP_Adminv1 {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_filter('manage_ccp_content_posts_columns', [$this, 'set_custom_columns']);
        add_action('manage_ccp_content_posts_custom_column', [$this, 'custom_column_content'], 10, 2);
    }
    
    /**
     * Add meta boxes for dynamic content post type
     */
    public function add_meta_boxes() {
        add_meta_box(
            'ccp_react_interface',
            __('Dynamic Content Builder', 'dynamic-content-pro'),
            [$this, 'render_meta_box'],
            'ccp_content',
            'normal',
            'high'
        );
        
        add_meta_box(
            'ccp_shortcode',
            __('Usage', 'dynamic-content-pro'),
            [$this, 'render_shortcode_box'],
            'ccp_content',
            'side',
            'default'
        );
    }
    
    /**
     * Render the main React interface meta box
     */
    public function render_meta_box($post) {
        echo '<div id="dcp-react-root"></div>';
    }
    
    /**
     * Render shortcode usage box
     */
    public function render_shortcode_box($post) {
        ?>
        <div class="dcp-shortcode-box">
            <p><strong><?php _e('Use this shortcode:', 'dynamic-content-pro'); ?></strong></p>
            <input type="text" 
                   value='[ccp_content id="<?php echo $post->ID; ?>"]' 
                   readonly 
                   onclick="this.select();"
                   style="width: 100%; padding: 8px; font-family: monospace; background: #f0f0f1;">
            
            <p style="margin-top: 15px;">
                <strong><?php _e('Or in templates:', 'dynamic-content-pro'); ?></strong>
            </p>
            <textarea readonly 
                      onclick="this.select();"
                      style="width: 100%; padding: 8px; font-family: monospace; background: #f0f0f1; resize: vertical;"
                      rows="2"><?php echo esc_html("<?php echo do_shortcode('[ccp_content id=\"{$post->ID}\"]'); ?>"); ?></textarea>
        </div>
        <?php
    }
    
    /**
     * Add settings page to admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=ccp_content',
            __('Settings', 'dynamic-content-pro'),
            __('Settings', 'dynamic-content-pro'),
            'manage_options',
            'dcp-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (isset($_POST['ccp_save_settings'])) {
            check_admin_referer('ccp_settings_nonce');
            
            $settings = [
                'enable_cache' => isset($_POST['enable_cache']),
                'cache_duration' => intval($_POST['cache_duration'] ?? 3600),
                'enable_analytics' => isset($_POST['enable_analytics'])
            ];
            
            update_option('ccp_settings', $settings);
            echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'dynamic-content-pro') . '</p></div>';
        }
        
        $settings = get_option('ccp_settings', [
            'enable_cache' => true,
            'cache_duration' => 3600,
            'enable_analytics' => false
        ]);
        ?>
        <div class="wrap">
            <h1><?php _e('Dynamic Content Pro Settings', 'dynamic-content-pro'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('ccp_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Caching', 'dynamic-content-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_cache" value="1" <?php checked($settings['enable_cache']); ?>>
                                <?php _e('Cache dynamic content for better performance', 'dynamic-content-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Cache Duration', 'dynamic-content-pro'); ?></th>
                        <td>
                            <input type="number" name="cache_duration" value="<?php echo esc_attr($settings['cache_duration']); ?>" min="60" step="60">
                            <p class="description"><?php _e('Cache duration in seconds (default: 3600 = 1 hour)', 'dynamic-content-pro'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Enable Analytics', 'dynamic-content-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_analytics" value="1" <?php checked($settings['enable_analytics']); ?>>
                                <?php _e('Track which content variants are shown to users', 'dynamic-content-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="ccp_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'dynamic-content-pro'); ?>">
                </p>
            </form>
            
            <hr>
            
            <h2><?php _e('Clear Cache', 'dynamic-content-pro'); ?></h2>
            <p><?php _e('Clear all cached dynamic content.', 'dynamic-content-pro'); ?></p>
            <button type="button" class="button" id="dcp-clear-cache"><?php _e('Clear Cache Now', 'dynamic-content-pro'); ?></button>
            
            <script>
            jQuery(document).ready(function($) {
                $('#dcp-clear-cache').on('click', function() {
                    if (confirm('<?php _e('Are you sure you want to clear all cache?', 'dynamic-content-pro'); ?>')) {
                        $.post(ajaxurl, {
                            action: 'ccp_clear_cache',
                            nonce: '<?php echo wp_create_nonce('ccp_clear_cache'); ?>'
                        }, function(response) {
                            alert(response.data.message);
                        });
                    }
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Custom columns for post list
     */
    public function set_custom_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['shortcode'] = __('Shortcode', 'dynamic-content-pro');
        $new_columns['conditions'] = __('Conditions', 'dynamic-content-pro');
        $new_columns['views'] = __('Views', 'dynamic-content-pro');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Custom column content
     */
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'shortcode':
                echo '<code>[ccp_content id="' . $post_id . '"]</code>';
                break;
                
            case 'conditions':
                $variants = get_post_meta($post_id, '_ccp_variants', true);
                if (is_array($variants)) {
                    $total_conditions = 0;
                    foreach ($variants as $variant) {
                        $total_conditions += count($variant['conditions'] ?? []);
                    }
                    echo $total_conditions . ' ' . __('condition(s)', 'dynamic-content-pro');
                } else {
                    echo '—';
                }
                break;
                
            case 'views':
                $views = get_post_meta($post_id, '_ccp_views', true);
                if (is_array($views)) {
                    echo array_sum($views);
                } else {
                    echo '0';
                }
                break;
        }
    }
}

// Initialize
CCP_Admin::instance();