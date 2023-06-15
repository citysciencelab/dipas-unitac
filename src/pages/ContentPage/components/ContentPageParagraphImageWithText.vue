<script>
import ContentPageParagraphImage from "./ContentPageParagraphImage.vue";
/**
 * Holds the content for the paragraph text
 * @displayName ContentPageParagraphImageWithText
 */
export default {
  name: "ContentPageParagraphImageWithText",
  components: {
    ContentPageParagraphImage
  },
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
  },
  computed: {
    imagePositionSetter () {
      return this.content.field_image_position.value;
    }
  }
};
</script>

<template>
  <div class="textParagraph textParagraphWithImage">
    <div
      :class="imagePositionSetter"
      class="imageParagraphDiv"
    >
      <ContentPageParagraphImage
        :content="content"
      />
    </div>
    <div v-html="content.field_text" />
  </div>
</template>

<style>
  div.textParagraphWithImage {
    display: table;
  }

  div.textParagraphWithImage div.imageParagraph {
    width: 20rem;
  }

  div.textParagraphWithImage div.imageParagraphDiv {
    width: 35%;
    justify-content: center;
    display: flex;
    height: 100%;
  }

  div.textParagraphWithImage div.imageParagraphDiv img {
    max-width: 100%;
    max-height: 100%;
    height: auto;
    width: auto;
  }

  div.textParagraphWithImage div.image_left {
    float: left;
    padding: 5px 10px 5px 0px;
  }

  div.textParagraphWithImage div.image_right {
    float: right;
    padding: 5px 0px 5px 10px;
  }

  #app.mobile div.textParagraphWithImage div.imageParagraph {
    width: 10rem;
  }
</style>
