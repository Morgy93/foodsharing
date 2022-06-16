const path = require('path')
const clientRoot = path.resolve(__dirname)
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const WriteFilePlugin = require('write-file-webpack-plugin')
const webpack = require('webpack')

const plugins = [
  new VueLoaderPlugin(),
  new WriteFilePlugin(), // to write files to filesystem when using webpack-dev-server
  new webpack.ProvidePlugin({
    // the following globals are needed for legacy packages and should hopefully vanish over time
    L: 'leaflet',
    jQuery: 'jquery',
    'window.jQuery': 'jquery',
  }),
]

const production = process.env.NODE_ENV === 'production'

if (production) {
  plugins.push(
    new MiniCssExtractPlugin({
      filename: 'css/[id].[hash].css',
      chunkFilename: 'css/[id].[hash].css',
    }),
  )
}

module.exports = {
  resolve: {
    extensions: ['.js', '.vue'],
    modules: [
      resolve('node_modules'),
    ],
    alias: {
      img: resolve('../img'),
      css: resolve('../css'),
      js: resolve('lib'),
      '@': resolve('src'),
      '@php': resolve('../src'),
      '>': resolve('test'),
      '@translations': resolve('../translations'),
      // the following resolves are needed for legacy packages and will hopefully vanish over time.
      'jquery-tagedit-auto-grow-input': resolve('lib/tagedit/js/jquery.autoGrowInput.js'),
      'jquery-tagedit': resolve('lib/tagedit/js/jquery.tagedit.js'),
      'jquery.tinymce': resolve('lib/tinymce/jquery.tinymce.min'),
      'leaflet.awesome-markers': require.resolve('leaflet.awesome-markers/dist/leaflet.awesome-markers.js'),
      'leaflet.css-awesome-markers': require.resolve('leaflet.awesome-markers/dist/leaflet.awesome-markers.css'),
      tablesorter: resolve('lib/tablesorter/jquery.tablesorter.js'),
      'tablesorter-pagercontrols': resolve('lib/tablesorter/jquery.tablesorter.pager.js'),
      'jquery-fancybox': resolve('lib/fancybox/jquery.fancybox.pack.js'),
      'jquery-ui-addons': resolve('lib/jquery-ui-addons.js'),
      'jquery-dynatree': resolve('lib/dynatree/jquery.dynatree.js'),
    },
  },
  module: {
    rules: [
      {
        test: /\.m?js$/,
        exclude: [
          /(node_modules)/,
          resolve('lib'), // ignore the old lib/**.js files
        ],
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              [
                '@babel/preset-env',
                {
                  targets: {
                    browsers: ['> 0.5%', 'last 2 versions', 'Firefox ESR', 'not dead'],
                  },
                  useBuiltIns: 'usage',
                  modules: 'commonjs',
                  corejs: '3',
                },
              ],
            ],
          },
        },
      },
      {
        test: /\.vue$/,
        exclude: /(node_modules)/,
        use: 'vue-loader',
      },
      {
        test: /\.(sc|c)ss$/,
        use: [
          production ? MiniCssExtractPlugin.loader : 'style-loader',
          'css-loader',
          'sass-loader',
        ],
      },
      {
        test: /\.ya?ml$/,
        exclude: [
          /(node_modules)/,
        ],
        use: [
          'yaml-loader',
        ],
      },
    ],
  },
  plugins,
}

function resolve (dir) {
  return path.join(clientRoot, dir)
}
