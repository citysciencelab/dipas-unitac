/**
 * @license GPL-2.0-or-later
 */

<script>
export default {
  name: "ContentPageParagraphImage",
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
      paragraphWidth: false
    };
  },
  computed: {
    imageSource () {
      if (typeof this.content.field_image.field_media_image.url !== "undefined") {
        const sizes = Object.keys(this.content.field_image.field_media_image.srcset).map(size => Number(size)).sort((a, b) => a - b).reverse();
        let source = this.content.field_image.field_media_image.url;

        if (typeof this.paragraphWidth === "number") {
          for (const index in sizes) {
            const size = sizes[index];

            if (this.paragraphWidth > size) {
              break;
            }
            source = this.content.field_image.field_media_image.srcset[size];
          }
        }
        return source;
      }
      return false;
    },
    altTag () {
      if (typeof this.content.field_image.field_media_image.alt !== "undefined") {
        return this.content.field_image.field_media_image.alt;
      }
      return "";
    }
  },
  mounted () {
    this.handleResize();
    window.addEventListener("resize", this.handleResize);
  },
  beforeDestroy: function () {
    window.removeEventListener("resize", this.handleResize);
  },
  methods: {
    handleResize () {
      this.paragraphWidth = this.$refs.imageParagraph.clientWidth;
    }
  }
};
</script>

<template>
  <div
    ref="imageParagraph"
    class="imageParagraph"
  >
    <a
      :href="content.field_image.field_media_image.origin"
      target="_blank"
      :title="$t('ParagraphImage.Tooltip')"
    >
      <div
        v-if="content.field_image.field_media_image.html"
        v-html="content.field_image.field_media_image.html"
      />
      <figure v-else>
        <img
          :src="imageSource"
          :alt="altTag"
        />
        <figcaption v-if="content.field_image.field_caption || content.field_image.field_copyright">
          {{ content.field_image.field_caption }}
          <br v-if="content.field_image.field_caption">
          <span v-if="content.field_image.field_copyright">
            © {{ content.field_image.field_copyright }}
          </span>
        </figcaption>
      </figure>
    </a>
  </div>
</template>

<style>
    .imageParagraph {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .imageParagraph figure{
        width: fit-content;
    }

    .imageParagraph img {
        border: 1px solid #707070;
        max-width: 100%;
    }
    .imageParagraph figcaption{
        background: #F0F0F0;
        color: #000;
        padding: 2px 6px;
        font-size: 12px;
    }
</style>
