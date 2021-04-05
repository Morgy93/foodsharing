const mkdirp = require('mkdirp')
const { merge } = require('webpack-merge')
const webpackBase = require('./webpack.base')
const { writeFileSync } = require('fs')
const BundleAnalyzePlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin
const TerserPlugin = require('terser-webpack-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const path = require('path')
const clientRoot = path.resolve(__dirname)
const { join, dirname } = require('path')
const glob = require('glob')
const ServiceWorkerWebpackPlugin = require('serviceworker-webpack-plugin')

const dev = process.env.NODE_ENV !== 'production'

const assetsPath = resolve('../assets')
const modulesJsonPath = join(assetsPath, 'modules.json')

const plugins = []

if (!dev) {
  plugins.push(
    new BundleAnalyzePlugin({
      analyzerMode: 'static',
      reportFilename: 'bundlesize.html',
      defaultSizes: 'gzip',
      openAnalyzer: false,
      logLevel: 'info',
    }),
  )
}

plugins.push(
  {
    // Writes modules.json which is then loaded by the php app (see src/Modules/Core/Control.php).
    // This is how the php app will know if it is a webpack-enabled module or not.
    apply (compiler) {
      compiler.hooks.emit.tapPromise('write-modules', compiler => {
        const stats = compiler.getStats().toJson()
        const data = {}
        for (const [entryName, { assets }] of Object.entries(stats.entrypoints)) {
          data[entryName] = assets.map(asset => join(stats.publicPath, asset))
        }
        // We do not emit the data like a proper plugin as we want to create the file when running the dev server too
        const json = `${JSON.stringify(data, null, 2)}
`
        mkdirp.sync(assetsPath)
        writeFileSync(modulesJsonPath, json)
        return Promise.resolve()
      })
    },
  },
)
plugins.push(
  new CopyWebpackPlugin({
    patterns: [
      { from: './lib/tinymce', to: './tinymce' },
    ],
  }),
)

plugins.push(
  new ServiceWorkerWebpackPlugin({
    entry: path.join(__dirname, 'src/serviceWorker.js'),
    filename: '../sw.js',
  }),
)

module.exports = merge(webpackBase, {
  entry: moduleEntries(),
  mode: dev ? 'development' : 'production',
  devtool: dev ? 'cheap-module-eval-source-map' : 'source-map',
  stats: 'minimal',
  output: {
    path: assetsPath,
    ...(dev
      ? {
          filename: 'js/[name].js',
          chunkFilename: 'js/[chunkhash].js',
        }
      : {
          filename: 'js/[name].[hash].js',
          chunkFilename: 'js/[id].[chunkhash].js',
        }),
    publicPath: '/assets/',
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.(js|vue)$/,
        exclude: [
          /node_modules/,
          resolve('lib'),
        ],
        loader: 'eslint-loader',
        options: {
          configFile: resolve('package.json'),
        },
      },
      {
        test: /\.(png|jpe?g|gif|svg)(\?.*)?$/,
        loader: 'url-loader',
        options: {
          limit: 10000,
          name: dev ? 'img/[name].[ext]' : 'img/[name].[hash:7].[ext]',
        },
      },
      {
        test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
        loader: 'url-loader',
        options: {
          limit: 10000,
          name: dev ? 'fonts/[name].[ext]' : 'fonts/[name].[hash:7].[ext]',
        },
      },
    ],
  },
  plugins,
  optimization: {
    minimizer: [
      new TerserPlugin({
        sourceMap: true,
      }),
    ],
    runtimeChunk: true,
    splitChunks: {
      chunks: 'all',
      name: dev,
      maxInitialRequests: 5,
    },
  },
})

function resolve (dir) {
  return path.join(clientRoot, dir)
}

function moduleEntries () {
  const basedir = join(__dirname, '../src/Modules')
  return uniq(glob.sync(join(basedir, '*/*.js')).map(filename => {
    return dirname(filename).substring(basedir.length + 1)
  })).reduce((entries, name) => {
    entries[`Modules/${name}`] = join(basedir, name, `${name}.js`)
    return entries
  }, {})
}

function uniq (items) {
  return [...new Set(items)]
}
