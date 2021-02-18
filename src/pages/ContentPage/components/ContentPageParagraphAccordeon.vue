/**
 * @license GPL-2.0-or-later
 */

<script>
import ContentPageParagraphAccordeonItem from "./ContentPageParagraphAccordeonItem.vue";

export default {
  name: "ContentPageParagraphAccordeon",
  components: {
    ContentPageParagraphAccordeonItem
  },
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
  <div class="accordeonParagraph">
    <ContentPageParagraphAccordeonItem
      v-for="(element, index) in content.field_content"
      :key="index"
      :content="element"
    />
  </div>
</template>
