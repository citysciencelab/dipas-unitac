/**
 * @license GPL-2.0-or-later
 */

const path = require("path");

module.exports = {
  title: "DIPAS Documentation",
  components: "src/basicComponents/**/[A-Z]*.vue",
  defaultExample: false,
  styleguideDir: "dist-doc",
  sections: [
    {
      name: "BasicComponents",
      components: "src/basicComponents/**/[A-Z]*.vue"
    },
    {
      name: "Pages",
      components: "src/pages/**/[A-Z]*.vue"
    }
  ],
  dangerouslyUpdateWebpackConfig (webpackConfig) {
    let filteredFirstHMR = false;

    webpackConfig.plugins = webpackConfig.plugins.filter(plugin => {
      if (plugin.constructor.name === "HotModuleReplacementPlugin" && !filteredFirstHMR) {
        filteredFirstHMR = true;
        return false;
      }

      return true;
    });

    return webpackConfig;
  },
  getComponentPathLine (componentPath) {
    const name = path.basename(componentPath, ".vue"),
      dir = path.dirname(componentPath);

    return `import ${name} from '${dir}';`;
  }
};
