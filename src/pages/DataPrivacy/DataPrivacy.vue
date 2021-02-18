/**
 * @license GPL-2.0-or-later
 */

<script>
import _ from "underscore";
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "DataPrivacy",
  extends: ContentPage,
  mixins: [requestBroker],
  computed: {
    hasCookie () {
      return !_.isNull(this.$cookies.get("dipas"));
    },
    showAcceptCookiesButton () {
      return this.$root.isMobile && !this.hasCookie && !this.cookieButtonClicked;
    }
  },
  beforeMount () {
    this.loadEndpoint("dataprivacy");
  }
};
</script>
