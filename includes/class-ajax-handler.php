<?php
/**
 * AJAX Handler for Dynamic Content Pro
 */

if (!defined('ABSPATH')) exit;

class CCP_Ajax_Handler {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Admin AJAX
        add_action('wp_ajax_ccp_save_content', [$this, 'save_content']);
        add_action('wp_ajax_ccp_get_content', [$this, 'get_content']);
        add_action('wp_ajax_ccp_test_conditions', [$this, 'test_conditions']);
        
        // Public AJAX (for logged-out users too)
        add_action('wp_ajax_nopriv_ccp_track_view', [$this, 'track_view']);
        add_action('wp_ajax_ccp_track_view', [$this, 'track_view']);
    }
    
    /**
     * Save dynamic content configuration
     */
    public function save_content() {
        check_ajax_referer('ccp_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $variants = json_decode(stripslashes($_POST['variants'] ?? '[]'), true);
        $default_content = wp_kses_post($_POST['defaultContent'] ?? '');
        $operator = sanitize_text_field($_POST['conditionOperator'] ?? 'AND');
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }
        
        // Sanitize variants
        $sanitized_variants = [];
        if (is_array($variants)) {
            foreach ($variants as $variant) {
                $sanitized_variants[] = [
                    'id' => intval($variant['id'] ?? time()),
                    'content' => wp_kses_post($variant['content'] ?? ''),
                    'conditions' => $this->sanitize_conditions($variant['conditions'] ?? [])
                ];
            }
        }
        
        // Save to post meta
        update_post_meta($post_id, '_ccp_variants', $sanitized_variants);
        update_post_meta($post_id, '_ccp_default_content', $default_content);
        update_post_meta($post_id, '_ccp_operator', $operator);
        
        // Clear cache
        delete_transient('ccp_content_' . $post_id);
        
        wp_send_json_success([
            'message' => 'Content saved successfully',
            'post_id' => $post_id,
            'variants_count' => count($sanitized_variants)
        ]);
    }
    
    /**
     * Get content configuration
     */
    public function get_content() {
        check_ajax_referer('ccp_admin_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }
        
        $variants = get_post_meta($post_id, '_ccp_variants', true);
        $default_content = get_post_meta($post_id, '_ccp_default_content', true);
        $operator = get_post_meta($post_id, '_ccp_operator', true);
        
        wp_send_json_success([
            'variants' => $variants ?: [],
            'defaultContent' => $default_content ?: '',
            'conditionOperator' => $operator ?: 'AND'
        ]);
    }
    
    /**
     * Test conditions in real-time
     */
    public function test_conditions() {
        check_ajax_referer('ccp_admin_nonce', 'nonce');
        
        $conditions = json_decode(stripslashes($_POST['conditions'] ?? '[]'), true);
        $operator = sanitize_text_field($_POST['operator'] ?? 'AND');
        
        $sanitized_conditions = $this->sanitize_conditions($conditions);
        
        $condition_engine = CCP_Condition_Engine::instance();
        $result = $condition_engine->evaluate($sanitized_conditions, $operator);
        
        wp_send_json_success([
            'result' => $result,
            'message' => $result 
                ? 'Conditions matched! Content would be displayed.' 
                : 'Conditions not matched. Default content would be displayed.'
        ]);
    }
    
    /**
     * Track content views for analytics
     */
    public function track_view() {
        check_ajax_referer('ccp_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $variant_id = intval($_POST['variant_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }
        
        // Get current views
        $views = get_post_meta($post_id, '_ccp_views', true) ?: [];
        
        if (!isset($views[$variant_id])) {
            $views[$variant_id] = 0;
        }
        
        $views[$variant_id]++;
        
        update_post_meta($post_id, '_ccp_views', $views);
        
        wp_send_json_success(['views' => $views[$variant_id]]);
    }
    
    /**
     * Sanitize conditions array
     */
    private function sanitize_conditions($conditions) {
        if (!is_array($conditions)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($conditions as $condition) {
            $sanitized[] = [
                'id' => intval($condition['id'] ?? 0),
                'type' => sanitize_text_field($condition['type'] ?? ''),
                'operator' => sanitize_text_field($condition['operator'] ?? 'equals'),
                'value' => $this->sanitize_condition_value($condition['value'] ?? ''),
                'parameter' => sanitize_text_field($condition['parameter'] ?? ''),
                'start_date' => sanitize_text_field($condition['start_date'] ?? ''),
                'end_date' => sanitize_text_field($condition['end_date'] ?? ''),
            ];
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize condition value based on type
     */
    private function sanitize_condition_value($value) {
        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }
        
        return sanitize_text_field($value);
    }
}

// Initialize
CCP_Ajax_Handler::instance();