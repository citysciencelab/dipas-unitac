/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The right column Element.
 * @displayName RightColumn
 */
import ProjectlogoBlock from "./ProjectlogoBlock.vue";
import NewsletterBlock from "./NewsletterBlock.vue";
import ContactBlock from "./ContactBlock.vue";
import DownloadsBlock from "./DownloadsBlock.vue";


export default {
  /**
   * More Informations for the RightColumn is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "RightColumn",
  components: {
    "projectlogo": ProjectlogoBlock,
    "newsletter": NewsletterBlock,
    "contact": ContactBlock,
    "downloads": DownloadsBlock
  },
  computed: {
    /**
     * serves the sidebar data
     * @name sidebar
     * @returns {Array} sidebar
     */
    sidebar () {
      return this.$store.getters.sidebar;
    }
  }
};
</script>

<template>
  <section class="sidebar">
    <template v-for="(settings, component) in sidebar">
      <component
        :is="component"
        :key="component.name"
        :settings="settings"
        class="block"
        :class="component + 'Block'"
      />
    </template>
  </section>
</template>

<style lang="css">
    section.block:not(:first-child) {
        margin-top: 20px;
    }

    section.block h3.headline {
        font-size: 1.5rem;
        font-weight: bold;
        color: #003063;
    }

    section.block .material-icons {
        vertical-align: middle;
        padding-bottom: 3px;
        font-size: 1rem;
        color: #005CA9;
    }
</style>
