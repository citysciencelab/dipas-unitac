/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The embedded masterportal.
 * @displayName Masterportal
 */
import _ from "underscore";

export default {
  /**
   * More Informations for the embedded Masterportal is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "Masterportal",
  props: {
    /**
     * The source for the masterportal.
     */
    src: {
      type: String,
      default: ""
    },
    /**
     * The contribution id for the masterportal.
     */
    contributionID: {
      type: [String, Number, undefined],
      default: undefined
    },
    /**
     * The center point for the initial masterportal viewport.
     */
    center: {
      type: [String, Boolean],
      default: ""
    },

    geodata: {
      type: [String, Boolean],
      default: false
    },
    aspectratio: {
      type: [String, undefined],
      default: undefined
    },
    /**
     * Describes the upper left and the bottom right point of the map at viewport
     */
    extent: {
      type: [Object, Boolean],
      default: false
    },
    /**
     * Describes the tool is used to draw geometries
     * @values point, line, polygon, lineString
     */
    geometryType: {
      type: String,
      default: "Point"
    }
  },
  data () {
    return {
      masterportalOptions: {
        zoomLevel: 6,
        projection: "EPSG:4326"
      },
      iframe: null,
      selectedFeat: this.geodata && Object.keys(JSON.parse(this.geodata)).length ? JSON.parse(this.geodata) : null
    };
  },
  computed: {
    /**
     * computed ToDo
     */
    hideOthers () {
      return this.$store.getters.singlecontributionmap.other_contributions === "hidden" ? 1 : 0;
    },
    queryString () {
      let params = {};

      params = _.clone(this.masterportalOptions);

      if (!_.isUndefined(this.contributionID)) {
        params.hideOthers = this.hideOthers;
        params.node = this.contributionID;

        if (this.extent === false) {
          params.center = this.center;
          params.zoomLevel = 6;
        }
        else {
          params.zoomToExtent = this.extent.minLon + "," + this.extent.minLat + "," + this.extent.maxLon + "," + this.extent.maxLat;
        }
      }

      if (this.selectedFeat && Object.keys(this.selectedFeat).length) {
        if (this.selectedFeat.geometry.type === "Point") {
          params.center = this.selectedFeat.geometry.coordinates[0] + "," + this.selectedFeat.geometry.coordinates[1];
          params.zoomLevel = 6;
        }
        else {
          params.center = this.selectedFeat.geometry.coordinates[0][0] + "," + this.selectedFeat.geometry.coordinates[0][1];
        }
      }

      params.postMessageUrl = this.postMessageUrl_frontend;

      const queryString = [];

      _.each(params, function (value, key) {
        queryString.push(key + "=" + encodeURI(value));
      });

      return queryString.join("&");
    },
    iframeSrc () {
      const src = this.src !== "" ? this.src.split("?") : [];

      return this.queryString.length ? src.shift() + "?" + src.concat([this.queryString]).join("&") : this.src;
    },
    arClass () {
      return "aspect_ratio_" + (!_.isUndefined(this.aspectratio) ? this.aspectratio.replace(":", "_") : "16_9");
    },
    postMessageUrl_frontend () {
      let url = window.location.protocol + "//" + window.location.hostname;

      if (window.location.port) {
        url += ":" + window.location.port;
      }

      return url;
    },
    postMessageUrl_masterportal () {
      const src = this.src.split("?").shift(),
        src_parts = src.split("/").filter(function (el) {
          return el !== "";
        });

      return window.location.protocol + "//" + src_parts[0];
    }
  },
  watch: {
    geometryType (newVal, oldVal) {

      if (oldVal !== newVal) {
        this.deleteDrawnFeatures();
        this.initDraw();
      }
    }
  },
  mounted () {
    // Vorbereitung zur Kommunikation, wenn alles geladen ist den iframe in die Variable übernehmen
    const dom_iframe = document.getElementById("create_geometry_map_iframe");

    if (!_.isNull(dom_iframe)) {
      this.iframe = document.getElementById("create_geometry_map_iframe").contentWindow;
      // Dieser Event-Listener empfängt die gezeichneten Geometrien, sobald sie per RemoteInterface gesendet werden
      window.addEventListener("message", this.receiveMessage);
    }
    /**
     * Map ready Event
     * @event mapReady
     */
    this.$emit("mapReady");
  },
  beforeDestroy () {
    window.removeEventListener("message", this.receiveMessage);
  },
  methods: {
    /**
     * Initialize the drawing process
     * @returns {void}
     */
    initDraw: function () {
      let drawType = "",
        postMessageObject = {};

      if (this.iframe) {

        switch (this.geometryType) {
          case "point":
            drawType = "Point";
            break;
          case "linestring":
            drawType = "LineString";
            break;
          case "polygon":
            drawType = "Polygon";
            break;
          default:
            drawType = "Point";
        }

        this.cancelDraw();

        postMessageObject = {
          "drawType": drawType,
          "color": [255, 0, 0],
          "opacity": 0.5,
          "maxFeatures": 1,
          "transformWGS": true
        };

        if (this.extent) {
          this.iframe.postMessage({
            "radio_channel": "Map",
            "radio_function": "zoomToProjExtent",
            "radio_para_object": {
              "extent": [this.extent.minLon, this.extent.minLat, this.extent.maxLon, this.extent.maxLat],
              "options": {},
              "projection": "EPSG:4326"
            }}, this.postMessageUrl_masterportal);
        }

        if (this.selectedFeat) {
          // if I have a geometry, init draw module with the given geom, then allow editing it
          postMessageObject.initialJSON = {
            "type": "FeatureCollection",
            "features": [this.selectedFeat],
            "properties": {
              "styleId": 1
            }
          };

          postMessageObject.drawType = this.selectedFeat.geometry.type;

          this.iframe.postMessage({"radio_channel": "Draw", "radio_function": "initWithoutGUI", "radio_para_object": postMessageObject}, this.postMessageUrl_masterportal);

          this.editDraw();
        }
        else {
          // if I have no geometry just init draw module
          this.iframe.postMessage({"radio_channel": "Draw", "radio_function": "initWithoutGUI", "radio_para_object": postMessageObject}, this.postMessageUrl_masterportal);
        }
      }
    },
    /**
     * Canceling the drawing process
     * @returns {void}
     */
    cancelDraw: function () {
      if (this.iframe) {
        this.iframe.postMessage({"radio_channel": "Draw", "radio_function": "cancelDrawWithoutGUI", "radio_para_object": {"cursor": true}}, this.postMessageUrl_masterportal);
      }
    },
    /**
     * Starts editing the drawn feature
     * @returns {void}
     */
    editDraw: function () {
      if (this.iframe) {
        this.iframe.postMessage({"radio_channel": "Draw", "radio_function": "editWithoutGUI"}, this.postMessageUrl_masterportal);

      }
    },
    /**
     * Deletes all drawn features
     * @returns {void}
     */
    deleteDrawnFeatures: function () {
      if (this.iframe) {
        this.iframe.postMessage({"radio_channel": "Draw", "radio_function": "deleteAllFeatures"}, this.postMessageUrl_masterportal);

        this.selectedFeat = null;
      }
    },
    /**
     * receiving the message to initiate the drawing process
     * @event event event from vue frontend
     * @returns {void}
     */
    receiveMessage: function (event) {
      let myObj = null;

      if (event.origin !== this.postMessageUrl_masterportal) {
        return;
      }

      if (event.data.hasOwnProperty("drawEnd")) {
        myObj = JSON.parse(event.data.drawEnd);
        /**
         * Triggers drawn process is ready and service drawn features
         */
        this.$emit("input", JSON.stringify(myObj.features[0]));
      }
      else if (event.data.hasOwnProperty("initDrawTool") && event.data.initDrawTool === true) {
        // the masterportal draw tool is fully initialized, now the drawing can start
        if (!this.contributionID) {
          this.initDraw();
        }
      }
    }
  }
};
</script>

<template>
  <div class="masterportal">
    <div
      class="aspectRatioWrapper"
      :class="arClass"
    >
      <iframe
        id="create_geometry_map_iframe"
        ref="iframe"
        width="100%"
        height="100%"
        :src="iframeSrc"
      />
    </div>
  </div>
</template>

<style>
    .masterportal {
        width: 100%;
    }

    .masterportal .aspectRatioWrapper {
        position: relative;
    }

    .masterportal .aspect_ratio_1_1 { padding-top: 100%; }
    .masterportal .aspect_ratio_4_3 { padding-top: 75%; }
    .masterportal .aspect_ratio_16_9 { padding-top: 56.25%; }
    .masterportal .aspect_ratio_16_10 { padding-top: 62.5%; }
    .masterportal .aspect_ratio_21_10 { padding-top: 47.62%; }

    .masterportal iframe {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
    }
</style>

