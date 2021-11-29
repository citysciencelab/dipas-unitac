/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Shows the single accordeon item
 * @displayName ContentPageParagraphAccordeonItem
 */
export default {
  name: "ContentPageParagraphAccordeonItem",
  props: {
    /**
     * holds the content for the single accordeon item
     */
    content: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      hideTextField: true
    };
  }
};
</script>

<template>
  <div class="accordeonItem">
    <div
      class="textHeadline"
      tabindex="0"
      role="button"
      :aria-expanded="!hideTextField ? 'true' : 'false'"
      @click="hideTextField = !hideTextField"
      @keyup.enter="hideTextField = !hideTextField"
    >
      <span>{{ content.field_headline }}</span>
      <i class="material-icons customIcon">{{ hideTextField ? "add" : "remove" }}</i>
    </div>

    <div
      v-if="!hideTextField"
      class="textParagraphContent"
      v-html="content.field_text"
    />
  </div>
</template>

<style>
    .accordeonItem .textHeadline {
        background-color: #F0F0F0;
        text-align: left;
        font-size: 1rem;
        line-height: 24px;
        font-weight: bold;
        letter-spacing: 0;
        color: #005CA9;
        opacity: 1;
        padding: 0.5em;
        display: flex;
        margin-top: 10px;
    }

    .accordeonItem .textHeadline .customIcon {
        font-size: 1.375rem;
        font-weight: bold;
        color: #E10019;
        width: 30px;
        cursor: pointer;
    }

    .accordeonItem .textHeadline span {
        width: 100%;
        cursor: default;
    }

    .accordeonItem .textParagraphContent {
        font-size: 1rem;
        padding: 10px 0 0 10px;
    }

    div.accordeonItem .textParagraphContent a {
      color: #005CA9;
      font-weight: bold;
    }

</style>
