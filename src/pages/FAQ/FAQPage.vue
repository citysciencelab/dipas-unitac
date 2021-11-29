/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the data of the faq page
 * @displayName FAQPage
 */
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "FAQPage",
  extends: ContentPage,
  mixins: [requestBroker],
  beforeMount () {
    /**
     * loads initally the faq data object from requestbroker drupal api
     * @returns {void}
     */
    this.loadEndpoint("faq");
  }
};
</script>
