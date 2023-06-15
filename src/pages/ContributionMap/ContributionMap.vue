/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The corresponding map to the contribution
 * @displayName ContributionMap
 */
import _ from "underscore";
import {mapGetters} from "vuex";
import {requestBroker} from "../../mixins/requestBroker.js";
import DipasButton from "../../basicComponents/DipasButton.vue";

export default {
  name: "ContributionMap",
  components: {
    DipasButton
  },
  mixins: [requestBroker],
  data () {
    return {
      // Border values are noted in percent values!
      extendBorder: {
        top: 0,
        bottom: 15,
        left: 0,
        right: 0
      },
      extent: {}
    };
  },
  computed: {
    ...mapGetters([
      "contributionmap",
      "projectRunning",
      "takesNewContributions"
    ]),
    htmlPageTitle () {
      return this.$t("ContributionMap.title");
    },
    /**
     * serves the map with the contribution extend
     * @returns {String|Boolean}
     */
    contributionMapWithExtend () {
      let queryString = "?castToPoint=true";

      if (!_.isUndefined(this.contributionmap) && this.contributionmap.length && Object.keys(this.extent).length) {
        if (this.extent.lon_diff > 0 && this.extent.lat_diff > 0) {
          const extentData = [
            this.extent.lon_min - ((this.extendBorder.left * this.extent.lon_diff) / 100),
            this.extent.lat_min - ((this.extendBorder.bottom * this.extent.lat_diff) / 100),
            this.extent.lon_max + ((this.extendBorder.right * this.extent.lon_diff) / 100),
            this.extent.lat_max + ((this.extendBorder.top * this.extent.lat_diff) / 100)
          ];

          queryString += "&projection=EPSG:4326&zoomToExtent=" + extentData.join(",");
        }
        return this.contributionmap + queryString;
      }
      else if (!_.isUndefined(this.contributionmap) && this.contributionmap.length) {
        return this.contributionmap + queryString;
      }
      return false;
    }
  },
  beforeMount () {
    /**
     * loads initial the contribution extend
     * @returns {void}
     */
    this.loadContributionsExtend();
  }
};
</script>

<template>
  <div class="map_frontpage">
    <h1
      class="sr-only"
    >
      {{ $t('ContributionMap.title') }}
    </h1>
    <iframe
      v-if="contributionMapWithExtend"
      id="contribution_map"
      :src="contributionMapWithExtend"
      :title="$t('ContributionMap.title')"
    />

    <div
      v-if="!$root.isMobile"
      class="buttonWrapper"
    >
      <!--
        @name DipasButton
        @event click createContribution
      -->
      <DipasButton
        v-if="projectRunning && takesNewContributions"
        :text="$t('ContributionMap.addContributionButton')"
        icon="add"
        class="red round"
        @click="$root.$emit('createContribution')"
      />
    </div>
  </div>
</template>

<style>
    #app.mobile section.content.contributionmap {
        padding: 0;
    }

    .map_frontpage {
        padding: 10px;
    }

    #app.desktop section.content div.map_frontpage,
    #app.desktop section.content div.map_frontpage iframe#contribution_map {
        height: 100%;
        width: 100%;
    }

    #app.desktop section.content .buttonWrapper {
        display: flex;
        height: 0;
    }

    #app.desktop section.content .buttonWrapper .dipasButton {
        width: 300px;
        position: relative;
        top: -100px;
        margin: 0 auto;
        font-size: 1.25rem;
        line-height: 0.8rem;
    }

    #app.mobile section.content div.map_frontpage,
    #app.mobile section.content div.map_frontpage iframe#contribution_map {
        padding: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
</style>
