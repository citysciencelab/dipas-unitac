/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Holds the content for the paragraph video
 * @displayName ContentPageParagraphVideo
 */
import _ from "underscore";

export default {
  name: "ContentPageParagraphVideo",
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
  data () {
    return {
      ratioComparison: [
        {
          ratioValue: 2.33333,
          ratioClass: "embed-responsive-21by9"
        },
        {
          ratioValue: 1.77778,
          ratioClass: "embed-responsive-16by9"
        },
        {
          ratioValue: 1.33333,
          ratioClass: "embed-responsive-4by3"
        },
        {
          ratioValue: 1.00000,
          ratioClass: "embed-responsive-1by1"
        }
      ]
    };
  },
  computed: {
    /**
     * serves the aspect ratio for the picture
     * @returns {String} ratioClass
     */
    ratio_class () {
      const matches = this.content.field_video.match(/.*?width="(\d+?)".*?height="(\d+?)"/i),
        width = !isNaN(parseInt(matches[1], 10)) ? parseInt(matches[1], 10) : 16,
        height = !isNaN(parseInt(matches[2], 10)) ? parseInt(matches[2], 10) : 9,
        ratio = width / height;
      let ratioDiffs = {};

      this.ratioComparison.forEach((item, index) => {
        ratioDiffs[index] = [index, Math.abs(item.ratioValue - ratio)];
      });
      ratioDiffs = _.sortBy(ratioDiffs, (elem) => elem[1]);
      return this.ratioComparison[ratioDiffs[0][0]].ratioClass;
    }
  }
};
</script>

<template>
  <div
    class="videoParagraph embed-responsive"
    :class="ratio_class"
    v-html="content.field_video"
  >
  </div>
</template>

<style>
    .videoParagraph {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .videoParagraph iframe {
        border: 1px solid #707070;
    }
</style>
