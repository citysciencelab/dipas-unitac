/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Holds the content for the paragraph text
 * @displayName ContentPageParagraphText
 */
export default {
  name: "ContentPageParagraphText",
  props: {
    /**
     * serves the content object
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
  <div class="textParagraph">
    <h2
      v-if="content.field_headline"
      class="headline"
    >
      {{ content.field_headline }}
    </h2>

    <div v-html="content.field_text" />
  </div>
</template>

<style>
    div.textParagraph p {
        font-size: 1rem;
        line-height: 30px;
        color: #000000;
    }

    div.textParagraph h2.headline {
        font-size: 1.875rem;
        line-height: 2rem;
        font-weight: bold;
        letter-spacing: 0;
    }

    div.textParagraph a {
      color: #005CA9;
      font-weight: bold;
    }
</style>
