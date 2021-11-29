/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Shows the Conception Details at the right column
 * @displayName ConceptionDetailsRightColumn
 */
import _ from "underscore";
import {requestBroker} from "../../../mixins/requestBroker.js";
import ContentPageNodeConception from "../../ContentPage/components/ContentPageNodeConception.vue";

export default {
  name: "ConceptionDetailsRightColumn",
  components: {
    ContentPageNodeConception
  },
  mixins: [requestBroker],
  data () {
    return {
      otherConceptions: []
    };
  },
  computed: {
    /**
     * The conception ID
     */
    conceptionId () {
      return this.$route.params.id;
    }
  },
  watch: {
    // This watch only gets triggered if the parent node is not cached.
    conceptionId (val) {
      this.loadOtherConceptions(val);
    }
  },
  mounted () {
    if (!_.isUndefined(this.conceptionId)) {
      this.loadOtherConceptions(this.conceptionId);
    }
  }
};
</script>

<template>
  <div class="conceptionSidebar">
    <h3 class="headline">
      {{ $t('ConceptionDetailsSidebar.headline') }}
    </h3>

    <ContentPageNodeConception
      v-for="(element, index) in otherConceptions"
      :key="index"
      :content="element"
      imageIsResponsive="false"
    />
  </div>
</template>

<style>
    .conceptionSidebar h3 {
        font-size: 1.5rem;
        font-weight: bold;
        color: #003063;
    }

</style>
