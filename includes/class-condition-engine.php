<?php
/**
 * Condition Engine - Evaluates all display conditions
 */

if (!defined('ABSPATH')) exit;

class CCP_Condition_Engine {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Available condition types
     */
    public function get_condition_types() {
        return [
            'user' => [
                'role' => __('User Role', 'dynamic-content-pro'),
                'logged_in' => __('Login Status', 'dynamic-content-pro'),
                'user_meta' => __('User Meta', 'dynamic-content-pro'),
            ],
            'location' => [
                'country' => __('Country', 'dynamic-content-pro'),
                'city' => __('City', 'dynamic-content-pro'),
                'ip_address' => __('IP Address', 'dynamic-content-pro'),
            ],
            'device' => [
                'device_type' => __('Device Type', 'dynamic-content-pro'),
                'browser' => __('Browser', 'dynamic-content-pro'),
                'os' => __('Operating System', 'dynamic-content-pro'),
            ],
            'time' => [
                'date_range' => __('Date Range', 'dynamic-content-pro'),
                'day_of_week' => __('Day of Week', 'dynamic-content-pro'),
                'time_range' => __('Time Range', 'dynamic-content-pro'),
            ],
            'page' => [
                'page_type' => __('Page Type', 'dynamic-content-pro'),
                'url_parameter' => __('URL Parameter', 'dynamic-content-pro'),
                'referrer' => __('Referrer', 'dynamic-content-pro'),
            ],
            'woocommerce' => [
                'cart_total' => __('Cart Total', 'dynamic-content-pro'),
                'cart_items' => __('Cart Items', 'dynamic-content-pro'),
                'purchased_product' => __('Purchased Product', 'dynamic-content-pro'),
            ],
            'advanced' => [
                'cookie' => __('Cookie Value', 'dynamic-content-pro'),
                'session' => __('Session Variable', 'dynamic-content-pro'),
                'custom_code' => __('Custom PHP Code', 'dynamic-content-pro'),
                'ab_test' => __('A/B Test', 'dynamic-content-pro'),
            ]
        ];
    }
    
    /**
     * Evaluate conditions
     */
    public function evaluate($conditions, $operator = 'AND') {
        if (empty($conditions)) {
            return true;
        }
        
        $results = [];
        foreach ($conditions as $condition) {
            $results[] = $this->evaluate_single_condition($condition);
        }
        
        if ($operator === 'AND') {
            return !in_array(false, $results, true);
        } else {
            return in_array(true, $results, true);
        }
    }
    
    private function evaluate_single_condition($condition) {
        $type = $condition['type'] ?? '';
        $operator = $condition['operator'] ?? 'equals';
        $value = $condition['value'] ?? '';
        
        switch ($type) {
            case 'role':
                return $this->check_user_role($value, $operator);
            
            case 'logged_in':
                return $this->check_logged_in($value);
            
            case 'country':
                return $this->check_country($value, $operator);
            
            case 'device_type':
                return $this->check_device_type($value);
            
            case 'date_range':
                return $this->check_date_range($condition);
            
            case 'page_type':
                return $this->check_page_type($value);
            
            case 'url_parameter':
                return $this->check_url_parameter($condition);
            
            case 'cart_total':
                return $this->check_cart_total($value, $operator);
            
            case 'cookie':
                return $this->check_cookie($condition);
            
            case 'ab_test':
                return $this->check_ab_test($condition);
            
            case 'custom_code':
                return $this->evaluate_custom_code($value);
            
            default:
                return apply_filters('ccp_evaluate_custom_condition', false, $condition);
        }
    }
    
    // User conditions
    private function check_user_role($roles, $operator) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user = wp_get_current_user();
        $user_roles = $user->roles;
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $has_role = !empty(array_intersect($roles, $user_roles));
        
