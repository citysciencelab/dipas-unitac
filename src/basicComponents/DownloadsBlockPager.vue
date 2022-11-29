/**
 * @license GPL-2.0-or-later
 */

<script>
import DownloadLink from "./DownloadLink.vue";
export default {
  /**
   * More Informations for DownloadBlockPager is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "DownloadsBlockPager",
  components: {
    DownloadLink
  },
  props: {
    /**
     * Array of file objects.
     */
    fileObjects: {
      type: Array,
      default () {
        return [];
      }
    }
  },
  data () {
    return {
      currentPage: 1,
      visiblePagerLinks: 3,
      filesPerPage: 5
    };
  },
  computed: {
    pagedFiles () {
      return this.fileObjects.slice((this.currentPage - 1) * this.filesPerPage, this.currentPage * this.filesPerPage);
    },
    /**
     * holds the number of total download pages
     * @name totalPages
     * @returns {Number} total pages amount
     */
    totalPages () {
      return Math.ceil(this.fileObjects.length / this.filesPerPage);
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
  <div class="listing">
    <div class="links">
      <!--
        @name DownloadLink
        @property pagedFiles
      -->
      <DownloadLink
        v-for="(link, index) in pagedFiles"
        :key="index"
        :link="link"
      />
    </div>
    <div
      v-if="totalPages > 1"
      class="pager"
    >
      <a
        :aria-label="$t('DownloadsBlock.firstPage')"
        @click="currentPage = 1"
      >
        <i
          aria-hidden="true"
          class="material-icons"
        >
          first_page
        </i>
      </a>
      <a
        :aria-label="$t('DownloadsBlock.goBack')"
        @click="prevPage"
      >
        <i
          aria-hidden="true"
          class="material-icons"
        >
          chevron_left
        </i>
      </a>
      <a
        v-for="page in pagerLinks"
        :key="page"
        :class="{active: currentPage === page}"
        @click="currentPage = page"
      >{{ page }}</a>
      <span v-if="totalPages > visiblePagerLinks && pagerLinks[pagerLinks.length - 1] < totalPages">&hellip;</span>
      <a
        :aria-label="$t('DownloadsBlock.goForward')"
        @click="nextPage()"
      >
        <i
          aria-hidden="true"
          class="material-icons"
        >
          chevron_right
        </i>
      </a>
      <a
        :aria-label="$t('DownloadsBlock.lastPage')"
        @click="currentPage = totalPages"
      >
        <i class="material-icons">last_page</i>
      </a>
    </div>
  </div>
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
  h4.sideblocksubtitle {
    font-weight: 700;
    margin-top: 1.7rem;
    font-size: 1.1rem;
  }
</style>
