<?php
/**
 * Elementor Widget for Dynamic Content Pro
 */

if (!defined('ABSPATH')) exit;

class CCP_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'dynamic-content-pro';
    }
    
    public function get_title() {
        return __('Dynamic Content Pro', 'dynamic-content-pro');
    }
    
    public function get_icon() {
        return 'eicon-code-highlight';
    }
    
    public function get_categories() {
        return ['dynamic-content-pro'];
    }
    
    protected function register_controls() {
        
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'dynamic-content-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'content_source',
            [
                'label' => __('Content Source', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'custom' => __('Custom', 'dynamic-content-pro'),
                    'saved' => __('Saved Dynamic Content', 'dynamic-content-pro'),
                ],
                'default' => 'custom',
            ]
        );
        
        $this->add_control(
            'saved_content_id',
            [
                'label' => __('Select Saved Content', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_saved_content_options(),
                'condition' => [
                    'content_source' => 'saved',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Conditions Tab
        $this->start_controls_section(
            'conditions_section',
            [
                'label' => __('Display Conditions', 'dynamic-content-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'content_source' => 'custom',
                ],
            ]
        );
        
        $repeater = new \Elementor\Repeater();
        
        $repeater->add_control(
            'condition_type',
            [
                'label' => __('Condition Type', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'role' => __('User Role', 'dynamic-content-pro'),
                    'logged_in' => __('Login Status', 'dynamic-content-pro'),
                    'country' => __('Country', 'dynamic-content-pro'),
                    'device_type' => __('Device Type', 'dynamic-content-pro'),
                    'date_range' => __('Date Range', 'dynamic-content-pro'),
                    'page_type' => __('Page Type', 'dynamic-content-pro'),
                    'url_parameter' => __('URL Parameter', 'dynamic-content-pro'),
                    'cart_total' => __('Cart Total', 'dynamic-content-pro'),
                    'cookie' => __('Cookie', 'dynamic-content-pro'),
                ],
                'default' => 'role',
            ]
        );
        
        $repeater->add_control(
            'operator',
            [
                'label' => __('Operator', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'equals' => __('Equals', 'dynamic-content-pro'),
                    'not_equals' => __('Not Equals', 'dynamic-content-pro'),
                    'contains' => __('Contains', 'dynamic-content-pro'),
                    'greater_than' => __('Greater Than', 'dynamic-content-pro'),
                    'less_than' => __('Less Than', 'dynamic-content-pro'),
                ],
                'default' => 'equals',
            ]
        );
        
        $repeater->add_control(
            'value',
            [
                'label' => __('Value', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            ]
        );
        
        $this->add_control(
            'conditions',
            [
                'label' => __('Conditions', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ condition_type }}} {{{ operator }}} {{{ value }}}',
            ]
        );
        
        $this->add_control(
            'condition_operator',
            [
                'label' => __('Condition Logic', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'AND' => __('Match ALL (AND)', 'dynamic-content-pro'),
                    'OR' => __('Match ANY (OR)', 'dynamic-content-pro'),
                ],
                'default' => 'AND',
            ]
        );
        
        $this->end_controls_section();
        
        // Variant A
        $this->start_controls_section(
            'variant_a_section',
            [
                'label' => __('Content Variant A', 'dynamic-content-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'content_source' => 'custom',
                ],
            ]
        );
        
        $this->add_control(
            'variant_a_content',
            [
                'label' => __('Content', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __('Content when conditions match', 'dynamic-content-pro'),
            ]
        );
        
        $this->end_controls_section();
        
        // Variant B (Default)
        $this->start_controls_section(
            'variant_b_section',
            [
                'label' => __('Default Content', 'dynamic-content-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'content_source' => 'custom',
                ],
            ]
        );
        
        $this->add_control(
            'variant_b_content',
            [
                'label' => __('Content', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __('Default content when conditions don\'t match', 'dynamic-content-pro'),
            ]
        );
        
        $this->end_controls_section();
        
        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'dynamic-content-pro'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ccp-content' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .ccp-content',
            ]
        );
        
        $this->add_responsive_control(
            'padding',
            [
                'label' => __('Padding', 'dynamic-content-pro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .ccp-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if ($settings['content_source'] === 'saved' && !empty($settings['saved_content_id'])) {
            // Render saved content
            echo do_shortcode('[ccp_content id="' . esc_attr($settings['saved_content_id']) . '"]');
        } else {
            // Evaluate custom conditions
            $conditions = $settings['conditions'] ?? [];
            $operator = $settings['condition_operator'] ?? 'AND';
            
            $condition_engine = CCP_Condition_Engine::instance();
            $show_variant_a = $condition_engine->evaluate($conditions, $operator);
            
            $content = $show_variant_a ? $settings['variant_a_content'] : $settings['variant_b_content'];
            
            echo '<div class="ccp-content">';
            echo wp_kses_post($content);
            echo '</div>';
        }
    }
    
    protected function content_template() {
        ?>
        <#
        var content = '';
        if (settings.content_source === 'custom') {
            // In editor, show variant A by default
            content = settings.variant_a_content;
        }
        #>
        <div class="ccp-content">{{{ content }}}</div>
        <?php
    }
    
    private function get_saved_content_options() {
        $options = [];
        $posts = get_posts([
            'post_type' => 'ccp_content',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        
        foreach ($posts as $post) {
            $options[$post->ID] = $post->post_title;
        }
        
        return $options;
    }
}

// Register Elementor Widget
class CCP_Elementor_Integration {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'add_category']);
    }
    
    public function register_widgets($widgets_manager) {
        require_once CCP_PLUGIN_DIR . 'includes/elementor/class-elementor-widget.php';
        $widgets_manager->register(new CCP_Elementor_Widget());
    }
    
    public function add_category($elements_manager) {
        $elements_manager->add_category(
            'dynamic-content-pro',
            [
                'title' => __('Dynamic Content Pro', 'dynamic-content-pro'),
                'icon' => 'fa fa-plug',
            ]
        );
    }
}

CCP_Elementor_Integration::instance();