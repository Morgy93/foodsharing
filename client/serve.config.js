const webpackConfig = require('./webpack.config')

const host = process.env.HOST || 'localhost'
const target = process.env.PROXY_TARGET || 'http://localhost:8080'

module.exports = {
  ...webpackConfig,
  devServer: {
    host,
    port: 8080,
    hot: true,
    index: '',
    contentBase: false,
    publicPath: '/assets/',
    disableHostCheck: true,
    inline: false, // https://webpack.js.org/loaders/expose-loader/#inline to hide silly error message
    overlay: {
      warnings: true,
      errors: true,
    },
    proxy: {
      '!/sockjs-node/**': {
        target,
        changeOrigin: true,
        xfwd: true,
        ws: true,
      },
    },
  },
}
