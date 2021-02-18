/**
 * @license GPL-2.0-or-later
 */

<script>
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
    this.loadEndpoint("custompage");
  }
};
</script>
