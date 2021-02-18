/**
 * @license GPL-2.0-or-later
 */

<script>
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
    contributionID (val) {
      this.loadRelatedContributions(val);
    }
  },
  mounted () {
    if (!_.isUndefined(this.contributionID)) {
      this.loadRelatedContributions(this.contributionID);
    }
  }
};
</script>

<template>
  <section class="relatedContributions">
    <p class="headline">
      {{ $t("RelatedContributions.headline") }}
    </p>

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

    section.relatedContributions p.headline {
        font-size: 26px;
        font-weight: bold;
        color: #003063;
    }

    section.relatedContributions article {
        padding-left: 0;
        padding-right: 0;
    }
</style>
