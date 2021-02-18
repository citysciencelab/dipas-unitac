/**
 * @license GPL-2.0-or-later
 */

<script>
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "FAQPage",
  extends: ContentPage,
  mixins: [requestBroker],
  beforeMount () {
    this.loadEndpoint("faq");
  }
};
</script>
