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
     * The link object
     */
    link: {
      type: Object,
      default () {
        return {};
      }
    }
  }
};
</script>

<template>
  <div class="download">
    <i class="material-icons">get_app</i>&nbsp;
    <a
      :href="link.url"
      target="_blank"
    >{{ link.name }}
    </a>
  </div>
</template>
