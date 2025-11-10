# Conditional Content Pro - Development Setup

## Directory Structure

```
conditional-content-pro/
├── conditional-content-pro.php          # Main plugin file
├── readme.txt                        # WordPress plugin readme
├── package.json                      # NPM dependencies
├── webpack.config.js                 # Build configuration
├── .gitignore
│
├── includes/
│   ├── class-condition-engine.php   # Core condition logic
│   ├── class-admin.php              # Admin interface
│   ├── class-shortcode.php          # Shortcode handlers
│   ├── class-ajax-handler.php       # AJAX endpoints
│   └── elementor/
│       ├── class-elementor-integration.php
│       └── class-elementor-widget.php
│
├── src/
│   └── admin/
│       └── index.jsx                # React admin interface
│
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       └── frontend.js
│
└── build/                           # Compiled files (auto-generated)
    └── index.js
```

## Quick Start

### 1. Install Dependencies

```bash
npm install
```

### 2. Build React Admin Interface

**Development mode (with watch):**
```bash
npm run dev
```

**Production build:**
```bash
npm run build
```

### 3. Install Plugin

- Upload the `conditional-content-pro` folder to `/wp-content/plugins/`
- Activate via WordPress admin
- Done!

## package.json

```json
{
  "name": "conditional-content-pro",
  "version": "1.0.0",
  "scripts": {
    "dev": "webpack --mode development --watch",
    "build": "webpack --mode production"
  },
  "devDependencies": {
    "@babel/core": "^7.23.0",
    "@babel/preset-react": "^7.22.0",
    "babel-loader": "^9.1.3",
    "webpack": "^5.89.0",
    "webpack-cli": "^5.1.4"
  },
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0"
  }
}
```

## webpack.config.js

```javascript
const path = require('path');

module.exports = {
  entry: './src/admin/index.jsx',
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: 'index.js',
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-react']
          }
        }
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.jsx']
  },
  externals: {
    'react': 'React',
    'react-dom': 'ReactDOM',
    '@wordpress/element': 'wp.element'
  }
};
```

## .gitignore

```
node_modules/
build/
*.log
.DS_Store
*.zip
vendor/
```

## Development Workflow

### Adding New Conditions

**1. Update `class-condition-engine.php`:**
```php
// Add to get_condition_types()
'mycategory' => [
    'my_condition' => __('My Condition', 'conditional-content-pro'),
]

// Add evaluation method
private function check_my_condition($value) {
    // Your logic here
    return true;
}

// Add case to evaluate_single_condition()
case 'my_condition':
    return $this->check_my_condition($value);
```

**2. Update React UI in `src/admin/index.jsx`:**
```javascript
const conditionTypes = {
    mycategory: ['my_condition'],
    // ...
};
```

### Adding AJAX Endpoints

**In `class-ajax-handler.php`:**
```php
add_action('wp_ajax_dcp_my_action', [$this, 'my_action']);

public function my_action() {
    check_ajax_referer('dcp_admin_nonce', 'nonce');
    // Your logic
    wp_send_json_success(['data' => 'value']);
}
```

### Creating Elementor Widgets

**Extend base widget in `includes/elementor/`:**
```php
class My_DCP_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'my-widget'; }
    protected function register_controls() { /* ... */ }
    protected function render() { /* ... */ }
}
```

## Testing

### Test Conditions
```php
// In WordPress admin or code
$engine = DCP_Condition_Engine::instance();
$result = $engine->evaluate([
    ['type' => 'role', 'operator' => 'equals', 'value' => 'administrator']
], 'AND');
var_dump($result); // true/false
```

### Test Shortcodes
```
[dcp_show role="administrator"]Admin content[/dcp_show]
[dcp_hide device="mobile"]Desktop only[/dcp_hide]
```

## Styling

- **Admin styles**: Edit `assets/css/admin.css`
- **Frontend styles**: Edit `assets/css/frontend.css`
- Changes are immediate (no build needed)

## Debugging

### Enable WordPress Debug Mode
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Check Logs
```bash
tail -f wp-content/debug.log
```

### Browser Console
```javascript
// Check if DCP data is loaded
console.log(dcpData);
console.log(dcpAdmin);
```

## Building for Production

```bash
# Build optimized JS
npm run build

# Create plugin ZIP
zip -r conditional-content-pro.zip conditional-content-pro \
  -x "*/node_modules/*" "*/src/*" "*/.git/*" "*.DS_Store"
```

## Version Updates

**1. Update version in:**
- `conditional-content-pro.php` (plugin header)
- `package.json`

**2. Rebuild:**
```bash
npm run build
```

**3. Test & Deploy**

## Common Issues

### React not loading
- Check `build/index.js` exists
- Verify `wp_enqueue_script` in main plugin file
- Clear browser cache

### AJAX not working
- Verify nonce generation
- Check browser console for errors
- Test endpoint with `admin-ajax.php`

### Conditions not evaluating
- Check `DCP_Condition_Engine::instance()`
- Verify condition type exists in `get_condition_types()`
- Test with simple condition first

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [React Documentation](https://react.dev)
- [Elementor Widget Development](https://developers.elementor.com/docs/widgets/)

## Contributing

1. Fork repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

---

**Need help?** Check the inline code comments or open an issue.