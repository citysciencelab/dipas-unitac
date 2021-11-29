/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for DownloadBlock is described here.
 * @displayName DownloadBlock
 */
import DownloadLink from "./DownloadLink.vue";

export default {
  /**
   * More Informations for DownloadBlock is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "DownloadsBlock",
  components: {
    DownloadLink
  },
  data () {
    return {
      currentPage: 1,
      visiblePagerLinks: 3,
      filesPerPage: 5
    };
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
     * holds the download page name
     * @name downloadPage
     * @returns {Array} downloads page
     */
    downloadPage () {
      return this.downloads.slice((this.currentPage - 1) * this.filesPerPage, this.currentPage * this.filesPerPage);
    },
    /**
     * holds the number of total download pages
     * @name totalPages
     * @returns {Number} total pages amount
     */
    totalPages () {
      return Math.ceil(this.downloads.length / this.filesPerPage);
    },
    /**
     * holds the pager links in array
     * @name pagerLinks
     * @returns {Array} pager links
     */
    pagerLinks () {
      const pagerLinks = [];

      if (this.visiblePagerLinks >= this.totalPages) {
        for (let i = 1; i <= this.totalPages; i++) {
          pagerLinks.push(i);
        }
      }
      else if (this.currentPage >= this.totalPages) {
        pagerLinks.push(this.currentPage);
      }
      else {
        let startPage = this.currentPage - Math.ceil((this.visiblePagerLinks - 1) / 2);

        if (startPage < 1) {
          startPage = 1;
        }
        for (let i = startPage; i < startPage + this.visiblePagerLinks; i++) {
          pagerLinks.push(i);
        }
      }
      return pagerLinks;
    }
  },
  methods: {
    /**
     * sets the previous page number
     * @name prevPage
     * @returns {Number} previous page
     */
    prevPage () {
      this.currentPage = this.currentPage > 1 ? this.currentPage - 1 : 1;
    },
    /**
     * sets the next page number
     * @name prevPage
     * @returns {Number} next page
     */
    nextPage () {
      this.currentPage = this.currentPage < this.totalPages ? this.currentPage + 1 : this.totalPages;
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
      <div class="links">
        <!--
          @name DownloadLink
          @property downloadPage
        -->
        <DownloadLink
          v-for="(link, index) in downloadPage"
          :key="index"
          :link="link"
        />
      </div>

      <div
        v-if="totalPages > 1"
        class="pager"
      >
        <a
          @click="currentPage = 1"
        >
          <i class="material-icons">first_page</i>
        </a>
        <a
          @click="prevPage"
        >
          <i class="material-icons">chevron_left</i>
        </a>
        <a
          v-for="page in pagerLinks"
          :key="page"
          :class="{active: currentPage === page}"
          @click="currentPage = page"
        >{{ page }}</a>
        <span v-if="totalPages > visiblePagerLinks && pagerLinks[pagerLinks.length - 1] < totalPages">&hellip;</span>
        <a
          @click="nextPage"
        >
          <i class="material-icons">chevron_right</i>
        </a>
        <a
          @click="currentPage = totalPages"
        >
          <i class="material-icons">last_page</i>
        </a>
      </div>
    </div>
  </section>
</template>

<style>
    div.listing {
        width: fit-content;
        max-width: 100%;
    }

    div.listing div.pager {
        margin: 10px 60px;
        width: fit-content;
    }

    div.listing div.pager a {
        cursor: pointer;
        white-space: unset;
        display: inline-block;
        margin: 0 2px;
    }

    div.listing div.pager a.active {
        text-decoration: underline;
    }
</style>
