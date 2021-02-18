/**
 * @license GPL-2.0-or-later
 */

<script>
export default {
  name: "ContentPageParagraphText",
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
  <div class="textParagraph">
    <p
      v-if="content.field_headline"
      class="headline"
    >
      {{ content.field_headline }}
    </p>

    <div v-html="content.field_text" />
  </div>
</template>

<style>
    div.textParagraph p {
        font-size: 16px;
        line-height: 30px;
        color: #003063;
    }

    div.textParagraph p.headline {
        font-size: 24px;
        line-height: 24px;
        font-weight: bold;
        letter-spacing: 0;
    }
</style>
