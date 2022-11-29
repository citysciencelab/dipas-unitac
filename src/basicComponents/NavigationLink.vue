/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The navigation link component.
 * @displayName NavigationLink
 */
import _ from "underscore";

export default {
  /**
   * More Informations for the navigation link is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "NavigationLink",
  props: {
    /**
     * Init endpoint for data request
     */
    endpoint: {
      type: String,
      default: ""
    },
    /**
     * Frontend navi button text
     */
    text: undefined
  },
  data () {
    return {
      isExtURL: false
    };
  },
  computed: {
    /**
     * serves the link icon
     * @name linkicon
     * @returns {String|Boolean} linkicon
     */
    linkicon () {
      return _.isObject(this.text) ? this.text.icon : false;
    },
    /**
     * serves the link icon
     * @name linktext
     * @returns {String} linktext
     */
    linktext () {
      return _.isObject(this.text) ? this.text.name : this.text;
    }
  }
};
</script>

<template>
  <div
    :aria-label="linktext"
  >
    <router-link :to="'/' + endpoint">
      <span
        v-if="linkicon"
        class="material-icons"
        aria-hidden="true"
      >
        {{ linkicon }}
      </span>
      <span
        class="linktext"
      >
        {{ linktext }}
      </span>
    </router-link>
  </div>
</template>
