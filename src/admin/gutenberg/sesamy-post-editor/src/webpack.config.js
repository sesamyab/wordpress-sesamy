const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
  ...defaultConfig,
  resolve: {
    ...defaultConfig.resolve,
    fallback: {
      ...defaultConfig.resolve.fallback,
      'react/jsx-runtime': require.resolve('react/jsx-runtime')
    }
  }
};