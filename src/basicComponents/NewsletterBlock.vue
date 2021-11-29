/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas Newsletter Block.
 * @displayName NewsletterBlock
 */
import postscribe from "postscribe";

export default {
  /**
   * More Informations the Newsletter Block is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "NewsletterBlock",
  props: {
    /**
     * holds the object for settings.
     * @name settings
     */
    settings: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  mounted () {
    if (this.settings.snippet) {
      postscribe("#snippet", this.settings.snippet);
    }
  }
};
</script>

<template>
  <section v-if="settings.snippet">
    <p class="headline">
      {{ $t("NewsletterBlock.headline") }}
    </p>
    <div id="snippet"></div>
  </section>
</template>
