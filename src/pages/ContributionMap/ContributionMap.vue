/**
 * @license GPL-2.0-or-later
 */

<script>
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
    this.loadContributionsExtend();
  }
};
</script>

<template>
  <div class="map_frontpage">
    <iframe
      v-if="contributionMapWithExtend"
      id="contribution_map"
      :src="contributionMapWithExtend"
      width="100%"
    />
    <div
      v-if="!$root.isMobile"
      class="buttonWrapper"
    >
      <DipasButton
        v-if="projectRunning && takesNewContributions"
        text="Beitrag erstellen"
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
        height: 100%
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
    }

    #app.mobile section.content div.map_frontpage,
    #app.mobile section.content div.map_frontpage iframe#contribution_map {
        padding: 0;
        height: 100%;
    }
</style>

