/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for DownloadLink is described here.
 * @displayName DownloadLink
 */
export default {
  /**
   * More Informations for DownloadLink is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "DownloadLink",
  props: {
    /**
     * holds the link object
     */
    link: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  computed: {
  /**
    * Adds the extension to the fileName for more user transparency
    * @type {String} fileNameWithExtension
    */
    linkFileName () {
      const pointIndex = this.link.url.lastIndexOf("."),
        extension = this.link.url.substr(pointIndex + 1);

      if (pointIndex > 0) {
        return this.link.name + " (" + extension.toUpperCase() + ")";
      }

      return this.link.name;
    }
  }
};
</script>

<template>
  <div
    class="download"
    :aria-label="$t('DownloadsBlock.headline')"
  >
    <i
      class="material-icons"
      aria-hidden="true"
    >
      get_app
    </i>&nbsp;
    <a
      :href="link.url"
      target="_blank"
    >{{ linkFileName }}
    </a>
  </div>
</template>

<style>
  div.download a {
    color: #005CA9;
    font-weight: bold;
  }
</style>

