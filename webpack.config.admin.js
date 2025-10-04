const path = require('path');
const isDev = (process.env.NODE_ENV !== 'production');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const autoprefixer = require('autoprefixer');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

module.exports = {
  mode: 'production',
  entry: {
    // ################################################
    // SCSS
    // ################################################
    // Theme for Varbase Admin
    'theme/varbase-admin.admin-navigation.theme': ['./modules/varbase_admin/scss/theme/varbase-admin.admin-navigation.theme.scss'],
    'theme/varbase-admin.admin-toolbar.theme': ['./modules/varbase_admin/scss/theme/varbase-admin.admin-toolbar.theme.scss'],
    'theme/varbase-admin.theme.style': ['./modules/varbase_admin/scss/theme/varbase-admin.theme.style.scss'],
    'theme/varbase-admin.theme.style-rtl': ['./modules/varbase_admin/scss/theme/varbase-admin.theme.style-rtl.scss'],
  },
  output: {
    path: path.resolve(__dirname, 'modules/varbase_admin/css'),
    pathinfo: false,
    publicPath: '../../',
  },
  module: {
    rules: [
      {
        test: /\.(png|jpe?g|gif|svg)$/,
        exclude: /sprite\.svg$/,
        type: 'asset/resource',
        generator: {
          filename: '[path][name][ext]',
        },
      },
      {
        test: /\.(css|scss)$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
          },
          {
            loader: 'css-loader',
            options: {
              sourceMap: isDev,
              importLoaders: 2,
              url: {
                filter: (url) => {
                  // Don't handle sprite svg
                  if (url.includes('sprite.svg')) {
                    return false;
                  }

                  return true;
                },
              },
            },
          },
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: isDev,
              postcssOptions: {
                plugins: [
                  autoprefixer(),
                ],
              },
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: isDev,
              // Global SCSS imports:
              additionalData: `
                @use "sass:color";
                @use "sass:math";
              `,
            },
          },
        ],
      },
    ],
  },
  resolve: {
    modules: [
      path.join(__dirname, 'node_modules'),
    ],
    extensions: ['.js', '.json'],
  },
  plugins: [
    new RemoveEmptyScriptsPlugin(),
    new CleanWebpackPlugin({
      cleanStaleWebpackAssets: false
    }),
    new MiniCssExtractPlugin(),
  ],
  watchOptions: {
    aggregateTimeout: 300,
    ignored: ['**/*.woff', '**/*.json', '**/*.woff2', '**/*.jpg', '**/*.png', '**/*.svg', 'node_modules', 'images'],
  }
};
