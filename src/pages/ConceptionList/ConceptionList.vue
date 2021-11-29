/**
 * @license GPL-2.0-or-later
 */

<script>
/**
  * Shows a page with listed conception data.
  * @displayName ConceptionList
  */
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "ConceptionList",
  extends: ContentPage,
  mixins: [requestBroker],
  beforeMount () {
    /**
     * load data "conceptionlist" via request broker
     * @returns {void}
     */
    this.loadEndpoint("conceptionlist");
  }
};
</script>
