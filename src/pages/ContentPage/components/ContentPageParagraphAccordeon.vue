/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Holds the content page paragraph accordeon
 * @displayName ContentPageParagraphAccordeon
 */
import ContentPageParagraphAccordeonItem from "./ContentPageParagraphAccordeonItem.vue";

export default {
  name: "ContentPageParagraphAccordeon",
  components: {
    ContentPageParagraphAccordeonItem
  },
  props: {
    /**
     * holds the content for the accordeon
     */
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
  <div class="accordeonParagraph">
    <!--
      ContentPageParagraphAccordeonItem component
      @property {Object} content
    -->
    <ContentPageParagraphAccordeonItem
      v-for="(element, index) in content.field_content"
      :key="index"
      :content="element"
    />
  </div>
</template>