        return $operator === 'not_equals' ? !$has_role : $has_role;
    }
    
    private function check_logged_in($value) {
        $is_logged_in = is_user_logged_in();
        return $value === 'logged_in' ? $is_logged_in : !$is_logged_in;
    }
    
    // Location conditions
    private function check_country($countries, $operator) {
        $user_country = $this->get_user_country();
        
        if (!is_array($countries)) {
            $countries = [$countries];
        }
        
        $match = in_array($user_country, $countries);
        return $operator === 'not_equals' ? !$match : $match;
    }
    
    private function get_user_country() {
        // Use IP geolocation API or CloudFlare headers
        if (isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            return $_SERVER['HTTP_CF_IPCOUNTRY'];
        }
        
        // Fallback to IP lookup
        $ip = $this->get_user_ip();
        $country = get_transient('ccp_geo_' . md5($ip));
        
        if ($country === false) {
            // Use free IP geolocation service
            $response = wp_remote_get('http://ip-api.com/json/' . $ip);
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $country = $data['countryCode'] ?? 'US';
                set_transient('ccp_geo_' . md5($ip), $country, DAY_IN_SECONDS);
            }
        }
        
        return $country ?: 'US';
    }
    
    private function get_user_ip() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
    
    // Device conditions
    private function check_device_type($type) {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if ($type === 'mobile') {
            return wp_is_mobile();
        } elseif ($type === 'tablet') {
            return preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $ua);
        } else {
            return !wp_is_mobile();
        }
    }
    
    // Time conditions
    private function check_date_range($condition) {
        $start = strtotime($condition['start_date'] ?? '');
        $end = strtotime($condition['end_date'] ?? '');
        $now = current_time('timestamp');
        
        return $now >= $start && $now <= $end;
    }
    
    // Page conditions
    private function check_page_type($type) {
        switch ($type) {
            case 'home':
                return is_front_page();
            case 'single':
                return is_single();
            case 'page':
                return is_page();
            case 'archive':
                return is_archive();
            case 'search':
                return is_search();
            case 'category':
                return is_category();
            default:
                return false;
        }
    }
    
    private function check_url_parameter($condition) {
        $param = $condition['parameter'] ?? '';
        $value = $condition['value'] ?? '';
        $operator = $condition['operator'] ?? 'equals';
        
        $actual = $_GET[$param] ?? '';
        
        switch ($operator) {
            case 'equals':
                return $actual === $value;
            case 'not_equals':
                return $actual !== $value;
            case 'contains':
                return strpos($actual, $value) !== false;
            case 'exists':
                return isset($_GET[$param]);
            default:
                return false;
        }
    }
    
    // WooCommerce conditions
    private function check_cart_total($amount, $operator) {
        if (!function_exists('WC')) {
            return false;
        }
        
        $cart_total = WC()->cart->get_cart_contents_total();
        
        switch ($operator) {
            case 'greater_than':
                return $cart_total > $amount;
            case 'less_than':
                return $cart_total < $amount;
            case 'equals':
                return $cart_total == $amount;
            default:
                return false;
        }
    }
    
    // Advanced conditions
    private function check_cookie($condition) {
        $cookie_name = $condition['name'] ?? '';
        $value = $condition['value'] ?? '';
        $operator = $condition['operator'] ?? 'equals';
        
        $actual = $_COOKIE[$cookie_name] ?? '';
        
        switch ($operator) {
            case 'equals':
                return $actual === $value;
            case 'not_equals':
                return $actual !== $value;
            case 'exists':
                return isset($_COOKIE[$cookie_name]);
            default:
                return false;
        }
    }
    
    private function check_ab_test($condition) {
        $test_id = $condition['test_id'] ?? '';
        $variant = $condition['variant'] ?? 'A';
        
        $user_variant = get_transient('ccp_ab_' . $test_id . '_' . $this->get_user_ip());
        
        if ($user_variant === false) {
            $user_variant = rand(0, 1) ? 'A' : 'B';
            set_transient('ccp_ab_' . $test_id . '_' . $this->get_user_ip(), $user_variant, WEEK_IN_SECONDS);
        }
        
        return $user_variant === $variant;
    }
    
    private function evaluate_custom_code($code) {
        // Sanitize and evaluate custom PHP code
        if (empty($code)) {
            return false;
        }
        
        try {
            return (bool) eval('return ' . $code . ';');
        } catch (Exception $e) {
            return false;
        }
    }
}

// Initialize
ccp_Condition_Engine::instance();