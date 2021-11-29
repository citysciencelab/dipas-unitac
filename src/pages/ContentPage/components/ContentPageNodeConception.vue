/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Shows the contact data page
 * @displayName ContentPageNodeConception
 */
import _ from "underscore";
export default {
  name: "ContentPageNodeConception",
  props: {
  /**
    * The conception content from node.
    */
    content: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  computed: {
  /**
    * Serves wether a image is available or not
    * @type {Boolean} useResponsiveImage
    */
    useResponsiveImage () {
      return !_.isUndefined(this.content.field_conception_image.html);
    }
  }
};
</script>

<template>
  <router-link
    :to="'/conception/' + content.nid"
    class="conceptionTeaser"
  >
    <div
      v-if="useResponsiveImage"
      v-html="content.field_conception_image.html"
    />
    <h3 class="headline">
      {{ content.title }}
    </h3>
    <img
      v-if="!useResponsiveImage"
      :src="content.field_conception_image.url ? content.field_conception_image.url : content.field_conception_image"
      alt=""
    />
  </router-link>
</template>

<style>
    .conceptionTeaser {
        margin-right: 20px;
    }

    .conceptionTeaser > img {
        width: 100%;
        border: 1px solid #777777
    }

    .conceptionTeaser > h3.headline {
        font-size: 1rem;
        line-height: 1.2rem;
        font-weight: bold;
        margin: 0;
        padding: 10px 0 5px;
        color: #003063;
    }
</style>
