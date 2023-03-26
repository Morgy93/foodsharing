const webpackConfig = require('./webpack.config')

const host = process.env.HOST || 'localhost'
const target = process.env.PROXY_TARGET || 'http://localhost:8082'

module.exports = {
  ...webpackConfig,
  devServer: {
    host,
    port: 8082,
    hot: true,
    index: '',
    contentBase: false,
    publicPath: '/assets/',
    public: host,
    disableHostCheck: true,
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
