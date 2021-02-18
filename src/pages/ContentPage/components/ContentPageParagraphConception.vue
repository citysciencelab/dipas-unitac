/**
 * @license GPL-2.0-or-later
 */

<script>
import ContentPageNodeConception from "./ContentPageNodeConception.vue";

export default {
  name: "ContentPageParagraphConception",
  components: {
    ContentPageNodeConception
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
  <ContentPageNodeConception :content="content" />
</template>

