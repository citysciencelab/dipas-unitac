/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for DownloadBlock is described here.
 * @displayName DownloadBlock
 */
import DownloadBlockPager from "./DownloadsBlockPager.vue";

export default {
  /**
   * More Informations for DownloadBlock is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "DownloadsBlock",
  components: {
    DownloadBlockPager
  },
  computed: {
    /**
     * holds the downloads
     * @name downloads
     * @returns {Array} downloads
     */
    downloads () {
      return this.$store.getters.downloads;
    },
    /**
     * serves downloads flagged for documentation
     * @name documentForDocumentation
     * @returns {Array} documentForDocumentation
     */
    documentForDocumentation () {
      return this.downloads.filter(item => item.fordoc === true);
    },
    /**
     * holds the general downloads
     * @name generalDownloads
     * @returns {Array} generalDownloads
     */
    generalDownloads () {
      return this.downloads.filter(item => item.fordoc === false);
    }
  }
};
</script>

<template>
  <section v-if="downloads.length">
    <h3 class="headline">
      {{ $t("DownloadsBlock.headline") }}
    </h3>

    <div class="listing">
      <DownloadBlockPager
        :fileObjects="generalDownloads"
      />
      <div v-if="documentForDocumentation.length">
        <h4 class="sideblocksubtitle">
          {{ $t("DownloadsBlock.documentationFiles") }}
        </h4>
        <DownloadBlockPager
          :fileObjects="documentForDocumentation"
        />
      </div>
    </div>
  </section>
</template>

