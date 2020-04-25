module.exports = {
  plugins: [
    ['@babel/proposal-decorators', { legacy: true }],
    ['@babel/proposal-class-properties', { loose: true }]
  ],
  presets: [
    '@vue/cli-plugin-babel/preset'
  ]
}
