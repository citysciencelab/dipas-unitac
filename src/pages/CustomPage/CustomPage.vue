/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The frame for a custom page
 * @displayName CustomPage
 */
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "CustomPage",
  extends: ContentPage,
  mixins: [requestBroker],
  data () {
    return {
      showPartnerLogo: true
    };
  },
  beforeMount () {
    /**
     * loads initally the custom page data object from requestbroker drupal api
     * @returns {void}
     */
    this.loadEndpoint("custompage");
  }
};
</script>
