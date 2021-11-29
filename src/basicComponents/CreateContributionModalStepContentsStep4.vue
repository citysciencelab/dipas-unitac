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
     * @name value
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
      extent: {},
      timestamp: null
    };
  },
  computed: {
    /**
     * serves the masterportal settings for the contribution map
     * @name masterportalSrc
     * @returns {String} settings
     */
    masterportalSrc () {
      let src = this.$store.getters.createcontributionmap;

      if (this.$root.isMobile) {
        src += (src.indexOf("?") !== -1 ? "&" : "?") + "style=simple";
      }
      return src;
    },
    /**
     * serves the geometry options
     * @name geometryOptions
     * @returns {Object} geometry options
     */
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
    /**
     * serves the extent of the contribution on map
     * @name mapExtent
     * @returns {Object} extent
     */
    mapExtent () {
      const projectExtent = {
        minLon: this.extent.lon_min,
        minLat: this.extent.lat_min,
        maxLon: this.extent.lon_max,
        maxLat: this.extent.lat_max
      };

      return projectExtent;
    }
  },
  watch: {
    geodata: {
      deep: true,
      /**
       * Saves the choosen geolocation
       * @event input
       * @param {String} geodata geolocation
       * @returns {void}
       */
      handler: function (val) {
        this.$emit("input", {geodata: val});
      }
    },
    /**
     * set geometry type to empty object
     * @name geometryType
     * @returns {void}
     */
    geometryType () {
      this.geodata = "{}";
    },
    /**
     * update the time stamp for the map extent after changing
     * @name mapExtent
     * @returns {void}
     */
    mapExtent: {
      deep: true,
      handler: function () {
        this.timestamp = Date.now();
      }
    }
  },
  /**
   * load initially the contribution extent via requestbroker from drupal api
   * @name geometryType
   * @returns {void}
   */
  beforeMount () {
    this.loadContributionsExtend();
  }
};
</script>

<template>
  <div class="createContributionStep4">
    <h3 class="headline">
      {{ $t("CreateContributionModal.StepMap.headline") }}
    </h3>
    <p
      v-if="Object.keys(geometryOptions).length > 1"
    >
      {{ $t("CreateContributionModal.StepMap.geometry") }}
    </p>
    <fieldset v-if="Object.keys(geometryOptions).length > 1">
      <legend class="sr-only">
        {{ $t("CreateContributionModal.StepMap.geometry") }}
      </legend>
      <RadioGroup
        v-model="geometryType"
        :options="geometryOptions"
        :value="geometryType"
        horizontal="true"
      />
    </fieldset>
    <!--
      triggered on click
      @event showInfotext
    -->
    <p
      class="usageHint"
      :class="[!showInfotext ? 'infotextHidden' : '']"
      tabindex="0"
      @click="showInfotext = !showInfotext"
      @keyup.enter="showInfotext = !showInfotext"
    >
      <i
        class="material-icons"
      >
        info
      </i>
      <span class="infotext">{{ $t("CreateContributionModal.StepMap.infotext_" + geometryType) }}</span>
    </p>
    <!--
      @name Masterportal
      @model geodata
    -->
    <Masterportal
      ref="map"
      :key="timestamp"
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
        height: 85%;
    }
    div.createContributionStep4 div.radio-wrapper input:focus-visible + label {
        outline: 3px solid #005CA9;
        outline-offset: 5px;
        opacity: 1;
    }
</style>
