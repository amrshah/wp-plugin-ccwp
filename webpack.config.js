const path = require('path');

module.exports = {
  entry: './src/admin/index.jsx',
  output: {
    path: path.resolve(__dirname, 'build'),
    filename: 'index.js',
    library: 'CCPAdmin',
    libraryTarget: 'window',
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              ['@babel/preset-env', { targets: 'defaults' }],
              ['@babel/preset-react', { runtime: 'automatic' }], // JSX auto transform
            ],
          },
        },
      },
    ],
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',

    // ✅ Add these for WordPress built-in libraries:
    '@wordpress/element': 'wp.element',
    '@wordpress/i18n': 'wp.i18n',
    '@wordpress/components': 'wp.components',
    '@wordpress/data': 'wp.data',
    '@wordpress/api-fetch': 'wp.apiFetch',
    '@wordpress/hooks': 'wp.hooks',
  },
  mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
  devtool: process.env.NODE_ENV === 'production' ? false : 'source-map',
};
