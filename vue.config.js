/**
 * @license GPL-2.0-or-later
 */
const webpack = require("webpack");

// If not configured or in pipeline, fallback to the example config
let config;

try {
  config = require("./vue.config.local.js");
}
catch (e) {
  config = require("./example.vue.config.local.js");
}


// vue.config.js
module.exports = {
  devServer: {
    disableHostCheck: true,
    proxy: {
      "^/drupal": {
        // eslint-disable-next-line
        target: `${config.drupal.protocol}://${config.drupal.baseHost}${config.drupal.port == 80 || config.drupal.port == 443 ? "" : ":" + config.drupal.port}`,
        changeOrigin: true,
        router: function (req) {
          const response = {
              protocol: `${config.drupal.protocol}:`,
              port: config.drupal.port
            },
            host = req.headers.host,

            // Split of the existing host
            parts = host.split(":");

          // eslint-disable-next-line
          response.host = parts[0];
          return response;
        }
      }
    },
    https: config.useSSL
  },
  publicPath: process.env.NODE_ENV === "production" ? "" : "/", // eslint-disable-line no-process-env
  css: {
    extract: process.env.NODE_ENV !== "production" ? false : { // eslint-disable-line no-process-env
      filename: "./css/style.css"
    }
  },
  chainWebpack: conf => {
    conf.optimization.splitChunks(false);
  },
  configureWebpack: {
    output: {
      filename: "./js/bundle.js"
    },
    devtool: "source-map",
    plugins: [
      new webpack.DefinePlugin({
        LOCALE: JSON.stringify(config.locale)
      })
    ]
  },
  transpileDependencies: [
    "@eli5/bootstrap-breakpoints-vue",
    "vue-page-title",
    "vue-resource",
    "vue-router",
    "vuex"
  ]
};
