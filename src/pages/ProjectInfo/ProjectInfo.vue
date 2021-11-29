/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the data of the project ifo page
 * @displayName ProjectInfo
 */
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "ProjectInfo",
  extends: ContentPage,
  mixins: [requestBroker],
  data () {
    return {
      showPartnerLogo: true
    };
  },
  beforeMount () {
    /**
     * loads initally the project info data object from requestbroker drupal api
     * @returns {void}
     */
    this.loadEndpoint("projectinfo");
  }
};
</script>
