/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for CreateContributionModalStepContentsStep5 is described here.
 * @displayName CreateContributionModalStepContentsStep5
 */
import Masterportal from "./Masterportal.vue";

export default {
  /**
   * More Informations for CreateContributionModalStepContentsStep3 is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CreateContributionModalStepContentsStep5",
  components: {
    Masterportal
  },
  props: {
    /**
     * The content object of the actual step
     */
    value: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * Provides the boolean whether you use rubrics in contribution.
     * @values true, false
     */
    useRubrics: {
      type: Boolean
    }
  },
  computed: {
    selectedKeywords: function () {
      return this.value.step2.selectedKeywords;
    },
    chosenCategoryName () {
      return this.$store.getters.categoryName(this.value.step2.category.toString());
    },
    chosenCategoryIcon () {
      return this.$store.getters.categoryIcon(this.value.step2.category.toString());
    },
    chosenRubric () {
      return this.$store.getters.rubricName(this.value.step3.rubric.toString());
    },
    masterportalSrc: function () {
      const src = this.$store.getters.createcontributionmap,
        geodata = this.value.step4.geodata ? JSON.parse(this.value.step4.geodata) : null,
        latall = [],
        lonall = [];
      let center = "",
        extent = "",
        coord = [],
        valueToAdd = "projection=EPSG:4326&zoomLevel=6&style=simple";

      if (typeof geodata === "object" && Object.keys(geodata).length) {
        if (geodata.geometry.type === "Point") {
          center = geodata.geometry.coordinates[0] + "," + geodata.geometry.coordinates[1];
          valueToAdd += "&center=" + center;
        }
        else {
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

          extent = Math.min(...lonall) + "," + Math.min(...latall) + "," + Math.max(...lonall) + "," + Math.max(...latall);
          valueToAdd += "&zoomtoextent=" + extent;
        }

      }

      return src + "?" + valueToAdd;
    },
    masterportalGeom: function () {
      return this.value.step4.geodata;
    },
    masterportalGeomType: function () {
      return this.value.step4.geodata ? JSON.parse(this.value.step4.geodata).type : "point";
    }
  },
  methods: {
    /**
     * Saves the choosen rubric
     * @event jumpTo
     * @param {Number} step step to jump into
     */
    jumpTo (step) {
      this.$emit("jumpTo", step);
    }
  }
};
</script>

<template>
  <div class="createContributionStep5">
    <div class="row">
      <p class="headline col-4">
        {{ $t("CreateContributionModal.StepOverview.headline") }}
      </p>
      <p
        v-if="$store.getters.isKeywordServiceEnabled"
        class="col-8 d-flex justify-content-end"
        :title="selectedKeywords + $t('CreateContributionModal.StepOverview.updateAltText')"
        @click="jumpTo(2)"
      >
        <span class="selected-keyword">
          {{ selectedKeywords.length }} {{ $t('CreateContributionModal.StepOverview.keyword') }}
        </span>
      </p>
    </div>
    <p
      class="greyBox editIcon contributionHeadline"
      @click="jumpTo(1)"
    >
      {{ value.step1.headline }}
    </p>
    <div
      class="greyBox editIcon contributionText"
      @click="jumpTo(1)"
    >
      <p>{{ value.step1.text }}</p>
    </div>
    <div class="taxonomy">
      <p
        class="greyBox editIcon inline contributionCategory"
        @click="jumpTo(2)"
      >
        <img :src="chosenCategoryIcon" />{{ chosenCategoryName }}
      </p>
      <p
        v-if="useRubrics"
        class="greyBox editIcon inline contributionRubric"
        @click="jumpTo(3)"
      >
        {{ chosenRubric }}
      </p>
    </div>
    <div class="locationWidget">
      <div
        class="clickCatcher editIcon"
        @click="jumpTo(4)"
      >
      </div>
      <Masterportal
        :src="masterportalSrc"
        :geodata="masterportalGeom"
        :geometryType="masterportalGeomType"
      />
    </div>
  </div>
</template>

<style>
    .selected-keyword {
        color: #ffffff;
        background-color: #005CA9;
        padding: 1px 15px 2px 15px;
        border-radius: 15px;
        cursor: pointer;
    }
    div.createContributionStep5 .greyBox {
        font-size: 14px;
        position: relative;
        background-color: #F0F0F0;
        padding: 5px;
        margin: 0 0 5px 0;
        z-index: 10;
        min-height: 35px;
    }

    div.createContributionStep5 .editIcon {
        position: relative;
    }

    div.createContributionStep5 .editIcon:after {
        display: inline-block;
        z-index: 11;
        margin: 0;
        padding: 0 2px 2px 0;
        content: "edit";
        font-family: "Material Icons";
        font-size: 20px;
        color: black;
        position: absolute;
        right: 0;
        bottom: 0;
        font-weight: normal;
    }

    div.createContributionStep5 p.inline {
        display: inline-block;
    }

    #app.mobile div.createContributionStep5 p.inline {
        display: block;
    }

    div.createContributionStep5 p.contributionHeadline {
        font-weight: bold;
    }

    div.createContributionStep5 div.contributionText p {
        margin: 0;
        padding: 0;
        height: 100px;
        overflow-x: hidden;
        overflow-y: auto;
    }

    #app.mobile div.createContributionStep5 div.contributionText p {
        height: 75px;
    }

    div.createContributionStep5 div.taxonomy {
        display: flex;
        flex-direction: row;
    }

    #app.mobile div.createContributionStep5 div.taxonomy {
        display: block;
    }

    div.createContributionStep5 div.taxonomy p {
        flex: 1 1 0px;
    }

    #app.mobile div.createContributionStep5 div.taxonomy p {
        flex: unset;
    }

    div.createContributionStep5 div.taxonomy p.contributionCategory,
    div.createContributionStep5 div.taxonomy p.contributionRubric {
    }

    div.createContributionStep5 div.taxonomy p.contributionCategory {
        margin-right: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #app.mobile div.createContributionStep5 div.taxonomy p.contributionCategory {
        margin-right: 0;
    }

    #app.mobile div.createContributionStep5 {
        overflow-y: auto;
        overflow-x: hidden;
        height: calc((var(--vh, 1vh) * 100));
    }

    div.createContributionStep5 div.taxonomy p.contributionCategory img {
        max-height: 25px;
        margin-right: 4px;
    }

    div.createContributionStep5 div.locationWidget {
        position: relative;
    }

    div.createContributionStep5 div.locationWidget div.clickCatcher,
    div.createContributionStep5 div.locationWidget div.masterportal {
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    #app.mobile div.createContributionStep5 div.locationWidget div.masterportal {
        height: calc((var(--vh, 1vh) * 100) - 390px);
    }

    div.createContributionStep5 div.locationWidget div.clickCatcher {
        position: absolute;
        z-index: 10;
        margin-top: 5px;
    }

    div.createContributionStep5 div.locationWidget div.masterportal {
        position: relative;
        z-index: 9;
    }

    div.createContributionStep5 div.locationWidget div.masterportal div.aspectRatioWrapper {
        padding: 0;
        height: 250px;
    }

    #app.mobile div.createContributionStep5 div.locationWidget div.masterportal div.aspectRatioWrapper {
        height: 100%;
    }
</style>

