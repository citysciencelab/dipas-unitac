/**
 * @license GPL-2.0-or-later
 */

<script>
export default {
  name: "ContentPageParagraphAccordeonItem",
  props: {
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
      @click="hideTextField = !hideTextField"
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
        font-size: 16px;
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
        font-size: 22px;
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
        font-size: 16px;
        padding: 10px 0 0 10px;
    }
</style>
