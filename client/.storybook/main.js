
const custom = require('../webpack.base.js');

module.exports = {
  stories: ['../src/components/**/*.stories.js'],
  addons: [
      '@storybook/addon-knobs/register',
      '@storybook/addon-viewport/register',
      '@storybook/addon-a11y/register'
  ],
  webpackFinal: (config) => {
    config.resolve.alias = {...config.resolve.alias, ...custom.resolve.alias}

    config.module.rules.push({
      test: /\.scss$/,
      use: ['style-loader', 'css-loader', 'sass-loader']
    });

    config.module.rules.push({
      test: /\.yml$/,
      exclude: [
        /(node_modules)/
      ],
      use: [
        'json-loader',
        'yaml-loader'
      ]
    });

    console.log(config);

    return config;
  }
};
