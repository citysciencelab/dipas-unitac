/**
 * @license GPL-2.0-or-later
 */

<script>
import _ from "underscore";

export default {
  name: "ContentPageNodeConception",
  props: {
    content: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  computed: {
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

    <p class="headline">
      {{ content.title }}
    </p>

    <img
      v-if="!useResponsiveImage"
      :src="content.field_conception_image.url"
    />
  </router-link>
</template>

<style>
    .conceptionTeaser {
        margin-right: 20px;
    }

    .conceptionTeaser > img {
        width: 100%;
    }

    .conceptionTeaser > p.headline {
        font-size: 18px;
        line-height: 18px;
        font-weight: bold;
        margin: 0;
        padding: 10px 10px 5px;
        color: black;
    }
</style>
