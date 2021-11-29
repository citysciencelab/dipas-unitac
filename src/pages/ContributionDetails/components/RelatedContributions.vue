/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Holds the related contributions
 */
import _ from "underscore";
import {requestBroker} from "../../../mixins/requestBroker.js";
import ContributionListTeaser from "../../ContributionList/components/ContributionListTeaser.vue";

export default {
  name: "RelatedContributions",
  components: {
    ContributionListTeaser
  },
  mixins: [requestBroker],
  props: {
    /**
     * holds the id of the contribution
     */
    contributionID: {
      type: String,
      default: ""
    }
  },
  data () {
    return {
      relatedContributions: []
    };
  },
  watch: {
    // This watch only gets triggered if the parent node is not cached.
    /**
     * reload related contributions if contribution id changes
     * @returns {void}
     */
    contributionID (val) {
      this.loadRelatedContributions(val);
    }
  },
  /**
   * load initially the related contributions
   * @returns {void}
   */
  mounted () {
    if (!_.isUndefined(this.contributionID)) {
      this.loadRelatedContributions(this.contributionID);
    }
  }
};
</script>

<template>
  <section class="relatedContributions">
    <h3 class="headline">
      {{ $t("RelatedContributions.headline") }}
    </h3>
    <!--
      @name contribution list teaser
      @property {Array} of relatedContributions objects
    -->
    <ContributionListTeaser
      v-for="relatedContribution in relatedContributions"
      :key="relatedContribution.nid"
      :teaser="relatedContribution"
    />
  </section>
</template>

<style>
    section.relatedContributions {
        margin-top: 40px;
    }

    section.relatedContributions h3.headline {
        font-size: 1.5rem;
        font-weight: bold;
        color: #003063;
    }

    section.relatedContributions article {
        padding-left: 0;
        padding-right: 0;
    }
</style>
