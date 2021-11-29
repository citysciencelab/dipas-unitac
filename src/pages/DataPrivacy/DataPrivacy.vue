/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the data privacy page
 * @displayName DataPrivacy
 */
import _ from "underscore";
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "DataPrivacy",
  extends: ContentPage,
  mixins: [requestBroker],
  computed: {
    /**
     * @name hasCookie
     * @returns {Object} cookies object
     */
    hasCookie () {
      return !_.isNull(this.$cookies.get("dipas"));
    },
    /**
     * @name showAcceptCookiesButton
     * @returns {Boolean}
     */
    showAcceptCookiesButton () {
      return this.$root.isMobile && !this.hasCookie && !this.cookieButtonClicked;
    }
  },
  beforeMount () {
    /**
     * loads initally the data privacy data object from requestbroker drupal api
     * @returns {void}
     */
    this.loadEndpoint("dataprivacy");
  }
};
</script>
