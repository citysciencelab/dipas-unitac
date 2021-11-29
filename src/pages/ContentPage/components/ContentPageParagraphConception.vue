/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the content page paragraph of the conception
 * @displayName ContentPageParagraphConception
 */
import ContentPageNodeConception from "./ContentPageNodeConception.vue";

export default {
  name: "ContentPageParagraphConception",
  components: {
    ContentPageNodeConception
  },
  /**
   * holds the content data
   */
  props: {
    content: {
      type: Object,
      default () {
        return {};
      }
    }
  }
};
</script>

<template>
  <!--
    ContentPageNodeConception component
    @property {Object} content
  -->
  <ContentPageNodeConception :content="content" />
</template>

