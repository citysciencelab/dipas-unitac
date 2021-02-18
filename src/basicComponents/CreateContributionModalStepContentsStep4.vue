/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for CreateContributionModalStepContentsStep4 is described here.
 * @displayName CreateContributionModalStepContentsStep4
 */
import Masterportal from "./Masterportal.vue";
import RadioGroup from "./RadioGroup.vue";
import {requestBroker} from "../mixins/requestBroker.js";

export default {
  /**
   * More Informations for CreateContributionModalStepContentsStep4 is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CreateContributionModalStepContentsStep4",
  components: {
    Masterportal,
    RadioGroup
  },
  mixins: [requestBroker],
  props: {
    /**
     * The content object of the actual step
     */
    value: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      geodata: this.value.geodata,
      showInfotext: false,
      geometryType: Object.keys(JSON.parse(this.value.geodata)).length ? JSON.parse(this.value.geodata).geometry.type.toLowerCase() : this.$store.getters.contributionGeometryType[0],
      extent: {}
    };
  },
  computed: {
    masterportalSrc () {
      let src = this.$store.getters.createcontributionmap;

      if (this.$root.isMobile) {
        src += (src.indexOf("?") !== -1 ? "&" : "?") + "style=simple";
      }
      return src;
    },
    geometryOptions () {
      const options = {},
        geomTypes = this.$store.getters.contributionGeometryType;

      geomTypes.forEach((geomType, index) => {
        options[index] = {
          val: geomType,
          label: this.$t("CreateContributionModal.StepMap.geom_" + geomType)
        };
      });

      return options;
    },
    mapExtent () {
      const geodata = JSON.parse(this.geodata);

      if (Object.keys(geodata).length && geodata.geometry.type !== "Point") {
        const extent = {
            minLon: 999999999,
            minLat: 999999999,
            maxLon: 0,
            maxLat: 0
          },
          latall = [],
          lonall = [];
        let coord = [];

        if (geodata.geometry.type === "LineString") {
          coord = geodata.geometry.coordinates;
        }
        else if (geodata.geometry.type === "Polygon") {
          coord = geodata.geometry.coordinates[0];
        }

        coord.forEach(function (coordinate) {
          latall.push(coordinate[1]);
          lonall.push(coordinate[0]);
        });

        extent.minLon = Math.min(...lonall);
        extent.minLat = Math.min(...latall);
        extent.maxLon = Math.max(...lonall);
        extent.maxLat = Math.max(...latall);

        return extent;
      }
      else if (!Object.keys(geodata).length && (this.extent.lon_diff > 0 && this.extent.lat_diff > 0)) {
        const projectExtent = {
          minLon: this.extent.lon_min,
          minLat: this.extent.lat_min,
          maxLon: this.extent.lon_max,
          maxLat: this.extent.lat_max
        };

        return projectExtent;
      }
      return false;
    }
  },
  watch: {
    geodata: {
      deep: true,
      /**
       * Saves the choosen geolocation
       * @event input
       * @param {String} geodata geolocation
       */
      handler: function (val) {
        this.$emit("input", {geodata: val});
      }
    },
    geometryType () {
      this.geodata = "{}";
    }
  },
  beforeMount () {
    this.loadContributionsExtend();
  }
};
</script>

<template>
  <div class="createContributionStep4">
    <p class="headline">
      {{ $t("CreateContributionModal.StepMap.headline") }}
    </p>

    <RadioGroup
      v-if="Object.keys(geometryOptions).length > 1"
      v-model="geometryType"
      :options="geometryOptions"
      :value="geometryType"
      horizontal="true"
    />
    <!--
      triggered on click
      @event showInfotext
    -->
    <p
      class="usageHint"
      :class="[!showInfotext ? 'infotextHidden' : '']"
      @click="showInfotext = !showInfotext"
    >
      <i
        class="material-icons"
      >
        info
      </i>
      <span class="infotext">{{ $t("CreateContributionModal.StepMap.infotext_" + geometryType) }}</span>
    </p>
    <Masterportal
      ref="map"
      v-model="geodata"
      :src="masterportalSrc"
      :geodata="geodata"
      :geometryType="geometryType"
      :extent="mapExtent"
    />
  </div>
</template>

<style>
    div.createContributionStep4 {
        position: relative;
    }

    #app.mobile div.createContributionStep4 p.usageHint {
        margin: 0 0 5px 0;
        text-align: right;
    }

    div.createContributionStep4 p.usageHint i.material-icons {
        vertical-align: bottom;
        color: #003063;
    }

    #app.mobile div.createContributionStep4 p.usageHint span.infotext {
        display: block;
        position: absolute;
        right: 0;
        background-color: white;
        z-index: 10;
        padding: 10px;
        border: solid 1px black;
        text-align: left;
    }

    #app.mobile div.createContributionStep4 p.usageHint.infotextHidden span.infotext {
        display: none;
    }

    #app.mobile div.createContributionStep4 div.masterportal {
        height: calc((var(--vh, 1vh) * 100) - 225px);
    }

    #app.mobile div.createContributionStep4 div.masterportal div.aspectRatioWrapper {
        padding: 0;
        height: 100%;
    }
</style>
