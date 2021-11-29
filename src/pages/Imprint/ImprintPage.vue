/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the data of the imprint page
 * @displayName ImprintPage
 */
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "ImprintPage",
  extends: ContentPage,
  mixins: [requestBroker],
  beforeMount () {
    /**
     * loads initally the imprint data object from requestbroker drupal api
     * @returns {void}
     */
    this.loadEndpoint("imprint");
  }
};
</script>
