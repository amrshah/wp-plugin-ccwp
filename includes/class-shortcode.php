<?php
/**
 * Shortcode Handler for Dynamic Content Pro
 */

if (!defined('ABSPATH')) exit;

class CCP_Shortcode {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('ccp_content', [$this, 'render_content']);
        add_shortcode('ccp_show', [$this, 'render_conditional']);
        add_shortcode('ccp_hide', [$this, 'render_hide']);
    }
    
    /**
     * Main shortcode: [ccp_content id="123"]
     */
    public function render_content($atts) {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts);
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $post_id = intval($atts['id']);
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'ccp_content') {
            return '';
        }
        
        // Get saved conditions and variants
        $variants = get_post_meta($post_id, '_ccp_variants', true);
        $default_content = get_post_meta($post_id, '_ccp_default_content', true);
        $operator = get_post_meta($post_id, '_ccp_operator', true) ?: 'AND';
        
        if (!is_array($variants)) {
            return do_shortcode($post->post_content);
        }
        
        // Evaluate each variant
        $condition_engine = ccp_Condition_Engine::instance();
        
        foreach ($variants as $variant) {
            $conditions = $variant['conditions'] ?? [];
            
            if ($condition_engine->evaluate($conditions, $operator)) {
                $content = $variant['content'] ?? '';
                return do_shortcode($content);
            }
        }
        
        // Return default content if no conditions matched
        return do_shortcode($default_content ?: $post->post_content);
    }
    
    /**
     * Conditional shortcode: [ccp_show role="administrator"]Content[/ccp_show]
     */
    public function render_conditional($atts, $content = null) {
        $atts = shortcode_atts([
            'role' => '',
            'logged_in' => '',
            'country' => '',
            'device' => '',
            'date_from' => '',
            'date_to' => '',
            'page_type' => '',
            'url_param' => '',
            'url_value' => '',
            'operator' => 'AND',
        ], $atts);
        
        $conditions = $this->parse_shortcode_conditions($atts);
        $operator = strtoupper($atts['operator']);
        
        $condition_engine = ccp_Condition_Engine::instance();
        
        if ($condition_engine->evaluate($conditions, $operator)) {
            return do_shortcode($content);
        }
        
        return '';
    }
    
    /**
     * Hide shortcode: [ccp_hide role="subscriber"]Content[/ccp_hide]
     */
    public function render_hide($atts, $content = null) {
        $atts = shortcode_atts([
            'role' => '',
            'logged_in' => '',
            'country' => '',
            'device' => '',
            'date_from' => '',
            'date_to' => '',
            'page_type' => '',
            'url_param' => '',
            'url_value' => '',
            'operator' => 'AND',
        ], $atts);
        
        $conditions = $this->parse_shortcode_conditions($atts);
        $operator = strtoupper($atts['operator']);
        
        $condition_engine = ccp_Condition_Engine::instance();
        
        // Inverted logic - hide if conditions match
        if (!$condition_engine->evaluate($conditions, $operator)) {
            return do_shortcode($content);
        }
        
        return '';
    }
    
    private function parse_shortcode_conditions($atts) {
        $conditions = [];
        
        if (!empty($atts['role'])) {
            $conditions[] = [
                'type' => 'role',
                'operator' => 'equals',
                'value' => explode(',', $atts['role'])
            ];
        }
        
        if (!empty($atts['logged_in'])) {
            $conditions[] = [
                'type' => 'logged_in',
                'operator' => 'equals',
                'value' => $atts['logged_in']
            ];
        }
        
        if (!empty($atts['country'])) {
            $conditions[] = [
                'type' => 'country',
                'operator' => 'equals',
                'value' => explode(',', $atts['country'])
            ];
        }
        
        if (!empty($atts['device'])) {
            $conditions[] = [
                'type' => 'device_type',
                'operator' => 'equals',
                'value' => $atts['device']
            ];
        }
        
        if (!empty($atts['date_from']) || !empty($atts['date_to'])) {
            $conditions[] = [
                'type' => 'date_range',
                'operator' => 'equals',
                'start_date' => $atts['date_from'],
                'end_date' => $atts['date_to']
            ];
        }
        
        if (!empty($atts['page_type'])) {
            $conditions[] = [
                'type' => 'page_type',
                'operator' => 'equals',
                'value' => $atts['page_type']
            ];
        }
        
        if (!empty($atts['url_param'])) {
            $conditions[] = [
                'type' => 'url_parameter',
                'operator' => 'equals',
                'parameter' => $atts['url_param'],
                'value' => $atts['url_value'] ?? ''
            ];
        }
        
        return $conditions;
    }
}

// Initialize
ccp_Shortcode::instance();