/**
 * @license GPL-2.0-or-later
 */

/* eslint-disable no-unused-vars */
const config = {
  /**
   * Configuration to for the proxy to drupal
   *
   * @type {Object}
   * @property {number} port - The port the apache hosting drupal is reachable (If port 443, the protocol must be https:)
   * @property {string} protocol - The protocol with wich the apache hosting drupal is reachable
   * @property {string} baseHost - The basic host under wich the drupal instance is reachable
   */
  drupal: {
    port: 80,
    protocol: "http",
    baseHost: "dipas.local"
  },
  /**
   * Setting whether the devServer should use ssl to communicate over HTTPS.
   * @type {Boolean}
   */
  useSSL: false,
  locale: "de" // Language Settings for Frontend - Set "en" for english
};

module.exports = config;
