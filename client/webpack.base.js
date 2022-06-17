const path = require('path')
const clientRoot = path.resolve(__dirname)
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const WriteFilePlugin = require('write-file-webpack-plugin')
const webpack = require('webpack')

const plugins = [
  new VueLoaderPlugin(),
  new WriteFilePlugin(), // to write files to filesystem when using webpack-dev-server
  // this is needed for vMap.test.js for some reason.
  // Do not use this global anywhere and remove if awesome-markers and/or markercluster are replaced or removed!
  new webpack.ProvidePlugin({ L: 'leaflet' }),
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
      // do only show debug information in development mode
      'jquery-migrate': require.resolve(production ? 'jquery-migrate/dist/jquery-migrate.min.js' : 'jquery-migrate'),
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
      {
        test: require.resolve('jquery-slimscroll'),
        loader: 'imports-loader',
        options:
          {
            imports: 'default jquery jQuery',
          },
      },
      {
        test: lib('jquery-ui-addon-autosize.js'),
        loader: 'imports-loader',
        options: {
          type: 'commonjs',
          imports: {
            moduleName: 'jquery',
            name: 'jQuery',
          },
          additionalCode: 'window.jQuery = jQuery',
        },
      },
      {
        test: lib('jquery-ui-addon-datepicker.js'),
        loader: 'imports-loader',
        options:
          {
            imports: 'default jquery jQuery',
          },
      },
      {
        test: lib('fancybox/jquery.fancybox.pack.js'),
        loader: 'imports-loader',
        options:
          {
            imports: 'default jquery jQuery',
          },
      },
      {
        test: lib('dynatree/jquery.dynatree.js'),
        loader: 'imports-loader',
        options:
          {
            imports: 'default jquery jQuery',
          },
      },
      {
        test: lib('tablesorterWrapper.js'),
        loader: 'imports-loader',
        options:
          {
            imports: 'default jquery jQuery',
          },
      },
    ],
  },
  plugins,
}

function resolve (dir) {
  return path.join(clientRoot, dir)
}

function lib (filename) {
  return path.join(clientRoot, 'lib', filename)
}
