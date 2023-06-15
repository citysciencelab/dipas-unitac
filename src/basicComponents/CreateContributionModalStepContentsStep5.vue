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
    /**
     * serves the selected keywords
     * @name selectedKeywords
     * @returns {Array} keywords
     */
    selectedKeywords: function () {
      return this.value.step2.selectedKeywords;
    },
    /**
     * serves the choosen category name
     * @name chosenCategoryName
     * @returns {String} category name
     */
    chosenCategoryName () {
      return this.$store.getters.categoryName(this.value.step2.category.toString());
    },
    /**
     * serves the choosen mdi-icon
     * @name chosenCategoryIcon
     * @returns {String} mdi-icon
     */
    chosenCategoryIcon () {
      return this.$store.getters.categoryIcon(this.value.step2.category.toString());
    },
    /**
     * serves the choosen rubric
     * @name chosenRubric
     * @returns {String} rubric
     */
    chosenRubric () {
      return this.$store.getters.rubricName(this.value.step3.rubric.toString());
    },
    /**
     * serves the masterportal source
     * @name masterportalSrc
     * @returns {String} masterportal source
     */
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
    /**
     * serves the masterportal geometry
     * @name masterportalGeom
     * @returns {String} masterportal geometry
     */
    masterportalGeom: function () {
      return this.value.step4.geodata;
    },
    /**
     * serves the masterportal geometry type
     * @name masterportalGeomType
     * @returns {String} geometry type
     */
    masterportalGeomType: function () {
      return this.value.step4.geodata ? JSON.parse(this.value.step4.geodata).type : "point";
    }
  },
  methods: {
    /**
     * Jumps to the step
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
      <h3 class="headline col-4">
        {{ $t("CreateContributionModal.StepOverview.headline") }}
      </h3>
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
      id="sm1headline"
      class="summaryheadline sm1"
    >
      Beitragstitel
    </p>
    <p
      class="greyBox editIcon contributionHeadline"
      tabindex="0"
      role="button"
      aria-labelledby="sm1headline"
      @click="jumpTo(1)"
      @keyup.enter="jumpTo(1)"
    >
      {{ value.step1.headline }}
    </p>
    <p
      id="sm2headline"
      class="summaryheadline sm2"
    >
      Beschreibung
    </p>
    <div
      class="greyBox editIcon contributionText"
      tabindex="0"
      role="button"
      aria-labelledby="sm2headline"
      @click="jumpTo(1)"
      @keyup.enter="jumpTo(1)"
    >
      <p>{{ value.step1.text }}</p>
    </div>
    <div class="taxonomy">
      <p
        id="sm3headline"
        class="summaryheadline sm3"
      >
        Kategorie
      </p>
      <p
        class="greyBox editIcon inline contributionCategory"
        tabindex="0"
        role="button"
        aria-labelledby="sm3headline"
        @click="jumpTo(2)"
        @keyup.enter="jumpTo(2)"
      >
        <img
          :src="chosenCategoryIcon"
          alt=""
        />
        {{ chosenCategoryName }}
      </p>
      <p
        id="sm4headline"
        class="summaryheadline sm4"
      >
        Beitragstyp
      </p>
      <p
        v-if="useRubrics"
        class="greyBox editIcon inline contributionRubric"
        tabindex="0"
        role="button"
        aria-labelledby="sm4headline"
        @click="jumpTo(3)"
        @keyup.enter="jumpTo(3)"
      >
        {{ chosenRubric }}
      </p>
    </div>
    <div class="locationWidget">
      <div
        class="clickCatcher editIcon"
        tabindex="0"
        role="button"
        @click="jumpTo(4)"
        @keyup.enter="jumpTo(4)"
      >
      </div>
      <!--
        @name Masterportal
      -->
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
        font-size: 0.875rem;
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
        font-size: 1.25rem;
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
        padding-top: 7px;
        padding-right: 40px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
      display: grid;
      grid-template-columns: 0fr 1fr;
      grid-template-rows: 1fr 1fr;
      grid-column-gap: 20px;
      column-gap: 20px;
      grid-row-gap: 0;
    }

    #app.mobile div.createContributionStep5 div.taxonomy {
        display: block;
    }

    #app.mobile div.createContributionStep5 div.taxonomy {
        grid-column: unset;
        grid-row: unset;
    }

    div.createContributionStep5 p.summaryheadline {
        font-weight: bold;
        margin-top: 5px;
        margin-bottom: 3px;
    }

    div.createContributionStep5 div.taxonomy p.contributionCategory {
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
    }

    div.createContributionStep5 div.taxonomy p.contributionCategory img {
        max-height: 25px;
        margin-right: 4px;
    }

    div.createContributionStep5 div.locationWidget {
        position: relative;
        min-height: 50px;
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
        min-height: 175px;
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
    #app.mobile div.createContributionStep5 div.locationWidget .masterportal iframe,
    #app.mobile div.createContributionStep5 div.locationWidget div.clickCatcher {
        height: 90%;
    }

    div.createContributionStep5 .editIcon:focus-visible {
      outline: 3px solid #005CA9;
    }
</style>

